<?php

declare(strict_types=1);

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Content\ContentID;
use Nelliel\Domains\Domain;
use PDO;

class OutputThread extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters = array(), bool $data_only = false)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('thread/thread');
        $thread_id = ($parameters['thread_id']) ?? 0;
        $command = ($parameters['command']) ?? 'view-thread';
        $thread_content_id = new ContentID(ContentID::createIDString($thread_id));
        $this->render_data['in_modmode'] = $this->session->inModmode($this->domain) && !$this->write_mode;

        if ($this->render_data['in_modmode'])
        {
            $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=threads&board-id=' .
                    $this->domain->id() . '&modmode=true';
        }
        else
        {
            $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=threads&board-id=' .
                    $this->domain->id();
        }

        $prepared = $this->database->prepare(
                'SELECT * FROM "' . $this->domain->reference('threads_table') . '" WHERE "thread_id" = ?');
        $thread_data = $this->database->executePreparedFetch($prepared, [$thread_id], PDO::FETCH_ASSOC);

        if (empty($thread_data))
        {
            return;
        }

        $prepared = $this->database->prepare(
                'SELECT * FROM "' . $this->domain->reference('posts_table') .
                '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC');
        $treeline = $this->database->executePreparedFetchAll($prepared, [$thread_id], PDO::FETCH_ASSOC);

        if (empty($treeline))
        {
            return;
        }

        $thread = $thread_content_id->getInstanceFromID($this->domain);
        $thread->loadFromDatabase();
        $page_title = $this->domain->reference('board_uri');

        if (!isset($treeline[0]['subject']) || nel_true_empty($treeline[0]['subject']))
        {
            $page_title .=' - Thread #' . $treeline[0]['post_number'];
        }
        else
        {
            $page_title .=' - ' . $treeline[0]['subject'];
        }

        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render(['page_title' => $page_title], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);

        if ($this->session->inModmode($this->domain) && !$this->write_mode)
        {
            $manage_headers['header'] = _gettext('Moderator Mode');
            $manage_headers['sub_header'] = _gettext('View Thread');
            $this->render_data['header'] = $output_header->board(['manage_headers' => $manage_headers], true);
            $return_url = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'render', 'actions' => 'view-index', 'index' => '0',
                                'board-id' => $this->domain->id(), 'modmode' => 'true']);
        }
        else
        {
            $this->render_data['header'] = $output_header->board([], true);
            $return_url = $this->domain->reference('board_web_path') . NEL_MAIN_INDEX . NEL_PAGE_EXT;
        }

        $this->render_data['show_global_announcement'] = !nel_true_empty(nel_site_domain()->setting('global_announcement'));
        $this->render_data['global_announcement_text'] = nel_site_domain()->setting('global_announcement');
        $this->render_data['return_url'] = $return_url;
        $json_thread = new \Nelliel\API\JSON\JSONThread($this->domain, $this->file_handler);
        $json_thread->storeData($json_thread->prepareData($thread_data), 'thread');
        $json_content = new \Nelliel\API\JSON\JSONContent($this->domain, $this->file_handler);
        $json_instances = ['thread' => $json_thread, 'content' => $json_content];
        $post_counter = 1;
        $gen_data['index_rendering'] = false;
        $gen_data['abbreviate'] = false;
        $this->render_data['abbreviate'] = false;
        $output_posting_form = new OutputPostingForm($this->domain, $this->write_mode);
        $this->render_data['posting_form'] = $output_posting_form->render(['response_to' => $thread_id], true);
        $output_post = new OutputPost($this->domain, $this->write_mode);
        $this->render_data['op_post'] = array();
        $this->render_data['thread_posts'] = array();
        $this->render_data['thread_id'] = $thread_content_id->getIDString();
        $this->render_data['thread_expand_id'] = 'thread-expand-' . $thread_content_id->getIDString();
        $this->render_data['thread_corral_id'] = 'thread-corral-' . $thread_content_id->getIDString();
        $this->render_data['thread_info_id'] = 'thread-header-info-' . $thread_content_id->getIDString();
        $this->render_data['thread_options_id'] = 'thread-header-options-' . $thread_content_id->getIDString();
        $this->render_data['board_id'] = $this->domain->id();
        $this->render_data['show_styles'] = true;
        $output_menu = new OutputMenu($this->domain, $this->write_mode);
        $this->render_data['styles'] = $output_menu->styles([], true);

        foreach ($treeline as $post_data)
        {
            $json_post = new \Nelliel\API\JSON\JSONPost($this->domain, $this->file_handler);
            $json_instances['post'] = $json_post;
            $parameters = ['thread_data' => $thread_data, 'post_data' => $post_data, 'gen_data' => $gen_data,
                'json_instances' => $json_instances, 'in_thread_number' => $post_counter];
            $post_render = $output_post->render($parameters, true);

            if ($post_data['op'] == 1)
            {
                $this->render_data['op_post'] = $post_render;
            }
            else
            {
                $this->render_data['thread_posts'][] = $post_render;
            }

            $json_thread->addPostData($json_post->retrieveData());
            $post_counter ++;
        }

        $this->render_data['index_navigation'] = true;
        $this->render_data['footer_form'] = true;
        $this->render_data['use_report_captcha'] = $this->domain->setting('use_report_captcha');
        $this->render_data['captcha_gen_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=captcha&actions=get';
        $this->render_data['captcha_regen_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                'module=captcha&actions=generate&no-display';
        $this->render_data['use_report_recaptcha'] = $this->domain->setting('use_report_recaptcha');
        $this->render_data['recaptcha_sitekey'] = $this->site_domain->setting('recaptcha_site_key');
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);

        if ($this->write_mode)
        {
            $this->file_handler->writeFile(
                    $this->domain->reference('page_path') . $thread_id . '/' . $thread->pageBasename() . NEL_PAGE_EXT, $output,
                    NEL_FILES_PERM, true);
            $json_thread->writeStoredData($this->domain->reference('page_path') . $thread_id . '/', $thread->pageBasename());
        }
        else
        {
            switch ($command)
            {
                case 'view-thread':
                    echo $output;
                    break;

                default:
                    echo $output;
            }

            nel_clean_exit();
        }
    }
}