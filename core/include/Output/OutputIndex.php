<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\API\JSON\CatalogJSON;
use Nelliel\API\JSON\IndexJSON;
use Nelliel\Content\Post;
use Nelliel\Content\Thread;
use Nelliel\Domains\DomainBoard;
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
        $page = $parameters['page'] ?? 1;
        $page_title = $this->domain->reference('title');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render(['page_title' => $page_title], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['in_modmode'] = $this->session->inModmode($this->domain) && !$this->write_mode;

        if ($this->render_data['in_modmode']) {
            $manage_headers['header'] = _gettext('Moderator Mode');
            $manage_headers['sub_header'] = _gettext('View Index');
            $this->render_data['header'] = $output_header->board(['manage_headers' => $manage_headers], true);
            $this->render_data['form_action'] = nel_build_router_url([$this->domain->id(), 'threads'], false, 'modmode');
        } else {
            $this->render_data['header'] = $output_header->board([], true);
            $this->render_data['form_action'] = nel_build_router_url([$this->domain->id(), 'threads']);
        }

        $output_navigation = new OutputNavigation($this->domain, $this->write_mode);
        $this->render_data['page_navigation'] = $output_navigation->boardPages(
            ['in_modmode' => $this->render_data['in_modmode']], $data_only);

        $threads = $this->domain->activeThreads(true);
        $thread_count = count($threads);
        $threads_done = 0;
        $gen_data = array();
        $gen_data['index']['thread_count'] = $thread_count;
        $output_new_post_form = new OutputNewPostForm($this->domain, $this->write_mode);
        $this->render_data['new_post_form'] = $output_new_post_form->render(['response_to' => 0], true);

        if ($thread_count === 0) {
            $page_count = 1;
        } else {
            $page_count = (int) ceil($thread_count / $this->domain->setting('threads_per_page'));
        }

        $this->render_data['show_global_announcement'] = !nel_true_empty(
            $this->site_domain->setting('global_announcement'));
        $this->render_data['global_announcement_text'] = $this->site_domain->setting('global_announcement');

        $blotter_limit = $this->site_domain->setting('small_blotter_limit');
        $query = 'SELECT * FROM "' . NEL_BLOTTER_TABLE . '" ORDER BY "time" DESC LIMIT ' . $blotter_limit;
        $blotter_entries = $this->database->executeFetchAll($query, PDO::FETCH_ASSOC);

        if ($this->site_domain->setting('show_blotter') && !empty($blotter_entries)) {
            foreach ($blotter_entries as $entry) {
                $blotter_data = array();
                $blotter_data['time'] = $this->domain->domainDateTime(intval($entry['time']))->format(
                    $this->site_domain->setting('blotter_time_format'));
                $blotter_data['text'] = $entry['text'];
                $this->render_data['blotter_entries'][] = $blotter_data;
            }

            $this->render_data['show_blotter'] = isset($this->render_data['blotter_entries']) &&
                !empty($this->render_data['blotter_entries']);
            $this->render_data['blotter_url'] = NEL_BASE_WEB_PATH . 'blotter.html';
        }

        $this->render_data['index_navigation_top'] = $this->domain->setting('index_nav_top');
        $this->render_data['index_navigation_bottom'] = $this->domain->setting('index_nav_bottom');
        $this->render_data['footer_form'] = true;
        $this->render_data['show_styles'] = true;
        $output_menu = new OutputMenu($this->domain, $this->write_mode);
        $this->render_data['styles'] = $output_menu->styles([], true);

        if ($this->site_domain->setting('enable_captchas') && $this->domain->setting('use_report_captcha')) {
            $this->render_data['use_report_captcha'] = true;
            $output_native_captchas = new OutputCAPTCHA($this->domain, $this->write_mode);
            $this->render_data['report_captchas'] = $output_native_captchas->render(['area' => 'report'], false);
        }

        if (empty($threads)) {
            $index_format = $this->site_domain->setting('first_index_filename_format');
            $output = $this->doOutput($gen_data, sprintf($index_format, ($page)), $page, $data_only);

            if (!$this->write_mode) {
                return $output;
            }
        }

        $gen_data['index_rendering'] = true;
        $threads_on_page = 0;

        $this->render_data['threads'] = array();

        foreach ($threads as $thread) {
            if (is_null($thread) || !$thread->exists()) {
                continue;
            }

            $thread_input = array();
            $index_format = ($page === 1) ? $this->site_domain->setting('first_index_filename_format') : $this->site_domain->setting(
                'index_filename_format');
            $posts = $thread->getPosts();

            if (empty($posts)) {
                $threads_done ++;
                continue;
            }

            $output_post = new OutputPost($this->domain, $this->write_mode);
            $thread_input = array();
            $thread_input['thread_id'] = $thread->contentID()->getIDString();
            $thread_input['thread_expand_id'] = 'thread-expand-' . $thread->contentID()->getIDString();
            $thread_input['thread_corral_id'] = 'thread-corral-' . $thread->contentID()->getIDString();
            $index_replies = $this->domain->setting('index_thread_replies');

            if ($thread->data('sticky')) {
                $index_replies = $this->domain->setting('index_sticky_replies');
            }

            $thread_input['omitted_count'] = $thread->data('post_count') - $index_replies - 1; // Subtract 1 to account for OP
            $gen_data['abbreviate'] = $thread_input['omitted_count'] > 0;
            $thread_input['abbreviate'] = $gen_data['abbreviate'];
            $abbreviate_start = $thread->data('post_count') - $index_replies;

            if ($this->session->inModmode($this->domain) && !$this->write_mode) {
                $modmode_options = $this->modmodeHeaders($thread);
                $thread_input['thread_modmode_options'] = $modmode_options['thread_modmode_options'];
                $thread_input['post_modmode_options'] = $modmode_options['post_modmode_options'];
            }

            $options = $this->threadHeaders($thread, $thread->firstPost(), $gen_data, $thread->contentID()->threadID());
            $thread_input['thread_options'] = $options['thread'];
            $post_counter = 1;

            foreach ($posts as $post) {
                $parameters = ['gen_data' => $gen_data, 'in_thread_number' => $post_counter];

                if ($post->data('op') == 1) {
                    $thread_input['op_post'] = $output_post->render($post, $parameters, true);
                } else {
                    if ($post_counter > $abbreviate_start) {
                        $thread_input['thread_posts'][] = $output_post->render($post, $parameters, true);
                    }
                }

                $post_counter ++;
            }

            $this->render_data['threads'][] = $thread_input;
            $threads_on_page ++;
            $threads_done ++;

            if ($threads_on_page >= $this->domain->setting('threads_per_page')) {
                $this->render_data['pagination'] = $this->indexNavigation($page, $page_count);
                $output = $this->doOutput($gen_data, sprintf($index_format, ($page)), $page, $data_only);

                if (!$this->write_mode) {
                    return $output;
                }

                $threads_on_page = 0;
                $this->render_data['threads'] = array();
                $this->timer->reset();
                $this->timer->start();
                $page ++;
            }
        }

        if (NEL_ENABLE_JSON_API) {
            $catalog_json = new CatalogJSON($this->domain, $page);
            $json_filename = 'catalog' . NEL_JSON_EXT;
            $this->file_handler->writeFile($this->domain->reference('base_path') . $json_filename,
                $catalog_json->getJSON(true));
        }

        $this->render_data['pagination'] = $this->indexNavigation($page, $page_count);
        $output = $this->doOutput($gen_data, sprintf($index_format, ($page)), $page, $data_only);
        return $output;
    }

    private function indexNavigation(int $page, int $page_count)
    {
        $pagination_object = new Pagination();
        $pagination_object->setPrevious(_gettext('Previous'));
        $pagination_object->setNext(_gettext('Next'));
        $pagination_object->setPage('%d', $this->site_domain->setting('index_filename_format') . NEL_PAGE_EXT);
        $pagination_object->setFirst('%d', $this->site_domain->setting('first_index_filename_format') . NEL_PAGE_EXT);
        $pagination_object->setLast('%d', $this->site_domain->setting('index_filename_format') . NEL_PAGE_EXT);
        return $pagination_object->generateNumerical(1, $page_count, $page);
    }

    private function doOutput(array $gen_data, string $index_basename, int $page, bool $data_only)
    {
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);

        if ($this->write_mode) {
            $this->file_handler->writeFile($this->domain->reference('base_path') . $index_basename . NEL_PAGE_EXT,
                $output);

            if (NEL_ENABLE_JSON_API) {
                $index_json = new IndexJSON($this->domain, $page);
                $json_filename = $page . NEL_JSON_EXT;
                $this->file_handler->writeFile($this->domain->reference('base_path') . $json_filename,
                    $index_json->getJSON(true));
            }
        } else {
            echo $output;
        }

        return $output;
    }

    private function modmodeHeaders(Thread $thread): array
    {
        $post = $thread->firstPost();
        $options = array();

        if ($this->session->user()->checkPermission($this->domain, 'perm_view_unhashed_ip') &&
            !empty($post->data('ip_address'))) {
            $ip = $post->data('ip_address');
        } else {
            $ip = $post->data('hashed_ip_address');
        }

        $this->render_data['mod_ip_address'] = $ip;
        $this->render_data['in_modmode'] = true;

        if ($this->session->user()->checkPermission($this->domain, 'perm_modify_content_status')) {
            $this->render_data['mod_links_lock']['url'] = nel_build_router_url(
                [$this->domain->id(), 'moderation', 'modmode', $thread->contentID()->getIDString(), 'lock']);
            $this->render_data['mod_links_unlock']['url'] = nel_build_router_url(
                [$this->domain->id(), 'moderation', 'modmode', $thread->contentID()->getIDString(), 'unlock']);
            $lock_id = $thread->data('locked') ? 'mod_links_unlock' : 'mod_links_lock';
            $options['thread_modmode_options'][] = $this->render_data[$lock_id];

            $this->render_data['mod_links_sticky']['url'] = nel_build_router_url(
                [$this->domain->id(), 'moderation', 'modmode', $thread->contentID()->getIDString(), 'sticky']);
            $this->render_data['mod_links_unsticky']['url'] = nel_build_router_url(
                [$this->domain->id(), 'moderation', 'modmode', $thread->contentID()->getIDString(), 'unsticky']);
            $sticky_id = $thread->data('sticky') ? 'mod_links_unsticky' : 'mod_links_sticky';
            $options['thread_modmode_options'][] = $this->render_data[$sticky_id];

            $this->render_data['mod_links_permasage']['url'] = nel_build_router_url(
                [$this->domain->id(), 'moderation', 'modmode', $thread->contentID()->getIDString(), 'sage']);
            $this->render_data['mod_links_unpermasage']['url'] = nel_build_router_url(
                [$this->domain->id(), 'moderation', 'modmode', $thread->contentID()->getIDString(), 'unsage']);
            $permasage_id = $thread->data('permasage') ? 'mod_links_unpermasage' : 'mod_links_permasage';
            $options['thread_modmode_options'][] = $this->render_data[$permasage_id];

            $this->render_data['mod_links_cyclic']['url'] = nel_build_router_url(
                [$this->domain->id(), 'moderation', 'modmode', $thread->contentID()->getIDString(), 'cyclic']);
            $this->render_data['mod_links_non_cyclic']['url'] = nel_build_router_url(
                [$this->domain->id(), 'moderation', 'modmode', $thread->contentID()->getIDString(), 'non-cyclic']);
            $cyclic_id = $thread->data('cyclic') ? 'mod_links_non_cyclic' : 'mod_links_cyclic';
            $options['thread_modmode_options'][] = $this->render_data[$cyclic_id];
        }

        if (!$thread->data('shadow')) {
            if ($this->session->user()->checkPermission($this->domain, 'perm_move_content')) {
                $this->render_data['mod_links_move']['url'] = nel_build_router_url(
                    [$this->domain->id(), 'moderation', 'modmode', $thread->contentID()->getIDString(), 'move']);
                $options['thread_modmode_options'][] = $this->render_data['mod_links_move'];
            }

            if ($this->session->user()->checkPermission($this->domain, 'perm_merge_threads')) {
                $this->render_data['mod_links_merge']['url'] = nel_build_router_url(
                    [$this->domain->id(), 'moderation', 'modmode', $thread->contentID()->getIDString(), 'merge']);
                $options['thread_modmode_options'][] = $this->render_data['mod_links_merge'];
            }
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_bans')) {
            $this->render_data['mod_links_ban']['url'] = nel_build_router_url(
                [$this->domain->id(), 'moderation', 'modmode', $post->contentID()->getIDString(), 'ban']);
            $options['post_modmode_options'][] = $this->render_data['mod_links_ban'];
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_delete_content')) {
            $this->render_data['mod_links_delete']['url'] = nel_build_router_url(
                [$this->domain->id(), 'moderation', 'modmode', $post->contentID()->getIDString(), 'delete']);
            $options['post_modmode_options'][] = $this->render_data['mod_links_delete'];
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_delete_by_ip')) {
            $this->render_data['mod_links_delete_by_ip']['url'] = nel_build_router_url(
                [$this->domain->id(), 'moderation', 'modmode', $post->contentID()->getIDString(), 'delete-by-ip']);
            $this->render_data['post_modmode_options'][] = $this->render_data['mod_links_delete_by_ip'];

            $this->render_data['mod_links_global_delete_by_ip']['url'] = nel_build_router_url(
                [$this->domain->id(), 'moderation', 'modmode', $post->contentID()->getIDString(), 'global-delete-by-ip']);
            $options['post_modmode_options'][] = $this->render_data['mod_links_global_delete_by_ip'];
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_bans') &&
            $this->session->user()->checkPermission($this->domain, 'perm_delete_content')) {
            $this->render_data['mod_links_ban_and_delete']['url'] = nel_build_router_url(
                [$this->domain->id(), 'moderation', 'modmode', $post->contentID()->getIDString(), 'ban-delete']);
            $options['post_modmode_options'][] = $this->render_data['mod_links_ban_and_delete'];
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_edit_posts')) {
            $this->render_data['mod_links_edit']['url'] = nel_build_router_url(
                [$this->domain->id(), 'moderation', 'modmode', $post->contentID()->getIDString(), 'edit']);
            $options['post_modmode_options'][] = $this->render_data['mod_links_edit'];
        }

        if (!$thread->data('shadow')) {
            if ($this->session->user()->checkPermission($this->domain, 'perm_move_content')) {
                $this->render_data['mod_links_move']['url'] = nel_build_router_url(
                    [$this->domain->id(), 'moderation', 'modmode', $post->contentID()->getIDString(), 'move']);
                $options['post_modmode_options'][] = $this->render_data['mod_links_move'];
            }
        }

        return $options;
    }

    private function threadHeaders(Thread $thread, Post $post, array $gen_data, int $in_thread_number): array
    {
        $thread_headers = array();
        $options = array();
        $this->render_data['headers'] = array();
        $post_content_id = $post->contentID();

        $this->render_data['headers']['thread_url'] = $thread->getURL(!$this->write_mode);
        $thread_headers['thread_content_id'] = $thread->contentID()->getIDString();
        $thread_headers['post_content_id'] = $post_content_id->getIDString();
        $thread_headers['index_render'] = true;

        if ($gen_data['abbreviate']) {
            if ($this->session->inModmode($this->domain)) {
                $this->render_data['content_links_expand_thread']['url'] = $thread->getURL(!$this->write_mode,
                    'expand&modmode');
                $this->render_data['content_links_expand_thread']['alt_url'] = $thread->getURL(!$this->write_mode,
                    'collapse&modmode');
            } else {
                $this->render_data['content_links_expand_thread']['url'] = $thread->getURL(!$this->write_mode, 'expand');
                $this->render_data['content_links_expand_thread']['alt_url'] = $thread->getURL(!$this->write_mode,
                    'collapse');
            }

            $this->render_data['content_links_expand_thread']['query_class'] = 'js-hide-thread';
            $this->render_data['content_links_expand_thread']['content_id'] = $thread->contentID()->getIDString();
            $options['thread'][] = $this->render_data['content_links_expand_thread'];
        }

        if ($this->session->inModmode($this->domain)) {
            $this->render_data['content_links_reply']['url'] = $thread->getURL(!$this->write_mode, 'modmode');
        } else {
            $this->render_data['content_links_reply']['url'] = $thread->getURL(!$this->write_mode);
        }

        $this->render_data['content_links_reply']['query_class'] = 'js-hide-thread';
        $this->render_data['content_links_reply']['content_id'] = $thread->contentID()->getIDString();
        $options['thread'][] = $this->render_data['content_links_reply'];
        $this->render_data['content_links_hide_thread']['content_id'] = $post->contentID()->getIDString();
        $options['thread'][] = $this->render_data['content_links_hide_thread'];

        $first_posts_increments = json_decode($this->domain->setting('first_posts_increments'));
        $first_posts_format = $thread->pageBasename() . $this->site_domain->setting('first_posts_filename_format');

        if (is_array($first_posts_increments) &&
            $thread->data('post_count') > $this->domain->setting('first_posts_threshold')) {
            foreach ($first_posts_increments as $increment) {
                if ($thread->data('post_count') >= $increment) {
                    $this->render_data['content_links_first_posts']['url'] = $this->domain->reference('page_web_path') .
                        $thread->contentID()->threadID() . '/' . sprintf($first_posts_format, $increment) . NEL_PAGE_EXT;
                    $this->render_data['content_links_first_posts']['text'] = sprintf(
                        $this->render_data['content_links_first_posts']['text'], $increment);
                    $this->render_data['content_links_first_posts']['query_class'] = 'js-hide-thread';
                    $options['thread'][] = $this->render_data['content_links_first_posts'];
                }
            }
        }

        $last_posts_increments = json_decode($this->domain->setting('last_posts_increments'));
        $last_posts_format = $thread->pageBasename() . $this->site_domain->setting('last_posts_filename_format');

        if (is_array($last_posts_increments) &&
            $thread->data('post_count') > $this->domain->setting('last_posts_threshold')) {
            foreach ($last_posts_increments as $increment) {
                if ($thread->data('post_count') >= $increment) {
                    $this->render_data['content_links_last_posts']['url'] = $this->domain->reference('page_web_path') .
                        $thread->contentID()->threadID() . '/' . sprintf($last_posts_format, $increment) . NEL_PAGE_EXT;
                    $this->render_data['content_links_last_posts']['text'] = sprintf(
                        $this->render_data['content_links_last_posts']['text'], $increment);
                    $this->render_data['content_links_last_posts']['query_class'] = 'js-hide-thread';
                    $options['thread'][] = $this->render_data['content_links_last_posts'];
                }
            }
        }

        $this->render_data['headers']['thread_headers'] = $thread_headers;
        return $options;
    }
}