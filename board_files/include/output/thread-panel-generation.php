<?php
function nel_render_thread_panel($dataforce, $expand, $dbh)
{
    $render = new nel_render();
    nel_render_header($dataforce, $render, array());
    nel_render_thread_panel_form($dataforce, $render);

    if ($expand)
    {
        $render->add_data('expand_thread', TRUE);
        $thread_id = utf8_str_replace('Expand ', '', $_POST['expand_thread']);
        $prepared = $dbh->prepare('SELECT * FROM ' . POSTTABLE . ' WHERE response_to=:threadid OR post_number=:threadid2 ORDER BY post_number ASC');
        $prepared->bindParam(':threadid', $thread_id, PDO::PARAM_INT);
        $prepared->bindParam(':threadid2', $thread_id, PDO::PARAM_INT); // This really shouldn't be necessary :|
        $prepared->execute();
    }
    else
    {
        $render->add_data('expand_thread', FALSE);
        $prepared = $dbh->query('SELECT * FROM ' . POSTTABLE . ' WHERE response_to=0 ORDER BY post_number DESC');
    }

    $j = 0;
    $all = 0;
    $thread_data = $prepared->fetchALL(PDO::FETCH_ASSOC);
    unset($prepared);

    foreach ($thread_data as $thread)
    {
        if ($thread['has_file'] === '1')
        {
            $result = $dbh->query('SELECT * FROM ' . FILETABLE . ' WHERE post_ref=' . $thread['post_number'] . ' ORDER BY file_order asc');
            $thread['files'] = $result->fetchALL(PDO::FETCH_ASSOC);
            unset($result);

            foreach ($thread['files'] as $file)
            {
                $all += $file['filesize'];
            }
        }

        $dataforce['j_increment'] = $j;
        nel_render_thread_panel_thread($dataforce, $render, $thread);
        $j ++;
    }

    $dataforce['all_filesize'] = (int) ($all / 1024);
    nel_render_thread_panel_bottom($dataforce, $render, $thread_data);
    nel_render_basic_footer($render);
    echo $render->output();
}

function nel_render_thread_panel_thread($dataforce, $render, $thread_data)
{
    $render->add_data('has_file', $thread_data['has_file']);

    switch (BS_DATE_FORMAT)
    {
    	case 'ISO':
    	    $render->add_data('post_time', date("Y/m/d H:i:s", floor($thread_data['post_time'] / 1000)));
    	    break;

    	case 'US':
    	    $render->add_data('post_time', date("m/d/Y H:i:s", floor($thread_data['post_time'] / 1000)));
    	    break;

    	case 'COM':
    	    $render->add_data('post_time', date("d/m/Y H:i:s", floor($thread_data['post_time'] / 1000)));
    	    break;
    }

    if (utf8_strlen($thread_data['name']) > 12)
    {
        $render->add_data('post_name', utf8_substr($thread_data['name'], 0, 11) . "...");
    }

    if (utf8_strlen($thread_data['subject']) > 12)
    {
        $render->add_data('subject', utf8_substr($thread_data['subject'], 0, 11) . "...");
    }

    if ($thread_data['email'])
    {
        $render->add_data('post_name', '"<a href="mailto:' . $thread_data['email'] . '">' . $thread_data['name'] . '</a>');
    }

    $thread_data['comment'] = utf8_str_replace("<br>", " ", $thread_data['comment']);
    $render->add_data('comment', htmlspecialchars($thread_data['comment']));

    if (utf8_strlen($thread_data['comment']) > 20)
    {
        $render->add_data('comment', utf8_substr($render->retrieve_data('comment'), 0, 19) . "...");
    }

    $render->add_data('host', (@inet_ntop($thread_data['host'])) ? inet_ntop($thread_data['host']) : 'Unknown');

    if ($thread_data['response_to'] == '0')
    {
        $render->add_data('is_op', TRUE);
    }
    else
    {
        $render->add_data('is_op', FALSE);
    }

    if (!empty($thread_data['files']))
    {
        $files = $thread_data['files'];
        $filecount = count($files);
        $i = 0;

        while ($i < $filecount)
        {
            $files[$i]['filesize'] = (int) ceil($files[$i]['filesize'] / 1024);
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