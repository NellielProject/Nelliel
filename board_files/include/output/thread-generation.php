<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_thread_generator($dataforce, $write, $write_id)
{
    $dbh = nel_database();

    if($write)
    {
        nel_session_set_ignored('render', true);
    }

    $render = new nel_render();
    $render_expand = new nel_render();
    $render_collapse = new nel_render();
    $dataforce['dotdot'] = '../../';
    //$write_id = ($dataforce['response_to'] === 0 || is_null($dataforce['response_to'])) ? $dataforce['response_id'] : $dataforce['response_to'];
    $dataforce['response_id'] = $write_id;
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

   /* if (empty($_SESSION) || $_SESSION['ignore_login'])
    {
        $dataforce['dotdot'] = '../../';
    }*/

    $page = 1;
    $gen_data['post_counter'] = 0;
    $gen_data['expand_post'] = FALSE;
    $dataforce['omitted_done'] = TRUE;
    $partlimit = 1;
    $gen_data['first100'] = FALSE;
    $dataforce['posts_beginning'] = false;
    $dataforce['posts_ending'] = false;
    $dataforce['index_rendering'] = false;

    while ($gen_data['post_counter'] < $gen_data['thread']['post_count'])
    {
        $gen_data['post'] = $treeline[$gen_data['post_counter']];

        if ($gen_data['post_counter'] === 0)
        {
            $render->add_data('header_type', 'NORMAL');
            nel_render_header($dataforce, $render, $treeline);
            nel_render_posting_form($dataforce, $render);
            $dataforce['posts_beginning'] = true;
        }
        else
        {
            $dataforce['posts_beginning'] = false;
        }

        if($gen_data['post_counter'] == $gen_data['thread']['post_count'] - 1)
        {
            $dataforce['posts_ending'] = true;
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
            nel_render_insert_hr($render);
            nel_render_footer($render_temp, true);
            nel_write_file(PAGE_PATH . $write_id . '/' . $write_id. '-0-100.html', $render_temp->output(), FILE_PERM);
            unset($render_temp);
        }

        $render_temp = new nel_render();
        $render_temp2 = new nel_render();
        $render_temp3 = new nel_render();

        if ($gen_data['post']['op'] == 1)
        {
            nel_render_post($dataforce, $render_temp, FALSE, FALSE, $gen_data, $treeline); // for thread
        }
        else
        {
            nel_render_post($dataforce, $render, TRUE, FALSE, $gen_data, $treeline);

            if ($gen_data['thread']['post_count'] > BS_ABBREVIATE_THREAD)
            {
                if ($gen_data['post_counter'] > $gen_data['thread']['post_count'] - BS_ABBREVIATE_THREAD)
                {
                    nel_render_post($dataforce, $render_temp2, TRUE, TRUE, $gen_data, $treeline); // for collapse
                    $render_collapse->input($render_temp2->output(FALSE));
                }
            }

            $resid = $dataforce['response_id'];
            $dataforce['response_id'] = 0;
            nel_render_post($dataforce, $render_temp3, TRUE, TRUE, $gen_data, $treeline); // for expand
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

    nel_render_insert_hr($render);
    nel_render_footer($render, true);

    if ($write)
    {
        nel_write_file(PAGE_PATH . $write_id . '/' . $write_id . '.html', $render->output(FALSE), FILE_PERM);
        nel_write_file(PAGE_PATH . $write_id . '/' . $write_id . '-expand.html', $render_expand->output(FALSE), FILE_PERM);
        nel_write_file(PAGE_PATH . $write_id . '/' . $write_id . '-collapse.html', $render_collapse->output(FALSE), FILE_PERM);
    }
    else
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

    if($write)
    {
        nel_session_set_ignored('render', false);
    }
}
