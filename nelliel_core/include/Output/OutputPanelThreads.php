<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelThreads extends OutputCore
{

    function __construct(Domain $domain, bool $write_mode)
    {
        $this->domain = $domain;
        $this->write_mode = $write_mode;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        if (!isset($parameters['section']))
        {
            return;
        }

        switch ($parameters['section'])
        {
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
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $manage_headers = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Threads')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'manage_headers' => $manage_headers], true);
        $thread_data = $this->database->executeFetchAll(
                'SELECT * FROM "' . $this->domain->reference('threads_table') .
                '" ORDER BY "sticky" DESC, "last_update" DESC', PDO::FETCH_ASSOC);
        $bgclass = 'row1';

        foreach ($thread_data as $thread)
        {
            $thread_info = array();
            $thread_info['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . $this->domain->reference('posts_table') . '" WHERE "post_number" = ?');
            $prepared->bindValue(1, $thread['first_post'], PDO::PARAM_INT);
            $prepared->execute();
            $op_post = $prepared->fetch(PDO::FETCH_ASSOC);
            $base_content_id = 'cid_' . $thread['thread_id'] . '_0_0';
            $thread_info['expand_url'] = '?module=admin&section=threads&board_id=' . $this->domain->id() .
                    '&actions=expand-thread&content-id=' . $base_content_id;
            $thread_info['thread_id'] = $thread['thread_id'];

            if ($thread['sticky'] == 1)
            {
                $thread_info['sticky_url'] = '?module=admin&section=threads&board_id=' . $this->domain->id() .
                        '&actions=unsticky&content-id=' . $base_content_id;
                $thread_info['sticky_text'] = _gettext('Unsticky Thread');
            }
            else
            {
                $thread_info['sticky_url'] = '?module=admin&section=threads&board_id=' . $this->domain->id() .
                        '&actions=sticky&content-id=' . $base_content_id;
                $thread_info['sticky_text'] = _gettext('Sticky Thread');
            }

            if ($thread['locked'] == 1)
            {
                $thread_info['lock_url'] = '?module=admin&section=threads&board_id=' . $this->domain->id() .
                        '&actions=unlock&content-id=' . $base_content_id;
                $thread_info['lock_text'] = _gettext('Unlock Thread');
            }
            else
            {
                $thread_info['lock_url'] = '?module=admin&section=threads&board_id=' . $this->domain->id() .
                        '&actions=lock&content-id=' . $base_content_id;
                $thread_info['lock_text'] = _gettext('Lock Thread');
            }

            $thread_info['delete_url'] = '?module=admin&section=threads&board_id=' . $this->domain->id() .
                    '&actions=delete&content-id=' . $base_content_id;
            $thread_info['delete_text'] = _gettext('Delete Thread');
            $thread_info['last_update'] = date($this->domain->setting('date_format'), $thread['last_update']);
            $thread_info['subject'] = $op_post['subject'];
            $thread_info['thread_url'] = $this->domain->reference('page_dir') . '/' . $thread['thread_id'] . '/' .
                    $thread['thread_id'] . '.html';
            $thread_info['op_name'] = $op_post['poster_name'];

            if ($user->checkPermission($this->domain, 'perm_view_unhashed_ip') && !empty($post_data['ip_address']))
            {
                $thread_info['op_ip'] = @inet_ntop($op_post['ip_address']);
            }
            else
            {
                $thread_info['op_ip'] = bin2hex($op_post['hashed_ip_address']);
            }

            $thread_info['post_count'] = $thread['post_count'];
            $thread_info['content_count'] = $thread['content_count'];
            $this->render_data['threads'][] = $thread_info;
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/thread_panel',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }

    private function renderExpandedThread(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $thread_id = $parameters['thread_id'] ?? 0;
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $manage_headers = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Expanded Thread')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'manage_headers' => $manage_headers], true);
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . $this->domain->reference('posts_table') .
                '" WHERE "parent_thread" = ? ORDER BY "post_time" DESC');
        $post_data = $this->database->executePreparedFetchAll($prepared, [$thread_id], PDO::FETCH_ASSOC);
        $bgclass = 'row1';

        foreach ($post_data as $post)
        {
            $post_info = array();
            $post_info['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $base_content_id = 'cid_' . $post['parent_thread'] . '_' . $post['post_number'] . '_0';
            $post_info['post_number'] = $post['post_number'];
            $post_info['delete_url'] = '?module=admin&section=threads&board_id=' . $this->domain->id() .
                    '&actions=delete&content-id=' . $base_content_id;
            $post_info['delete_text'] = _gettext('Delete Post');
            $post_info['sticky_url'] = '?module=admin&section=threads&board_id=' . $this->domain->id() .
                    '&actions=sticky&content-id=' . $base_content_id;
            $post_info['sticky_text'] = _gettext('Sticky Post');
            $post_info['parent_thread'] = $post['parent_thread'];
            $post_info['post_time'] = date($this->domain->setting('date_format'), $post['post_time']);
            $post_info['subject'] = $post['subject'];
            $post_info['thread_url'] = $this->domain->reference('page_dir') . '/' . $post['parent_thread'] . '/' .
                    $post['post_number'] . '.html';
            $post_info['poster_name'] = $post['poster_name'];
            $post_info['poster_ip'] = @inet_ntop($post['ip_address']);
            $post_info['email'] = $post['email'];
            $post_info['comment'] = $post['comment'];
            $this->render_data['posts'][] = $post_info;
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/thread_panel_expand',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}