<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

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
        $command = ($parameters['command']) ?? 'view';
        $thread_content_id = new ContentID(ContentID::createIDString($thread_id));
        $thread = $thread_content_id->getInstanceFromID($this->domain);

        if (is_null($thread) || !$thread->exists())
        {
            return;
        }

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

        $posts = $thread->getPosts();

        if (empty($posts))
        {
            return;
        }

        $op_post = $posts[0];
        $page_title = '';

        if ($this->domain->setting('prefix_board_title'))
        {
            $page_title .= $this->domain->reference('title');
        }

        if ($this->domain->setting('subject_in_title') && !nel_true_empty($op_post->data('subject')))
        {
            $page_title .= ' - ' . $op_post->data('subject');
        }
        else if ($this->domain->setting('slug_in_title') && !nel_true_empty($thread->data('slug')))
        {
            $page_title .= ' - ' . $thread->data('slug');
        }
        else if ($this->domain->setting('thread_number_in_title'))
        {
            $page_title .= ' - ' . _gettext('Thread') . ' #' . $op_post->data('post_number');
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
                            ['module' => 'output', 'section' => 'index', 'actions' => 'view', 'index' => '0',
                                'board-id' => $this->domain->id(), 'modmode' => 'true']);
        }
        else
        {
            $this->render_data['header'] = $output_header->board([], true);
            $return_url = $this->domain->reference('board_web_path') . NEL_MAIN_INDEX . NEL_PAGE_EXT;
        }

        $this->render_data['show_global_announcement'] = !nel_true_empty(
                nel_site_domain()->setting('global_announcement'));
        $this->render_data['global_announcement_text'] = nel_site_domain()->setting('global_announcement');

        $query = 'SELECT * FROM "' . NEL_BLOTTER_TABLE . '" ORDER BY "time" ASC';
        $blotter_entries = $this->database->executeFetchAll($query, PDO::FETCH_ASSOC);

        foreach ($blotter_entries as $entry)
        {
            $blotter_data = array();
            $blotter_data['time'] = date('Y/m/d', intval($entry['time']));
            $blotter_data['text'] = $entry['text'];
            $this->render_data['blotter_entries'][] = $blotter_data;
        }

        $this->render_data['show_blotter'] = isset($this->render_data['blotter_entries']) &&
        !empty($this->render_data['blotter_entries']);
        $this->render_data['blotter_url'] = NEL_BASE_WEB_PATH . 'blotter.html';

        $this->render_data['return_url'] = $return_url;
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

        foreach ($posts as $post)
        {
            $thread->getJSON()->addPost($post->getJSON());
            $parameters = ['gen_data' => $gen_data, 'in_thread_number' => $post_counter];
            $post_render = $output_post->render($post, $parameters, true);

            if ($post->data('op'))
            {
                $this->render_data['op_post'] = $post_render;
            }
            else
            {
                $this->render_data['thread_posts'][] = $post_render;
            }

            $post_counter ++;
        }

        $this->render_data['index_navigation'] = true;
        $this->render_data['footer_form'] = true;
        $this->render_data['use_report_captcha'] = $this->domain->setting('use_report_captcha');
        $this->render_data['captcha_gen_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                'module=anti-spam&section=captcha&actions=get';
        $this->render_data['captcha_regen_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                'module=anti-spam&section=captcha&actions=generate&no-display';
        $this->render_data['use_report_recaptcha'] = $this->domain->setting('use_report_recaptcha');
        $this->render_data['recaptcha_sitekey'] = $this->site_domain->setting('recaptcha_site_key');
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);

        if ($this->write_mode)
        {
            $this->file_handler->writeFile(
                    $this->domain->reference('page_path') . $thread_id . '/' . $thread->pageBasename() . NEL_PAGE_EXT,
                    $output, NEL_FILES_PERM, true);
            $thread->getJSON()->write();
        }
        else
        {
            switch ($command)
            {
                case 'view':
                    echo $output;
                    break;

                default:
                    echo $output;
            }

            nel_clean_exit();
        }
    }
}