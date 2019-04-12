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

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters = array(), bool $data_only = false)
    {
        if (!isset($parameters['section']))
        {
            return;
        }

        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_threads_access'))
        {
            nel_derp(341, _gettext('You are not allowed to access the bans panel.'));
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
        $render_data = array();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $render_data['head'] = $output_head->render(['dotdot' => $dotdot]);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Threads')];
        $render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'extra_data' => $extra_data], true);
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
            $thread_info['expand_url'] = '?module=threads-admin&board_id=' . $this->domain->id() .
                    '&action=expand-thread&content-id=' . $base_content_id;
            $thread_info['thread_id'] = $thread['thread_id'];

            if ($thread['sticky'] == 1)
            {
                $thread_info['sticky_url'] = '?module=threads-admin&board_id=' . $this->domain->id() .
                        '&action=unsticky&content-id=' . $base_content_id;
                $thread_info = _gettext('Unsticky Thread');
            }
            else
            {
                $thread_info['sticky_url'] = '?module=threads-admin&board_id=' . $this->domain->id() .
                        '&action=sticky&content-id=' . $base_content_id;
                $thread_info['sticky_text'] = _gettext('Sticky Thread');
            }

            if ($thread['locked'] == 1)
            {
                $thread_info['lock_url'] = '?module=threads-admin&board_id=' . $this->domain->id() .
                        '&action=unlock&content-id=' . $base_content_id;
                $thread_info['lock_text'] = _gettext('Unlock Thread');
            }
            else
            {
                $thread_info['lock_url'] = '?module=threads-admin&board_id=' . $this->domain->id() .
                        '&action=lock&content-id=' . $base_content_id;
                $thread_info['lock_text'] = _gettext('Lock Thread');
            }

            $thread_info['delete_url'] = '?module=threads-admin&board_id=' . $this->domain->id() .
                    '&action=delete&content-id=' . $base_content_id;
            $thread_info['delete_text'] = _gettext('Delete Thread');
            $thread_info['last_update'] = date($this->domain->setting('date_format'), $thread['last_update']);
            $thread_info['subject'] = $op_post['subject'];
            $thread_info['thread_url'] = $this->domain->reference('page_dir') . '/' . $thread['thread_id'] . '/' .
                    $thread['thread_id'] . '.html';
            $thread_info['op_name'] = $op_post['poster_name'];
            $thread_info['op_ip'] = @inet_ntop($op_post['ip_address']);
            $thread_info['post_count'] = $thread['post_count'];
            $thread_info['total_files'] = $thread['total_files'];
            $render_data['threads'][] = $thread_info;
        }

        $render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/thread_panel', $render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output($render_data, 'page', true);
        echo $output;
        return $output;
    }

    private function renderExpandedThread(array $parameters, bool $data_only)
    {
        $render_data = array();
        $thread_id = $parameters['thread_id'] ?? 0;
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $render_data['head'] = $output_head->render(['dotdot' => $dotdot]);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Expanded Thread')];
        $render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'extra_data' => $extra_data], true);
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
            $post_info['delete_url'] = '?module=threads-admin&board_id=' . $this->domain->id() .
                    '&action=delete&content-id=' . $base_content_id;
            $post_info['delete_text'] = _gettext('Delete Post');
            $post_info['sticky_url'] = '?module=threads-admin&board_id=' . $this->domain->id() .
                    '&action=sticky&content-id=' . $base_content_id;
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
            $render_data['posts'][] = $post_info;
        }

        $render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/thread_panel_expand',
                $render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output($render_data, 'page', true);
        echo $output;
        return $output;
    }
}