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
        $route_parameters = $parameters['parameters'] ?? array();
        $thread_id = $parameters['thread_id'] ?? array();
        $expand = array_key_exists('expand', $route_parameters);
        $collapse = array_key_exists('collapse', $route_parameters);
        $thread_content_id = new ContentID(ContentID::createIDString($thread_id));
        $thread = $thread_content_id->getInstanceFromID($this->domain);

        if (is_null($thread) || !$thread->exists()) {
            return;
        }

        $this->render_data['in_modmode'] = $this->session->inModmode($this->domain) && !$this->write_mode;

        if ($this->render_data['in_modmode']) {
            $this->render_data['form_action'] = nel_build_router_url([$this->domain->id(), 'threads'], false, 'modmode');
        } else {
            $this->render_data['form_action'] = nel_build_router_url([$this->domain->id(), 'threads']);
        }

        $posts = $thread->getPosts();
        $post_count = count($posts);

        if (empty($posts)) {
            return;
        }

        $op_post = $posts[0];

        if (!$expand && !$collapse) {
            $page_title = '';

            if ($this->domain->setting('prefix_board_title')) {
                $page_title .= $this->domain->reference('title');
            }

            if ($this->domain->setting('subject_in_title') && !nel_true_empty($op_post->data('subject'))) {
                $page_title .= ' - ' . $op_post->data('subject');
            } else if ($this->domain->setting('slug_in_title') && !nel_true_empty($thread->data('slug'))) {
                $page_title .= ' - ' . $thread->data('slug');
            } else if ($this->domain->setting('thread_number_in_title')) {
                $page_title .= ' - ' . _gettext('Thread') . ' #' . $op_post->data('post_number');
            }

            $output_head = new OutputHead($this->domain, $this->write_mode);
            $this->render_data['head'] = $output_head->render(['page_title' => $page_title], true);
            $output_header = new OutputHeader($this->domain, $this->write_mode);

            if ($this->session->inModmode($this->domain) && !$this->write_mode) {
                $manage_headers['header'] = _gettext('Moderator Mode');
                $manage_headers['sub_header'] = _gettext('View Thread');
                $this->render_data['header'] = $output_header->board(['manage_headers' => $manage_headers], true);
                $return_url = nel_build_router_url([$this->domain->id()], true, 'modmode');
            } else {
                $this->render_data['header'] = $output_header->board([], true);
                $return_url = $this->domain->reference('board_web_path') . NEL_MAIN_INDEX . NEL_PAGE_EXT;
            }

            $this->render_data['show_global_announcement'] = !nel_true_empty(
                nel_site_domain()->setting('global_announcement'));
            $this->render_data['global_announcement_text'] = nel_site_domain()->setting('global_announcement');

            $query = 'SELECT * FROM "' . NEL_BLOTTER_TABLE . '" ORDER BY "time" ASC';
            $blotter_entries = $this->database->executeFetchAll($query, PDO::FETCH_ASSOC);

            foreach ($blotter_entries as $entry) {
                $blotter_data = array();
                $blotter_data['time'] = date('Y/m/d', intval($entry['time']));
                $blotter_data['text'] = $entry['text'];
                $this->render_data['blotter_entries'][] = $blotter_data;
            }

            $this->render_data['show_blotter'] = isset($this->render_data['blotter_entries']) &&
                !empty($this->render_data['blotter_entries']);
            $this->render_data['blotter_url'] = NEL_BASE_WEB_PATH . 'blotter.html';
            $this->render_data['return_url'] = $return_url;

            $this->render_data['abbreviate'] = false;
            $output_new_post_form = new OutputNewPostForm($this->domain, $this->write_mode);
            $this->render_data['new_post_form'] = $output_new_post_form->render(['response_to' => $thread_id], true);
            $this->render_data['show_styles'] = true;
            $output_menu = new OutputMenu($this->domain, $this->write_mode);
            $this->render_data['styles'] = $output_menu->styles([], true);
            $this->render_data['return_link'] = true;
            $gen_data['index_rendering'] = false;
        } else {
            $gen_data['index_rendering'] = true;
            $this->render_data['return_link'] = false;
        }

        $gen_data['abbreviate'] = false;
        $output_post = new OutputPost($this->domain, $this->write_mode);
        $this->render_data['op_post'] = array();
        $this->render_data['thread_posts'] = array();
        $this->render_data['thread_id'] = $thread_content_id->getIDString();
        $this->render_data['thread_expand_id'] = 'thread-expand-' . $thread_content_id->getIDString();
        $this->render_data['thread_corral_id'] = 'thread-corral-' . $thread_content_id->getIDString();
        $this->render_data['thread_info_id'] = 'thread-header-info-' . $thread_content_id->getIDString();
        $this->render_data['thread_options_id'] = 'thread-header-options-' . $thread_content_id->getIDString();
        $this->render_data['board_id'] = $this->domain->id();
        $this->render_data['board_safety'] = $this->domain->setting('safety_level');
        $generate_first_posts = $post_count > $this->domain->setting('first_posts_threshold');
        $generate_last_posts = $post_count > $this->domain->setting('last_posts_threshold');
        $first_posts_increments = json_decode($this->domain->setting('first_posts_increments'));
        $first_posts_increments = is_array($first_posts_increments) ? $first_posts_increments : array();
        $last_posts_increments = json_decode($this->domain->setting('last_posts_increments'));
        $last_posts_increments = is_array($last_posts_increments) ? $last_posts_increments : array();
        $first_posts = array();
        $last_posts = array();
        $post_counter = 1;
        $abbreviate_start = $thread->data('post_count') - $this->domain->setting('index_thread_replies');

        foreach ($posts as $post) {
            if ($collapse && $post_counter <= $abbreviate_start) {
                $post_counter ++;
                continue;
            }

            $posts_from_end = $post_count - $post_counter;
            $parameters = ['gen_data' => $gen_data, 'in_thread_number' => $post_counter];
            $post_render = $output_post->render($post, $parameters, true);

            if ($post->data('op')) {
                $this->render_data['op_post'] = $post_render;

                if ($this->domain->setting('new_post_auto_subject')) {
                    $this->render_data['new_post_form']['verb'] = $post->data('subject');
                }
            } else {
                $this->render_data['thread_posts'][] = $post_render;

                if ($generate_first_posts) {
                    foreach ($first_posts_increments as $increment) {
                        // Account for OP
                        if ($post_counter - 1 <= $increment) {
                            $first_posts[$increment][] = $post_render;
                        }
                    }
                }

                if ($generate_last_posts) {
                    foreach ($last_posts_increments as $increment) {
                        if ($posts_from_end < $increment && $post_count > $increment) {
                            $last_posts[$increment][] = $post_render;
                        }
                    }
                }
            }

            $post_counter ++;
        }

        $this->render_data['use_report_captcha'] = nel_site_domain()->setting('enable_captchas') && $this->domain->setting('use_report_captcha');
        $this->render_data['captcha_gen_url'] = nel_build_router_url([Domain::SITE, 'captcha', 'get']);
        $this->render_data['captcha_regen_url'] = nel_build_router_url([Domain::SITE, 'captcha', 'regenerate']);
        $this->render_data['use_report_recaptcha'] = nel_site_domain()->setting('enable_captchas') && $this->domain->setting('use_report_recaptcha');
        $this->render_data['recaptcha_sitekey'] = $this->site_domain->setting('recaptcha_site_key');

        if (!$expand && !$collapse) {
            $this->render_data['index_navigation'] = true;
            $this->render_data['footer_form'] = true;
            $output_footer = new OutputFooter($this->domain, $this->write_mode);
            $this->render_data['footer'] = $output_footer->render([], true);
        }

        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        $first_posts_format = $thread->pageBasename() . $this->site_domain->setting('first_posts_filename_format');

        foreach ($first_posts as $increment => $posts) {
            $this->render_data['thread_posts'] = $posts;
            $first_output = $this->output('basic_page', $data_only, true, $this->render_data);

            if ($this->write_mode) {
                $this->file_handler->writeFile(
                    $this->domain->reference('page_path') . $thread_id . '/' . sprintf($first_posts_format, $increment) .
                    NEL_PAGE_EXT, $first_output, true);
            }
        }

        $last_posts_format = $thread->pageBasename() . $this->site_domain->setting('last_posts_filename_format');

        foreach ($last_posts as $increment => $posts) {
            $this->render_data['thread_posts'] = $posts;
            $last_output = $this->output('basic_page', $data_only, true, $this->render_data);

            if ($this->write_mode) {
                $this->file_handler->writeFile(
                    $this->domain->reference('page_path') . $thread_id . '/' . sprintf($last_posts_format, $increment) .
                    NEL_PAGE_EXT, $last_output, true);
            }
        }

        if ($this->write_mode) {
            $this->file_handler->writeFile(
                $this->domain->reference('page_path') . $thread_id . '/' . $thread->pageBasename() . NEL_PAGE_EXT,
                $output, true);

            if (NEL_ENABLE_JSON_API) {
                $json_filename = $thread->contentID()->threadID() . NEL_JSON_EXT;
                $this->file_handler->writeFile($thread->pageFilePath() . $json_filename,
                    $thread->getJSON()->getJSON(true));
            }
        } else {
            echo $output;
            nel_clean_exit();
        }
    }

    private function lastPosts()
    {}
}