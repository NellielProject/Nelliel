<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_thread_panel($dataforce, $expand, $dbh)
{
    $render = new nel_render();
    nel_render_header($dataforce, $render, array());
    nel_render_thread_panel_form($dataforce, $render);

    if ($expand)
    {
        $render->add_data('expand_thread', TRUE);
        $thread_id = utf8_str_replace('Expand ', '', $_POST['expand_thread']);

        $prepared = $dbh->prepare('SELECT * FROM ' . POST_TABLE . ' WHERE parent_thread=? ORDER BY post_number asc');
        $prepared->bindValue(1, $thread_id, PDO::PARAM_INT);
        $prepared->execute();
        $thread_data = $prepared->fetchAll(PDO::FETCH_ASSOC);
        $prepared->closeCursor();
    }
    else
    {
        $render->add_data('expand_thread', FALSE);
        $result = $dbh->query('SELECT * FROM ' . THREAD_TABLE . ' ORDER BY thread_id DESC');
        $thread_data = $result->fetchAll(PDO::FETCH_ASSOC);
        unset($result);
    }

    $j = 0;
    $all = 0;

    foreach ($thread_data as $thread)
    {
        if (!$expand)
        {
            $thread_id = $thread['thread_id'];
            $thread['post_number'] = $thread['thread_id'];
        }

        $prepared = $dbh->prepare('SELECT * FROM ' . POST_TABLE . ' WHERE post_number=?');
        $prepared->bindValue(1, $thread_id, PDO::PARAM_INT);
        $prepared->execute();
        $post = $prepared->fetch(PDO::FETCH_ASSOC);
        $prepared->closeCursor();

        if ($post['has_file'] === '1')
        {
            $prepared = $dbh->prepare('SELECT * FROM ' . FILE_TABLE . ' WHERE post_ref=? ORDER BY file_order asc');
            $prepared->bindValue(1, $thread['post_number'], PDO::PARAM_INT);
            $prepared->execute();
            $thread['files'] = $prepared->fetchAll(PDO::FETCH_ASSOC);
            $prepared->closeCursor();

            foreach ($thread['files'] as $file)
            {
                $all += $file['filesize'];
            }
        }

        $dataforce['j_increment'] = $j;
        nel_render_thread_panel_thread($dataforce, $render, $thread, $post);
        $j ++;
    }

    $dataforce['all_filesize'] = (int) ($all / 1024);
    nel_render_thread_panel_bottom($dataforce, $render, $thread_data);
    nel_render_basic_footer($render);
    $render->output(TRUE);
}

function nel_render_thread_panel_thread($dataforce, $render, $thread_data, $post_data)
{
    $render->add_multiple_data($thread_data);
    $render->add_multiple_data($post_data);

    switch (BS_DATE_FORMAT)
    {
        case 'ISO':
            $render->add_data('post_time', date("Y/m/d H:i:s", floor($post_data['post_time'] / 1000)));
            break;

        case 'US':
            $render->add_data('post_time', date("m/d/Y H:i:s", floor($post_data['post_time'] / 1000)));
            break;

        case 'COM':
            $render->add_data('post_time', date("d/m/Y H:i:s", floor($post_data['post_time'] / 1000)));
            break;
    }

    if (utf8_strlen($post_data['name']) > 12)
    {
        $render->add_data('post_name', utf8_substr($post_data['name'], 0, 11) . "...");
    }

    if (utf8_strlen($post_data['subject']) > 12)
    {
        $render->add_data('subject', utf8_substr($post_data['subject'], 0, 11) . "...");
    }

    if ($post_data['email'])
    {
        $render->add_data('post_name', '"<a href="mailto:' . $post_data['email'] . '">' . $post_data['name'] . '</a>');
    }

    $post_data['comment'] = utf8_str_replace("<br>", " ", $post_data['comment']);
    $render->add_data('comment', htmlspecialchars($post_data['comment']));

    if (utf8_strlen($post_data['comment']) > 20)
    {
        $render->add_data('comment', utf8_substr($render->retrieve_data('comment'), 0, 19) . "...");
    }

    $render->add_data('host', (@inet_ntop($post_data['host'])) ? inet_ntop($post_data['host']) : 'Unknown');

    if ($post_data['post_number'] == $post_data['parent_thread'])
    {
        $render->add_data('is_op', TRUE);
    }
    else
    {
        $render->add_data('is_op', FALSE);
    }

    if (!empty($post_data['files']))
    {
        $files = $post_data['files'];
        $i = 0;

        foreach ($files as $file)
        {
            $files[$i]['filesize'] = (int) ceil($file['filesize'] / 1024);
            ++ $i;
        }

        $render->add_data('files', $files);
    }

    $render->add_data('bg_class', ($dataforce['j_increment'] % 2) ? 'row1' : 'row2');
    $render->parse('thread_panel_thread.tpl', 'management');
}

function nel_render_thread_panel_form($dataforce, $render)
{
    $render->parse('thread_panel_form.tpl', 'management');
}

function nel_render_thread_panel_bottom($dataforce, $render, $thread_data)
{
    $render->add_multiple_data($thread_data);
    $render->add_data('all_filesize', $dataforce['all_filesize']);
    $render->parse('thread_panel_bottom.tpl', 'management');
}
?>