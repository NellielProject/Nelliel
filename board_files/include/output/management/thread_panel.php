<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_thread_panel_main($dataforce)
{
    $dbh = nel_database();
    $render = new nel_render();
    nel_render_header($dataforce, $render, array());
    $render1 = new NellielTemplates\RenderCore();
    $dom = $render1->newDOMDocument();
    $render1->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $dom->loadTemplateFromFile('management/thread_panel.html');
    $xpath = new DOMXPath($dom);

    $result =  $dbh->query('SELECT * FROM "' . THREAD_TABLE . '" ORDER BY "sticky" DESC, "last_update" DESC, "thread_id" DESC');
    $thread_data = $result->fetchAll(PDO::FETCH_ASSOC);
    unset($result);
    $thread_list_table = $dom->getElementById('thread-list');
    $thread_row = $dom->getElementById('thread-row-');
    $i = 0;

    foreach($thread_data as $thread)
    {
        $temp_thread_row = $thread_row->cloneNode(true);
        $temp_thread_row->changeId('thread_row-' . $thread['thread_id']);

        $prepared = $dbh->prepare('SELECT * FROM "' . POST_TABLE . '" WHERE "post_number" = ? LIMIT 1');
        $prepared->bindValue(1, $thread['thread_id'], PDO::PARAM_INT);
        $prepared->execute();
        $op_post = $prepared->fetch(PDO::FETCH_ASSOC);
        unset($result);

        $xpath->query(".//*[@id='expand-thread-button-']", $temp_thread_row)->item(0);
        $expand_thread_button = $xpath->query(".//*[@id='expand-thread-button-']", $temp_thread_row)->item(0);
        $expand_thread_button->extSetAttribute('value', nel_stext('FORM_EXPAND') . ' ' . $thread['thread_id']);
        $expand_thread_button->changeId('expand-thread-button-' . $thread['thread_id']);
        $thread_post_number = $xpath->query(".//*[@id='thread-post-number-']", $temp_thread_row)->item(0);
        $thread_post_number->setContent($thread['thread_id']);
        $thread_post_number->changeId('thread-post-number-' . $thread['thread_id']);
        $delete_thread = $xpath->query(".//*[@id='delete-thread-']", $temp_thread_row)->item(0);
        $delete_thread->modifyAttribute('name', $thread['thread_id'], 'after');
        $delete_thread->modifyAttribute('value', $thread['thread_id'], 'after');
        $delete_thread->changeId('delete-thread-' . $thread['thread_id']);

        if($thread['sticky'] == 1)
        {
            $unsticky_thread = $xpath->query(".//*[@id='unsticky-thread-']", $temp_thread_row)->item(0);
            $unsticky_thread->modifyAttribute('name', $thread['thread_id'], 'after');
            $unsticky_thread->modifyAttribute('value', $thread['thread_id'], 'after');
            $unsticky_thread->changeId('unsticky-thread-' . $thread['thread_id']);
            $xpath->query(".//*[@id='sticky-thread-']", $temp_thread_row)->item(0)->removeSelf();
        }
        else
        {
            $sticky_thread = $xpath->query(".//*[@id='sticky-thread-']", $temp_thread_row)->item(0);
            $sticky_thread->modifyAttribute('name', $thread['thread_id'], 'after');
            $sticky_thread->modifyAttribute('value', $thread['thread_id'], 'after');
            $sticky_thread->changeId('sticky-thread-' . $thread['thread_id']);
            $xpath->query(".//*[@id='unsticky-thread-']", $temp_thread_row)->item(0)->removeSelf();
        }

        $thread_locked = $xpath->query(".//*[@id='thread-locked-']", $temp_thread_row)->item(0);
        $thread_locked->setContent($thread['locked'] == 1 ? 'Locked' : 'Unlocked');
        $thread_locked->changeId('thread-locked-' . $thread['thread_id']);

        $thread_last_update = $xpath->query(".//*[@id='thread-last-update-']", $temp_thread_row)->item(0);
        $thread_last_update->setContent(date("D F jS Y  H:i:s", $thread['last_update'] / 1000));
        $thread_last_update->changeId('thread-last-update-' . $thread['thread_id']);

        $thread_subject_link = $xpath->query(".//*[@id='thread-subject-link-']", $temp_thread_row)->item(0);
        $thread_subject_link->setContent($op_post['subject']);
        $thread_subject_link->extSetAttribute('href', PAGE_DIR . $thread['thread_id']. '/' . $thread['thread_id']. '.html', 'none');
        $thread_subject_link->changeId('thread-subject-link-' . $thread['thread_id']);

        $thread_op_name = $xpath->query(".//*[@id='thread-op-name-']", $temp_thread_row)->item(0);
        $thread_op_name->setContent($op_post['poster_name']);
        $thread_op_name->changeId('thread-op-name-' . $thread['thread_id']);
        $thread_op_ip = $xpath->query(".//*[@id='thread-op-ip-']", $temp_thread_row)->item(0);
        $thread_op_ip->setContent($op_post['ip_address']);
        $thread_op_ip->changeId('thread-op-ip-' . $thread['thread_id']);

        if($i & 1)
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

    $thread_row->removeSelf();

    nel_process_i18n($dom);
    $render->appendOutput($dom->outputHTML());
    nel_render_footer($render, false);
    $render->output(true);
}