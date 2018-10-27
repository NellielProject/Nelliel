<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_thread_panel_main($user, $board_id)
{
    if (!$user->boardPerm($board_id, 'perm_threads_access'))
    {
        nel_derp(350, _gettext('You are not allowed to access the threads panel.'));
    }

    $dbh = nel_database();
    $authorization = new \Nelliel\Auth\Authorization($dbh);
    $language = new \Nelliel\language\Language($authorization);
    $references = nel_parameters_and_data()->boardReferences($board_id);
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, $board_id,
            array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Threads')));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/thread_panel.html');
    $thread_data = $dbh->executeFetchAll(
            'SELECT * FROM "' . $references['thread_table'] .
            '" ORDER BY "sticky" DESC, "last_update" DESC, "thread_id" DESC', PDO::FETCH_ASSOC);
    $thread_list_table = $dom->getElementById('thread-list');
    $thread_row = $dom->getElementById('thread-row-');
    $i = 0;

    foreach ($thread_data as $thread)
    {
        $temp_thread_row = $thread_row->cloneNode(true);
        $temp_thread_row->changeId('thread_row-' . $thread['thread_id']);

        $prepared = $dbh->prepare('SELECT * FROM "' . $references['post_table'] . '" WHERE "post_number" = ? LIMIT 1');
        $prepared->bindValue(1, $thread['first_post'], PDO::PARAM_INT);
        $prepared->execute();
        $op_post = $prepared->fetch(PDO::FETCH_ASSOC);
        unset($result);

        $base_content_id = 'nci_' . $thread['thread_id'] . '_0_0';

        $expand_thread_button = $temp_thread_row->getElementById('expand-thread-button-');
        $expand_thread_button->extSetAttribute('value', _gettext('Expand') . ' ' . $thread['thread_id']);
        $expand_thread_button->changeId('expand-thread-button-' . $thread['thread_id']);
        $thread_post_number = $temp_thread_row->getElementById('thread-post-number-');
        $thread_post_number->setContent($thread['thread_id']);
        $thread_post_number->changeId('thread-post-number-' . $thread['thread_id']);

        $sticky_thread_link = $temp_thread_row->getElementById('sticky-thread-link-');

        if ($thread['sticky'] == 1)
        {
            $sticky_thread_link->extSetAttribute('href',
                    '?module=threads&board_id=' . $board_id . '&action=unsticky&content-id=' .
                    $base_content_id);
            $sticky_thread_link->setContent(_gettext('Unsticky Thread'));
        }
        else
        {
            $sticky_thread_link->extSetAttribute('href',
                    '?module=threads&board_id=' . $board_id . '&action=sticky&content-id=' .
                    $base_content_id);
        }

        $lock_thread_link = $temp_thread_row->getElementById('lock-thread-link-');

        if ($thread['locked'] == 1)
        {
            $lock_thread_link->extSetAttribute('href',
                    '?module=threads&board_id=' . $board_id . '&action=unlock&content-id=' .
                    $base_content_id);
            $lock_thread_link->setContent(_gettext('Unlock Thread'));
        }
        else
        {
            $lock_thread_link->extSetAttribute('href',
                    '?module=threads&board_id=' . $board_id . '&action=lock&content-id=' .
                    $base_content_id);
        }

        $lock_thread_link = $temp_thread_row->getElementById('delete-thread-link-');
        $lock_thread_link->extSetAttribute('href',
                '?module=threads&board_id=' . $board_id . '&action=delete-thread&content-id=' .
                $base_content_id);
        $lock_thread_link->setContent(_gettext('Delete Thread'));

        $thread_last_update = $temp_thread_row->getElementById('thread-last-update-');
        $thread_last_update->setContent(date("D F jS Y  H:i:s", $thread['last_update'] / 1000));
        $thread_last_update->changeId('thread-last-update-' . $thread['thread_id']);

        $thread_subject_link = $temp_thread_row->getElementById('thread-subject-link-');
        $thread_subject_link->setContent($op_post['subject']);
        $thread_subject_link->extSetAttribute('href',
                $references['page_dir'] . '/' . $thread['thread_id'] . '/' . $thread['thread_id'] . '.html', 'none');
        $thread_subject_link->changeId('thread-subject-link-' . $thread['thread_id']);

        $thread_op_name = $temp_thread_row->getElementById('thread-op-name-');
        $thread_op_name->setContent($thread['thread_id']);
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

    $language->i18nDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}

function nel_render_thread_panel_expand($user, $board_id, $thread_id)
{
    if (!$user->boardPerm($this->board_id, 'perm_threads_access'))
    {
        nel_derp(350, _gettext('You are not allowed to access the threads panel.'));
    }

    $dbh = nel_database();
    $authorization = new \Nelliel\Auth\Authorization($dbh);
    $language = new \Nelliel\language\Language($authorization);
    $references = nel_parameters_and_data()->boardReferences($board_id);
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, $board_id,
            array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Threads')));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/thread_panel_expand.html');
    $dom->getElementById('thread-list-form')->extSetAttribute('action',
            PHP_SELF . '?module=threads&action=update&board_id=' . $board_id);
    $prepared = $dbh->prepare('SELECT * FROM "' . $references['post_table'] . '" WHERE "parent_thread" = ?');
    $post_data = $dbh->executePreparedFetchAll($prepared, array($thread_id), PDO::FETCH_ASSOC);
    $post_list_table = $dom->getElementById('post-list');
    $post_row = $dom->getElementById('post-row-');
    $i = 0;

    foreach ($post_data as $post)
    {
        $temp_post_row = $post_row->cloneNode(true);
        $temp_post_row->changeId('post-row-' . $post['post_number']);

        $base_content_id = 'nci_' . $post['parent_thread'] . '_' . $post['post_number'] . '_0';

        $post_post_number = $temp_post_row->getElementById('post-post-number-');
        $post_post_number->setContent($post['post_number']);
        $post_post_number->changeId('post-post-number-' . $post['post_number']);
        $delete_post = $temp_post_row->getElementById('delete-post-');
        $delete_post->extSetAttribute('name', $base_content_id);
        $delete_post->changeId('delete-post-' . $post['post_number']);
        $post_parent_thread = $temp_post_row->getElementById('post-thread-');
        $post_parent_thread->setContent($post['parent_thread']);
        $post_parent_thread->changeId('post-thread-' . $post['parent_thread']);
        $post_last_update = $temp_post_row->getElementById('post-time-');
        $post_last_update->setContent(date("D F jS Y  H:i:s", $post['post_time'] / 1000));
        $post_last_update->changeId('post-time-' . $post['post_number']); /////
        $post_subject_link = $temp_post_row->getElementById('post-subject-link-');
        $post_subject_link->setContent($post['subject']);
        $post_subject_link->extSetAttribute('href',
                $references['page_dir'] . '/' . $post['parent_thread'] . '/' . $post['post_number'] . '.html', 'none');
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

    $language->i18nDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}