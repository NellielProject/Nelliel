<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\API\JSON\PostJSON;
use Nelliel\Content\ContentID;
use Nelliel\Domains\DomainBoard;
use Nelliel\Domains\DomainSite;
use PDO;

class OutputIndex extends Output
{

    function __construct(DomainBoard $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('index/index');
        $page = 1;
        $site_domain = new DomainSite($this->database);
        $page_title = $this->domain->reference('board_uri') . ' - ' . $this->domain->reference('title');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render(['page_title' => $page_title], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['in_modmode'] = $this->session->inModmode($this->domain) && !$this->write_mode;

        if ($this->render_data['in_modmode'])
        {
            $manage_headers['header'] = _gettext('Moderator Mode');
            $manage_headers['sub_header'] = _gettext('View Index');
            $this->render_data['header'] = $output_header->board(['manage_headers' => $manage_headers], true);
            $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=threads&board-id=' .
                    $this->domain->id() . '&modmode=true';
        }
        else
        {
            $this->render_data['header'] = $output_header->board([], true);
            $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=threads&board-id=' .
                    $this->domain->id();
        }

        $threads = $this->domain->activeThreads(true);
        $thread_count = count($threads);
        $threads_done = 0;
        $gen_data = array();
        $gen_data['index']['thread_count'] = $thread_count;
        $output_posting_form = new OutputPostingForm($this->domain, $this->write_mode);
        $this->render_data['posting_form'] = $output_posting_form->render(['response_to' => 0], true);

        if ($thread_count === 0)
        {
            $page_count = 1;
        }
        else
        {
            $page_count = (int) ceil($thread_count / $this->domain->setting('threads_per_page'));
        }

        $this->render_data['show_global_announcement'] = !nel_true_empty($site_domain->setting('global_announcement'));
        $this->render_data['global_announcement_text'] = $site_domain->setting('global_announcement');
        $index_format = $site_domain->setting('index_filename_format');
        $this->render_data['catalog_url'] = 'catalog.html';
        $this->render_data['index_navigation_top'] = $this->domain->setting('index_nav_top');
        $this->render_data['index_navigation_bottom'] = $this->domain->setting('index_nav_bottom');
        $this->render_data['footer_form'] = true;
        $this->render_data['use_report_captcha'] = $this->domain->setting('use_report_captcha');
        $this->render_data['captcha_gen_url'] = NEL_MAIN_SCRIPT_WEB_PATH .
                '?module=anti-spam&section=captcha&actions=get';
        $this->render_data['captcha_regen_url'] = NEL_MAIN_SCRIPT_WEB_PATH .
                '?module=anti-spam&section=captcha&actions=generate&no-display';
        $this->render_data['use_report_recaptcha'] = $this->domain->setting('use_report_recaptcha');
        $this->render_data['recaptcha_sitekey'] = $this->site_domain->setting('recaptcha_site_key');
        $this->render_data['show_styles'] = true;
        $output_menu = new OutputMenu($this->domain, $this->write_mode);
        $this->render_data['styles'] = $output_menu->styles([], true);
        $index_basename = 'index';

        if (empty($threads))
        {
            $output = $this->doOutput($gen_data, $index_basename, $data_only);

            if (!$this->write_mode)
            {
                return $output;
            }
        }

        $gen_data['index_rendering'] = true;
        $threads_on_page = 0;

        foreach ($threads as $thread)
        {
            $thread_input = array();
            $index_basename = ($page == 1) ? 'index' : sprintf($index_format, ($page));
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . $this->domain->reference('posts_table') .
                    '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC');
            $treeline = $this->database->executePreparedFetchAll($prepared, [$thread->contentID()->threadID()],
                    PDO::FETCH_ASSOC);

            if (empty($treeline))
            {
                $threads_done ++;
                continue;
            }

            $output_post = new OutputPost($this->domain, $this->write_mode);
            $thread_input = array();
            $thread_input['thread_id'] = $thread->contentID()->getIDString();
            $thread_input['thread_expand_id'] = 'thread-expand-' . $thread->contentID()->getIDString();
            $thread_input['thread_corral_id'] = 'thread-corral-' . $thread->contentID()->getIDString();
            $index_replies = $this->domain->setting('index_thread_replies');

            if ($thread->data('sticky'))
            {
                $index_replies = $this->domain->setting('index_sticky_replies');
            }

            $thread_input['omitted_count'] = $thread->data('post_count') - $index_replies - 1; // Subtract 1 to account for OP
            $gen_data['abbreviate'] = $thread_input['omitted_count'] > 0;
            $thread_input['abbreviate'] = $gen_data['abbreviate'];
            $abbreviate_start = $thread->data('post_count') - $index_replies;
            $post_counter = 1;

            foreach ($treeline as $post_data)
            {
                $post_content_id = new ContentID(
                        ContentID::createIDString($thread->contentID()->threadID(), $post_data['post_number']));
                $post = $post_content_id->getInstanceFromID($this->domain);
                $post_json = new PostJSON($post, $this->file_handler);
                $parameters = ['gen_data' => $gen_data, 'post_json' => $post_json, 'in_thread_number' => $post_counter];

                if ($post_data['op'] == 1)
                {
                    $thread_input['op_post'] = $output_post->render($post, $parameters, true);
                }
                else
                {
                    if ($post_counter > $abbreviate_start)
                    {
                        $thread_input['thread_posts'][] = $output_post->render($post, $parameters, true);
                    }
                }

                $post_counter ++;
            }

            $this->render_data['threads'][] = $thread_input;
            $threads_on_page ++;
            $threads_done ++;

            if ($threads_on_page >= $this->domain->setting('threads_per_page') || $threads_done == $thread_count)
            {
                $this->render_data['pagination'] = $this->indexNavigation($page, $page_count, $index_format);
                $output = $this->doOutput($gen_data, $index_basename, $data_only);

                if (!$this->write_mode)
                {
                    return $output;
                }

                $threads_on_page = 0;
                $this->render_data['threads'] = array();
                $this->timer->reset();
                $this->timer->start();
                $page ++;
            }
        }
    }

    private function indexNavigation(int $page, int $page_count, $page_format)
    {
        $pagination_object = new Pagination();
        $pagination_object->setPrevious(_gettext('Previous'));
        $pagination_object->setNext(_gettext('Next'));
        $pagination_object->setPage('%d', $page_format . NEL_PAGE_EXT);
        $pagination_object->setFirst('%d', 'index' . NEL_PAGE_EXT);
        $pagination_object->setLast('%d', $page_format . NEL_PAGE_EXT);
        return $pagination_object->generateNumerical(1, $page_count, $page);
    }

    private function doOutput(array $gen_data, string $index_basename, bool $data_only)
    {
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);

        if ($this->write_mode)
        {
            $this->file_handler->writeFile($this->domain->reference('board_path') . $index_basename . NEL_PAGE_EXT,
                    $output, NEL_FILES_PERM, true);
        }
        else
        {
            echo $output;
        }

        return $output;
    }
}