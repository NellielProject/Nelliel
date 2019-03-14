<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_thread_panel_main($user, \Nelliel\Domain $domain)
{
    if (!$user->domainPermission($domain, 'perm_threads_access'))
    {
        nel_derp(350, _gettext('You are not allowed to access the threads panel.'));
    }

    $database = $domain->database();
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    $output_header = new \Nelliel\Output\OutputHeader($domain);
    $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Threads')];
    $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/thread_panel.html');
    $thread_data = $database->executeFetchAll(
            'SELECT * FROM "' . $domain->reference('threads_table') . '" ORDER BY "sticky" DESC, "last_update" DESC',
            PDO::FETCH_ASSOC);
    $thread_list_table = $dom->getElementById('thread-list');
    $thread_row = $dom->getElementById('thread-row-');
    $i = 0;

    foreach ($thread_data as $thread)
    {
        $temp_thread_row = $thread_row->cloneNode(true);
        $temp_thread_row->changeId('thread_row-' . $thread['thread_id']);

        $prepared = $database->prepare(
                'SELECT * FROM "' . $domain->reference('posts_table') . '" WHERE "post_number" = ?');
        $prepared->bindValue(1, $thread['first_post'], PDO::PARAM_INT);
        $prepared->execute();
        $op_post = $prepared->fetch(PDO::FETCH_ASSOC);
        unset($result);

        $base_content_id = 'cid_' . $thread['thread_id'] . '_0_0';

        $expand_link = $temp_thread_row->getElementById('expand-link-');
        $expand_link->changeId('expand-link-' . $thread['thread_id']);
        $expand_link->extSetAttribute('href',
                '?module=threads-admin&board_id=' . $domain->id() . '&action=expand-thread&content-id=' .
                $base_content_id);
        $expand_link->setContent(_gettext('Expand Thread'));

        $thread_post_number = $temp_thread_row->getElementById('thread-post-number-');
        $thread_post_number->setContent($thread['thread_id']);
        $thread_post_number->changeId('thread-post-number-' . $thread['thread_id']);

        $sticky_link = $temp_thread_row->getElementById('sticky-link-');
        $sticky_link->changeId('sticky-link-' . $thread['thread_id']);

        if ($thread['sticky'] == 1)
        {
            $sticky_link->extSetAttribute('href',
                    '?module=threads-admin&board_id=' . $domain->id() . '&action=unsticky&content-id=' . $base_content_id);
            $sticky_link->setContent(_gettext('Unsticky Thread'));
        }
        else
        {
            $sticky_link->extSetAttribute('href',
                    '?module=threads-admin&board_id=' . $domain->id() . '&action=sticky&content-id=' . $base_content_id);
            $sticky_link->setContent(_gettext('Sticky Thread'));
        }

        $lock_link = $temp_thread_row->getElementById('lock-link-');
        $lock_link->changeId('lock-link-' . $thread['thread_id']);

        if ($thread['locked'] == 1)
        {
            $lock_link->extSetAttribute('href',
                    '?module=threads-admin&board_id=' . $domain->id() . '&action=unlock&content-id=' . $base_content_id);
            $lock_link->setContent(_gettext('Unlock Thread'));
        }
        else
        {
            $lock_link->extSetAttribute('href',
                    '?module=threads-admin&board_id=' . $domain->id() . '&action=lock&content-id=' . $base_content_id);
            $lock_link->setContent(_gettext('Lock Thread'));
        }

        $delete_link = $temp_thread_row->getElementById('delete-link-');
        $delete_link->changeId('delete-link-' . $thread['thread_id']);
        $delete_link->extSetAttribute('href',
                '?module=threads-admin&board_id=' . $domain->id() . '&action=delete&content-id=' . $base_content_id);
        $delete_link->setContent(_gettext('Delete Thread'));

        $thread_last_update = $temp_thread_row->getElementById('thread-last-update-');
        $thread_last_update->setContent(date($domain->setting('date_format'), $thread['last_update']));
        $thread_last_update->changeId('thread-last-update-' . $thread['thread_id']);

        $thread_subject_link = $temp_thread_row->getElementById('thread-subject-link-');
        $thread_subject_link->setContent($op_post['subject']);
        $thread_subject_link->extSetAttribute('href',
                $domain->reference('page_dir') . '/' . $thread['thread_id'] . '/' . $thread['thread_id'] . '.html',
                'none');
        $thread_subject_link->changeId('thread-subject-link-' . $thread['thread_id']);

        $thread_op_name = $temp_thread_row->getElementById('thread-op-name-');
        $thread_op_name->setContent($op_post['poster_name']);
        $thread_op_name->changeId('thread-op-name-' . $thread['thread_id']);
        $thread_op_ip = $temp_thread_row->getElementById('thread-op-ip-');
        $thread_op_ip->setContent(@inet_ntop($op_post['ip_address']));
        $thread_op_ip->changeId('thread-op-ip-' . $thread['thread_id']);
        $thread_post_count = $temp_thread_row->getElementById('thread-post-count-');
        $thread_post_count->setContent($thread['post_count']);
        $thread_post_count->changeId('thread-post-count-' . $thread['thread_id']);
        $thread_total_files = $temp_thread_row->getElementById('thread-total-files-');
        $thread_total_files->setContent($thread['total_files']);
        $thread_total_files->changeId('thread-total-files-' . $thread['thread_id']);

        if ($i & 1)
        {
            $bgclass = 'row1';
        }
        else
        {
            $bgclass = 'row2';
        }

        $temp_thread_row->extSetAttribute('class', $bgclass);
        $thread_list_table->appendChild($temp_thread_row);
        $i ++;
    }

    $thread_row->remove();

    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}

function nel_render_thread_panel_expand($user, \Nelliel\Domain $domain, $thread_id)
{
    if (!$user->domainPermission($domain, 'perm_threads_access'))
    {
        nel_derp(350, _gettext('You are not allowed to access the threads panel.'));
    }

    $database = $domain->database();
    $authorization = new \Nelliel\Auth\Authorization($database);
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    $output_header = new \Nelliel\Output\OutputHeader($domain);
    $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Expanded Thread')];
    $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/thread_panel_expand.html');
    $dom->getElementById('thread-list-form')->extSetAttribute('action',
            MAIN_SCRIPT . '?module=threads&action=update&board_id=' . $domain->id());
    $prepared = $database->prepare(
            'SELECT * FROM "' . $domain->reference('posts_table') .
            '" WHERE "parent_thread" = ? ORDER BY "post_time" DESC');
    $post_data = $database->executePreparedFetchAll($prepared, array($thread_id), PDO::FETCH_ASSOC);
    $post_list_table = $dom->getElementById('post-list');
    $post_row = $dom->getElementById('post-row-');
    $i = 0;

    foreach ($post_data as $post)
    {
        $temp_post_row = $post_row->cloneNode(true);
        $temp_post_row->changeId('post-row-' . $post['post_number']);

        $base_content_id = 'cid_' . $post['parent_thread'] . '_' . $post['post_number'] . '_0';

        $post_post_number = $temp_post_row->getElementById('post-post-number-');
        $post_post_number->setContent($post['post_number']);
        $post_post_number->changeId('post-post-number-' . $post['post_number']);
        $delete_link = $temp_post_row->getElementById('delete-link-');
        $delete_link->changeId('delete-link-' . $post['post_number']);
        $delete_link->extSetAttribute('href',
                '?module=threads-admin&board_id=' . $domain->id() . '&action=delete&content-id=' . $base_content_id);
        $delete_link->setContent(_gettext('Delete Post'));
        $sticky_link = $temp_post_row->getElementById('sticky-link-');
        $sticky_link->changeId('sticky-link-' . $post['post_number']);
        $sticky_link->extSetAttribute('href',
                '?module=threads-admin&board_id=' . $domain->id() . '&action=sticky&content-id=' . $base_content_id);
        $sticky_link->setContent(_gettext('Sticky Post'));
        $post_parent_thread = $temp_post_row->getElementById('post-thread-');
        $post_parent_thread->setContent($post['parent_thread']);
        $post_parent_thread->changeId('post-thread-' . $post['parent_thread']);
        $post_last_update = $temp_post_row->getElementById('post-time-');
        $post_last_update->setContent(date($domain->setting('date_format'), $post['post_time']));
        $post_last_update->changeId('post-time-' . $post['post_number']); /////
        $post_subject_link = $temp_post_row->getElementById('post-subject-link-');
        $post_subject_link->setContent($post['subject']);
        $post_subject_link->extSetAttribute('href',
                $domain->reference('page_dir') . '/' . $post['parent_thread'] . '/' . $post['post_number'] . '.html',
                'none');
        $post_subject_link->changeId('post-subject-link-' . $post['post_number']);

        $post_name = $temp_post_row->getElementById('post-name-');
        $post_name->setContent($post['poster_name']);
        $post_name->changeId('post-name-' . $post['post_number']);
        $post_ip = $temp_post_row->getElementById('post-ip-');
        $post_ip->setContent(@inet_ntop($post['ip_address']));
        $post_ip->changeId('post-ip-' . $post['post_number']);

        if ($i & 1)
        {
            $bgclass = 'row1';
        }
        else
        {
            $bgclass = 'row2';
        }

        $temp_post_row->extSetAttribute('class', $bgclass);
        $post_list_table->appendChild($temp_post_row);
        $i ++;
    }

    $post_row->remove();

    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}