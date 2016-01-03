<?php
function nel_render_thread_panel($dataforce, $expand, $dbh)
{
    nel_render_init(TRUE);
    $dat = '';
    $dat = nel_render_header($dataforce, 'ADMIN', array());
    $dat .= nel_render_thread_panel_form($dataforce);

    if ($expand)
    {
        nel_render_in('expand_thread', TRUE);
        $thread_id = utf8_str_replace('Expand ', '', $_POST['expand_thread']);
        $prepared = $dbh->prepare('SELECT * FROM ' . POSTTABLE . ' WHERE response_to=:threadid OR post_number=:threadid2 ORDER BY post_number ASC');
        $prepared->bindParam(':threadid', $thread_id, PDO::PARAM_INT);
        $prepared->bindParam(':threadid2', $thread_id, PDO::PARAM_INT); // This really shouldn't be necessary :|
        $prepared->execute();
    }
    else
    {
        nel_render_in('expand_thread', FALSE);
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
        $dat .= nel_render_thread_panel_thread($dataforce, $thread);
        $j ++;
    }

    $dataforce['all_filesize'] = (int) ($all / 1024);
    $dat .= nel_render_thread_panel_bottom($dataforce, $thread_data);
    $dat .= nel_render_basic_footer();
    return $dat;
}

function nel_render_thread_panel_thread($dataforce, $thread_data)
{
    nel_render_in('has_file', $thread_data['has_file']);

    switch (BS_DATE_FORMAT)
    {
    	case 'ISO':
    	    nel_render_in('post_time', date("Y/m/d H:i:s", floor($thread_data['post_time'] / 1000)));
    	    break;

    	case 'US':
    	    nel_render_in('post_time', date("m/d/Y H:i:s", floor($thread_data['post_time'] / 1000)));
    	    break;

    	case 'COM':
    	    nel_render_in('post_time', date("d/m/Y H:i:s", floor($thread_data['post_time'] / 1000)));
    	    break;
    }

    if (utf8_strlen($thread_data['name']) > 12)
    {
        nel_render_in('post_name', utf8_substr($thread_data['name'], 0, 11) . "...");
    }

    if (utf8_strlen($thread_data['subject']) > 12)
    {
        nel_render_in('subject', utf8_substr($thread_data['subject'], 0, 11) . "...");
    }

    if ($thread_data['email'])
    {
        nel_render_in('post_name', '"<a href="mailto:' . $thread_data['email'] . '">' . $thread_data['name'] . '</a>');
    }

    $thread_data['comment'] = utf8_str_replace("<br>", " ", $thread_data['comment']);
    nel_render_in('comment', htmlspecialchars($thread_data['comment']));

    if (utf8_strlen($thread_data['comment']) > 20)
    {
        nel_render_in('comment', utf8_substr(nel_render_out('comment'), 0, 19) . "...");
    }

    nel_render_in('host', (@inet_ntop(nel_render_out('host'))) ? inet_ntop(nel_render_out('host')) : 'Unknown');

    if (nel_render_out('response_to') == '0')
    {
        nel_render_in('is_op', TRUE);
        $num = nel_render_out('post_number');
    }
    else
    {
        nel_render_in('is_op', FALSE);
        $num = nel_render_out('response_to');
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

        nel_render_in('files', $files);
    }

    nel_render_in('bg_class', ($dataforce['j_increment'] % 2) ? 'row1' : 'row2');
    $dat = nel_parse_template('thread_panel_thread.tpl', 'management', FALSE);
    return $dat;
}

function nel_render_thread_panel_form($dataforce)
{
    $dat = nel_parse_template('thread_panel_form.tpl', 'management', FALSE);
    return $dat;
}

function nel_render_thread_panel_bottom($dataforce, $thread_data)
{
    nel_render_multiple_in($thread_data);
    nel_render_in('all_filesize', $dataforce['all_filesize']);
    $dat = nel_parse_template('thread_panel_bottom.tpl', 'management', FALSE);
    return $dat;
}
?>