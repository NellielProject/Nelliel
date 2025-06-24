<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\ContentID;
use Nelliel\Domains\Domain;
use PDO;

class OutputPanelThreads extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        if (!isset($parameters['section'])) {
            return;
        }

        switch ($parameters['section']) {
            case 'panel':
                $output = $this->renderPanel($parameters, $data_only);
                break;

            case 'expanded_thread':
                $output = $this->renderExpandedThread($parameters, $data_only);
                break;
        }

        return $output;
    }

    private function renderPanel(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/thread');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Threads');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        /*
         * $thread_data = $this->database->executeFetchAll(
         * 'SELECT * FROM "' . $this->domain->reference('threads_table') .
         * '" WHERE "old" = 0 ORDER BY "sticky" DESC, "bump_time" DESC, "bump_time_milli" DESC',
         * PDO::FETCH_ASSOC);
         */
        $bgclass = 'row1';
        $threads = $this->domain->getThreads(true, false);

        foreach ($threads as $thread) {
            $thread_info = array();
            $thread_info['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $content_id = $thread->contentID();
            $op_content_id = new ContentID(
                'cid_' . $content_id->threadID() . '_' . $thread->firstPost->contentID()->postID() . '_0');
            $op_post = $op_content_id->getInstanceFromID($this->domain);
            $thread_info['thread_id'] = $content_id->threadID();

            if ($thread->getData('sticky')) {
                $thread_info['sticky_url'] = '?module=admin&section=threads&board-id=' . $this->domain->uri() .
                    '&actions=unsticky&content-id=' . $content_id->getIDString();
                $thread_info['sticky_text'] = _gettext('Unsticky Thread');
            } else {
                $thread_info['sticky_url'] = '?module=admin&section=threads&board-id=' . $this->domain->uri() .
                    '&actions=sticky&content-id=' . $content_id->getIDString();
                $thread_info['sticky_text'] = _gettext('Sticky Thread');
            }

            if ($thread->getData('locked')) {
                $thread_info['lock_url'] = '?module=admin&section=threads&board-id=' . $this->domain->uri() .
                    '&actions=unlock&content-id=' . $content_id->getIDString();
                $thread_info['lock_text'] = _gettext('Unlock Thread');
            } else {
                $thread_info['lock_url'] = '?module=admin&section=threads&board-id=' . $this->domain->uri() .
                    '&actions=lock&content-id=' . $content_id->getIDString();
                $thread_info['lock_text'] = _gettext('Lock Thread');
            }

            $thread_info['delete_url'] = '?module=admin&section=threads&board-id=' . $this->domain->uri() .
                '&actions=delete&content-id=' . $content_id->getIDString();
            $thread_info['delete_text'] = _gettext('Delete Thread');
            $thread_info['last_update'] = $this->domain->domainDateTime(intval($thread->getData('last_update')))->format(
                $this->domain->setting('post_time_format'));
            $thread_info['subject'] = $op_post->getData('subject');

            if($this->session->user()->checkPermission($this->domain, 'perm_mod_mode', false)) {
                $thread_info['thread_url'] = $thread->getRoute(true, 'modmode');
            } else {
                $thread_info['thread_url'] = $thread->getRoute();
            }

            if ($this->session->user()->checkPermission($this->domain, 'perm_view_unhashed_ip')) {
                $thread_info['op_ip'] = $op_post->getData('unhashed_ip_address');
            } else {
                $thread_info['op_ip'] = $op_post->getData('hashed_ip_address');
            }

            $thread_info['post_count'] = $thread->getData('post_count');
            $thread_info['total_uploads'] = $thread->getData('total_uploads');
            $thread_info['file_count'] = $thread->getData('file_count');
            $this->render_data['threads'][] = $thread_info;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    private function renderExpandedThread(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/thread_expand');
        $thread_id = $parameters['thread_id'] ?? 0;
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $manage_headers = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Expanded Thread')];
        $this->render_data['header'] = $output_header->general(['manage_headers' => $manage_headers], true);
        $prepared = $this->database->prepare(
            'SELECT * FROM "' . $this->domain->reference('posts_table') .
            '" WHERE "parent_thread" = ? ORDER BY "post_time" DESC');
        $post_data = $this->database->executePreparedFetchAll($prepared, [$thread_id], PDO::FETCH_ASSOC);
        $bgclass = 'row1';

        foreach ($post_data as $post) {
            $post_info = array();
            $post_info['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $base_content_id = 'cid_' . $post['parent_thread'] . '_' . $post['post_number'] . '_0';
            $post_info['post_number'] = $post['post_number'];
            $post_info['delete_url'] = '?module=admin&section=threads&board-id=' . $this->domain->uri() .
                '&actions=delete&content-id=' . $base_content_id;
            $post_info['delete_text'] = _gettext('Delete Post');
            $post_info['sticky_url'] = '?module=admin&section=threads&board-id=' . $this->domain->uri() .
                '&actions=sticky&content-id=' . $base_content_id;
            $post_info['sticky_text'] = _gettext('Sticky Post');
            $post_info['parent_thread'] = $post['parent_thread'];
            $post_info['post_time'] = $this->domain->domainDateTime(intval($post['post_time']))->format(
                $this->domain->setting('post_time_format'));
            $post_info['subject'] = $post['subject'];
            $post_info['thread_url'] = $this->domain->reference('page_directory') . '/' . $post['parent_thread'] . '/' .
                $post['post_number'] . '.html';
            $post_info['name'] = $post['name'];
            $post_info['poster_ip'] = $post['unhashed_ip_address'];
            $post_info['email'] = $post['email'];
            $post_info['comment'] = $post['comment'];
            $this->render_data['posts'][] = $post_info;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function editPost(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/threads_edit_post');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Threads');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit Post');
        $post = $parameters['post'] ?? null;
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $this->render_data['not_anonymous_value'] = $post->getData('name');
        $this->render_data['spam_target_value'] = $post->getData('email');
        $this->render_data['verb_value'] = $post->getData('subject');
        $this->render_data['wordswordswords_value'] = $post->getData('comment');
        $this->render_data['return_url'] = $_SERVER['HTTP_REFERER'] ?? '';
        $this->render_data['form_action'] = nel_build_router_url(
            [$this->domain->uri(), 'moderation', 'modmode', $post->contentID()->getIDString(), 'edit']);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function move(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/threads_move');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Threads');
        $parameters['section'] = $parameters['section'] ?? _gettext('Move');
        $content_id = $parameters['content_id'] ?? new ContentID();
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $output_menu = new OutputMenu($this->domain, $this->write_mode);
        $this->render_data['current_board'] = $this->domain->uri();
        $this->render_data['boards_select'] = $output_menu->boards('destination_board', $this->domain->uri(), true);
        $this->render_data['move_thread'] = $content_id->isThread();
        $this->render_data['move_post'] = $content_id->isPost();
        $this->render_data['move_upload'] = $content_id->isUpload();
        $this->render_data['return_url'] = $_SERVER['HTTP_REFERER'] ?? '';
        $this->render_data['allow_shadow_message'] = $this->domain->setting('allow_shadow_message');
        $this->render_data['form_action'] = nel_build_router_url(
            [$this->domain->uri(), 'moderation', 'modmode', $content_id->getIDString(), 'move']);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function merge(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/threads_merge');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Threads');
        $parameters['section'] = $parameters['section'] ?? _gettext('Merge');
        $content_id = $parameters['content_id'] ?? new ContentID();
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $output_menu = new OutputMenu($this->domain, $this->write_mode);
        $this->render_data['current_board'] = $this->domain->uri();
        $this->render_data['boards_select'] = $output_menu->boards('target_board', $this->domain->uri(), true);
        $this->render_data['return_url'] = $_SERVER['HTTP_REFERER'] ?? '';
        $this->render_data['allow_shadow_message'] = $this->domain->setting('allow_shadow_message');
        $this->render_data['form_action'] = nel_build_router_url(
            [$this->domain->uri(), 'moderation', 'modmode', $content_id->getIDString(), 'merge']);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}