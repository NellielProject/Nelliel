<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_thread_generator($dataforce, $dbh)
{
    $render = new nel_render();
    $render_expand = new nel_render();
    $render_collapse = new nel_render();
    $gen_data['insert_hr'] = FALSE;
    $dataforce['dotdot'] = '';
    $write_id = ($dataforce['response_to'] === 0 || is_null($dataforce['response_to'])) ? $dataforce['response_id'] : $dataforce['response_to'];

    $prepared = $dbh->prepare('SELECT * FROM ' . THREAD_TABLE . ' WHERE thread_id=?');
    $prepared->bindValue(1, $write_id, PDO::PARAM_INT);
    $prepared->execute();
    $gen_data['thread'] = $prepared->fetch(PDO::FETCH_ASSOC);
    $prepared->closeCursor();

    $prepared = $dbh->prepare('SELECT * FROM ' . POST_TABLE . ' WHERE parent_thread=? ORDER BY post_number asc');
    $prepared->bindValue(1, $write_id, PDO::PARAM_INT);
    $prepared->execute();
    $treeline = $prepared->fetchAll(PDO::FETCH_ASSOC);
    $prepared->closeCursor();

    if (empty($treeline))
    {
        return;
    }

    if (empty($_SESSION) || $_SESSION['ignore_login'])
    {
        $dataforce['dotdot'] = '../../';
    }

    $page = 1;
    $gen_data['post_counter'] = 0;
    $gen_data['expand_post'] = FALSE;
    $dataforce['omitted_done'] = TRUE;
    $partlimit = 1;
    $gen_data['first100'] = FALSE;

    while ($gen_data['post_counter'] < $gen_data['thread']['post_count'])
    {
        $gen_data['post'] = $treeline[$gen_data['post_counter']];

        if ($gen_data['post_counter'] === 0)
        {
            $render->add_data('header_type', 'NORMAL');
            nel_render_header($dataforce, $render, $treeline);
            nel_render_posting_form($dataforce, $render);
        }

        if ($gen_data['post']['has_file'] == 1)
        {
            $prepared = $dbh->prepare('SELECT * FROM ' . FILE_TABLE . ' WHERE post_ref=? ORDER BY file_order asc');
            $prepared->bindValue(1, $gen_data['post']['post_number'], PDO::PARAM_INT);
            $prepared->execute();
            $gen_data['files'] = $prepared->fetchAll(PDO::FETCH_ASSOC);
            $prepared->closeCursor();
        }

        if ($partlimit === 100)
        {
            $render_temp = clone $render;
            $gen_data['insert_hr'] = TRUE;
            nel_render_post($dataforce, $render_temp, FALSE, FALSE, $gen_data, $treeline, $dbh);
            $gen_data['insert_hr'] = FALSE;
            nel_render_footer($render_temp, FALSE, TRUE, TRUE, TRUE, FALSE);
            nel_write_file(PAGE_PATH . $write_id . '/' . $dataforce['response_id'] . '-0-100.html', $render_temp->output(), 0644);
            unset($render_temp);
        }

        $render_temp = new nel_render();
        $render_temp2 = new nel_render();
        $render_temp3 = new nel_render();

        if ($gen_data['post']['op'] == 1)
        {
            nel_render_post($dataforce, $render_temp, FALSE, FALSE, $gen_data, $treeline, $dbh); // for thread
        }
        else
        {
            nel_render_post($dataforce, $render, TRUE, FALSE, $gen_data, $treeline, $dbh);

            if ($gen_data['thread']['post_count'] > BS_ABBREVIATE_THREAD)
            {
                if ($gen_data['post_counter'] > $gen_data['thread']['post_count'] - BS_ABBREVIATE_THREAD)
                {
                    nel_render_post($dataforce, $render_temp2, TRUE, TRUE, $gen_data, $treeline, $dbh); // for collapse
                    $render_collapse->input($render_temp2->output(FALSE));
                }
            }

            $resid = $dataforce['response_id'];
            $dataforce['response_id'] = 0;
            nel_render_post($dataforce, $render_temp3, TRUE, TRUE, $gen_data, $treeline, $dbh); // for expand
            $render_expand->input($render_temp3->output(FALSE));
            $dataforce['response_id'] = $resid;
        }

        $render->input($render_temp->output(FALSE));
        ++ $partlimit;
        ++ $gen_data['post_counter'];
        unset($render_temp);
        unset($render_temp2);
        unset($render_temp3);
    }

    nel_render_footer($render, FALSE, TRUE, TRUE, TRUE, FALSE);

    if (!nel_session_ignored())
    {
        if ($dataforce['expand'])
        {
            $render_expand->output(TRUE);
        }
        else if ($dataforce['collapse'])
        {
            $render_collapse->output(TRUE);
        }
        else
        {
            $render->output(TRUE);
        }

        die();
    }
    else
    {
        nel_write_file(PAGE_PATH . $write_id . '/' . $dataforce['response_id'] . '.html', $render->output(FALSE), 0644);
        nel_write_file(PAGE_PATH . $write_id . '/' . $dataforce['response_id'] . '-expand.html', $render_expand->output(FALSE), 0644);
        nel_write_file(PAGE_PATH . $write_id . '/' . $dataforce['response_id'] . '-collapse.html', $render_collapse->output(FALSE), 0644);
    }
}

?>