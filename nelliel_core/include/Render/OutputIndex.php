<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Content\ContentID;
use Nelliel\Domains\Domain;
use PDO;

class OutputIndex extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $session = new \Nelliel\Account\Session();
        $page = 1;
        $site_domain = new \Nelliel\Domains\DomainSite($this->database);
        $json_index = new \Nelliel\API\JSON\JSONIndex($this->domain, $this->file_handler);
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);

        if ($session->isActive() && !$this->write_mode)
        {
            $manage_headers['header'] = _gettext('Moderator Mode');
            $manage_headers['sub_header'] = _gettext('View Index');
            $this->render_data['header'] = $output_header->board(['manage_headers' => $manage_headers], true);
        }
        else
        {
            $this->render_data['header'] = $output_header->board([], true);
        }

        $thread_list = $this->database->executeFetchAll(
                'SELECT * FROM "' . $this->domain->reference('threads_table') .
                '" WHERE "archive_status" = 0 ORDER BY "sticky" DESC, "last_bump_time" DESC, "last_bump_time_milli" DESC',
                PDO::FETCH_ASSOC);
        $thread_count = count($thread_list);
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

        $index_format = $site_domain->setting('index_filename_format');
        $this->render_data['catalog_url'] = 'catalog.html';

        if (empty($thread_list))
        {
            $this->render_data['index_navigation'] = true;
            $this->render_data['footer_form'] = true;
            $this->render_data['pagination'] = $this->indexNavigation($page, $page_count, $index_format);
            $this->render_data['use_report_captcha'] = $this->domain->setting('use_report_captcha');
            $this->render_data['captcha_gen_url'] = NEL_MAIN_SCRIPT_WEB_PATH . '?module=captcha&actions=get';
            $this->render_data['captcha_regen_url'] = NEL_MAIN_SCRIPT_WEB_PATH .
                    '?module=captcha&actions=generate&no-display';
            $this->render_data['use_report_recaptcha'] = $this->domain->setting('use_report_recaptcha');
            $this->render_data['recaptcha_sitekey'] = $this->site_domain->setting('recaptcha_site_key');
            $output_footer = new OutputFooter($this->domain, $this->write_mode);
            $output_footer = new OutputFooter($this->domain, $this->write_mode);
            $this->render_data['footer'] = $output_footer->render(['show_styles' => true], true);
            $output = $this->output('index/index_page', $data_only, true);
            $index_filename = ($page == 1) ? 'index' . NEL_PAGE_EXT : sprintf($index_format, ($page)) . NEL_PAGE_EXT;

            if ($this->write_mode)
            {
                $this->file_handler->writeFile($this->domain->reference('board_path') . $index_filename, $output,
                        NEL_FILES_PERM, true);
                $json_index->storeData($json_index->prepareData($gen_data['index']), 'index');
                $json_index->writeStoredData($this->domain->reference('board_path'), sprintf('index-%d', $page));
            }
            else
            {
                echo $output;
                return $output;
            }

            if ($this->write_mode)
            {
                $this->file_handler->writeFile($this->domain->reference('board_path') . NEL_MAIN_INDEX . NEL_PAGE_EXT,
                        $output, NEL_FILES_PERM);
                $json_index->writeStoredData($this->domain->reference('board_path'), 'index-1');
            }
            else
            {
                echo $output;
            }

            return $output;
        }

        $gen_data['index_rendering'] = true;
        $this->render_data['form_action'] = NEL_MAIN_SCRIPT_WEB_PATH . '?module=threads&board-id=' . $this->domain->id();
        $threads_on_page = 0;
        $timer_offset = $this->endTimer(false);

        foreach ($thread_list as $thread_data)
        {
            $this->startTimer($timer_offset);
            $thread_input = array();
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . $this->domain->reference('posts_table') .
                    '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC');
            $treeline = $this->database->executePreparedFetchAll($prepared, [$thread_data['thread_id']],
                    PDO::FETCH_ASSOC);

            if (empty($treeline))
            {
                $threads_done ++;
                continue;
            }

            $output_post = new OutputPost($this->domain, $this->write_mode);
            $json_thread = new \Nelliel\API\JSON\JSONThread($this->domain, $this->file_handler);
            $thread_content_id = ContentID::createIDString(intval($thread_data['thread_id']));
            $thread_input = array();
            $thread_input['thread_id'] = $thread_content_id;
            $thread_input['thread_expand_id'] = 'thread-expand-' . $thread_content_id;
            $thread_input['thread_corral_id'] = 'thread-' . $thread_content_id;
            $thread_input['omitted_count'] = $thread_data['post_count'] - $this->domain->setting('abbreviate_thread');
            $gen_data['abbreviate'] = $thread_data['post_count'] > $this->domain->setting('abbreviate_thread');
            $thread_input['abbreviate'] = $gen_data['abbreviate'];
            $abbreviate_start = $thread_data['post_count'] - ($this->domain->setting('abbreviate_thread') - 1);
            $post_counter = 1;

            foreach ($treeline as $post_data)
            {
                $json_post = new \Nelliel\API\JSON\JSONPost($this->domain, $this->file_handler);
                $json_instances['post'] = $json_post;
                $parameters = ['thread_data' => $thread_data, 'post_data' => $post_data, 'gen_data' => $gen_data,
                    'json_instances' => $json_instances, 'in_thread_number' => $post_counter];

                if ($post_data['op'] == 1)
                {
                    $thread_input['op_post'] = $output_post->render($parameters, true);
                    $json_thread->addPostData($json_post->retrieveData());
                }
                else
                {
                    if ($post_counter > $abbreviate_start)
                    {
                        $thread_input['thread_posts'][] = $output_post->render($parameters, true);
                        $json_thread->addPostData($json_post->retrieveData());
                    }
                }

                $post_counter ++;
            }

            $json_index->addThreadData($json_thread->retrieveData());
            $this->render_data['threads'][] = $thread_input;
            $threads_on_page ++;
            $threads_done ++;

            if ($threads_on_page >= $this->domain->setting('threads_per_page') || $threads_done == $thread_count)
            {
                $this->render_data['index_navigation'] = true;
                $this->render_data['footer_form'] = true;
                $this->render_data['pagination'] = $this->indexNavigation($page, $page_count, $index_format);
                $this->render_data['use_report_captcha'] = $this->domain->setting('use_report_captcha');
                $this->render_data['captcha_gen_url'] = NEL_MAIN_SCRIPT_WEB_PATH . '?module=captcha&actions=get';
                $this->render_data['captcha_regen_url'] = NEL_MAIN_SCRIPT_WEB_PATH .
                        '?module=captcha&actions=generate&no-display';
                $this->render_data['use_report_recaptcha'] = $this->domain->setting('use_report_recaptcha');
                $this->render_data['recaptcha_sitekey'] = $this->site_domain->setting('recaptcha_site_key');
                $output_footer = new OutputFooter($this->domain, $this->write_mode);
                $this->render_data['footer'] = $output_footer->render(['show_styles' => true], true);
                $output = $this->output('index/index_page', $data_only, true);
                $index_filename = ($page == 1) ? 'index' . NEL_PAGE_EXT : sprintf($index_format, ($page)) . NEL_PAGE_EXT;

                if ($this->write_mode)
                {
                    $this->file_handler->writeFile($this->domain->reference('board_path') . $index_filename, $output,
                            NEL_FILES_PERM, true);
                    $json_index->storeData($json_index->prepareData($gen_data['index']), 'index');
                    $json_index->writeStoredData($this->domain->reference('board_path'), sprintf('index-%d', $page));
                }
                else
                {
                    echo $output;
                    return $output;
                }

                $threads_on_page = 0;
                $this->render_data['threads'] = array();
                $page ++;
            }
        }
    }

    private function indexNavigation(int $page, int $page_count, $page_format)
    {
        $pagination_object = new Pagination();
        $pagination_object->setPrevious(_gettext('Previous'));
        $pagination_object->setNext(_gettext('Next'));
        $pagination_object->setPage('%d', $page_format);
        $pagination_object->setFirst('%d', 'index' . NEL_PAGE_EXT);
        $pagination_object->setLast('%d', $page_format);
        return $pagination_object->generateNumerical(1, $page_count, $page);
    }
}