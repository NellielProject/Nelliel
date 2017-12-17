<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Genrerates the main thread listings
//
function nel_main_thread_generator($dataforce, $write)
{
    $dbh = nel_database();
    $gen_data = array();
    $dataforce['dotdot'] = '';

    if($write)
    {
        nel_session_set_ignored('render', true);
    }

    $result =  $dbh->query('SELECT thread_id FROM ' . THREAD_TABLE . ' WHERE archive_status=0 ORDER BY sticky desc, last_bump_time desc');
    $front_page_list = $result->fetchAll(PDO::FETCH_COLUMN);
    unset($result);

    $treeline = array(0);
    $counttree = count($front_page_list);
    $dataforce['posts_beginning'] = false;
    $dataforce['posts_ending'] = false;
    $dataforce['index_rendering'] = true;

    // Special handling when there's no content
    if ($counttree === 0)
    {
        $render = new NellielTemplates\RenderCore();
        $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
        nel_render_header($dataforce, $render, $treeline);
        nel_render_posting_form($dataforce, $render);
        nel_render_footer($render, true);

        if ($write)
        {
            nel_write_file(PHP_SELF2 . PHP_EXT, $render->outputRenderSet(), FILE_PERM);
        }
        else
        {
            echo $render->outputRenderSet();
        }

        return;
    }

    $thread_counter = 0;
    $page = 1;
    $gen_data['post_counter'] = -1;

    while ($thread_counter < $counttree)
    {
        $render = new NellielTemplates\RenderCore();
        $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
        $dataforce['omitted_done'] = TRUE;
        nel_render_header($dataforce, $render, $treeline);
        nel_render_posting_form($dataforce, $render);
        $sub_page_thread_counter = 0;
        $gen_data['first100'] = FALSE;

        while ($sub_page_thread_counter < BS_THREADS_PER_PAGE)
        {
            if ($gen_data['post_counter'] === -1)
            {
                $prepared = $dbh->prepare('SELECT * FROM ' . THREAD_TABLE . ' WHERE thread_id=?');
                $prepared->bindValue(1, $front_page_list[$thread_counter], PDO::PARAM_INT);
                $prepared->execute();
                $gen_data['thread'] = $prepared->fetch(PDO::FETCH_ASSOC);
                $prepared->closeCursor();

                $prepared = $dbh->prepare('SELECT * FROM ' . POST_TABLE . ' WHERE parent_thread=? ORDER BY post_number asc');
                $prepared->bindValue(1, $front_page_list[$thread_counter], PDO::PARAM_INT);
                $prepared->execute();
                $treeline = $prepared->fetchAll(PDO::FETCH_ASSOC);
                $prepared->closeCursor();

                $gen_data['thread']['expand_post'] = ($gen_data['thread']['post_count'] > BS_ABBREVIATE_THREAD) ? TRUE : FALSE;
                $gen_data['thread']['first100'] = ($gen_data['thread']['post_count'] > 100) ? TRUE : FALSE;
                $gen_data['post_counter'] = 0;
            }

            if($thread_counter === 0 && $gen_data['post_counter'] === 0)
            {
                $dataforce['posts_beginning'] = true;
            }
            else
            {
                $dataforce['posts_beginning'] = false;
            }

            $gen_data['post'] = $treeline[$gen_data['post_counter']];

            if ($gen_data['post']['has_file'] == 1)
            {
                $prepared = $dbh->prepare('SELECT * FROM ' . FILE_TABLE . ' WHERE post_ref=? ORDER BY file_order asc');
                $prepared->bindValue(1, $gen_data['post']['post_number'], PDO::PARAM_INT);
                $prepared->execute();
                $gen_data['files'] = $prepared->fetchAll(PDO::FETCH_ASSOC);
                $prepared->closeCursor();
            }

            if ($gen_data['post']['op'] == 1)
            {
                if ($gen_data['thread']['post_count'] > BS_ABBREVIATE_THREAD)
                {
                    $gen_data['post_counter'] = $gen_data['thread']['post_count'] - BS_ABBREVIATE_THREAD;
                    $dataforce['omitted_done'] = FALSE;
                    nel_render_post($dataforce, $render, FALSE, FALSE, $gen_data, $treeline);
                    $dataforce['omitted_done'] = TRUE;
                }
                else
                {
                    nel_render_post($dataforce, $render, FALSE, FALSE, $gen_data, $treeline);
                }
            }
            else
            {
                nel_render_post($dataforce, $render, TRUE, TRUE, $gen_data, $treeline);
            }

            if (empty($treeline[$gen_data['post_counter'] + 1]))
            {
                $sub_page_thread_counter = ($thread_counter == $counttree - 1) ? BS_THREADS_PER_PAGE : ++ $sub_page_thread_counter;
                ++ $thread_counter;
                nel_render_insert_hr($render);
                $gen_data['post_counter'] = -1;
            }
            else
            {
                ++ $gen_data['post_counter'];
            }
        }

        $dataforce['posts_ending'] = true;

        // if not in res display mode
        $prev = $page - 1;
        $next = $page + 1;

        $page_count = (int) ceil($counttree / BS_THREADS_PER_PAGE);
        //$render->add_data('main_page', TRUE);
        $pages = array();

        if ($page === 1)
        {
            $pages['prev'] = '';
        }
        else if ($page === 2)
        {
            $pages['prev'] = PHP_SELF2 . PHP_EXT;
        }
        else
        {
            $pages['prev'] = PHP_SELF2 . ($page - 2) . PHP_EXT;
        }

        $i = 0;

        // TODO: Clean shit up
        while ($i < $page_count)
        {
            if ($i === 0)
            {
                $pages[$i] = (($page > 1) ? PHP_SELF2 . PHP_EXT : '');
            }
            else if ($i === ($page - 1) || $dataforce['max_pages'] === 1)
            {
                $pages[$i] = '';
            }
            else
            {
                $pages[$i] = PHP_SELF2 . ($i) . PHP_EXT;
            }

            ++ $i;
        }

        if($page === $page_count || $dataforce['max_pages'] === 1)
        {
            $pages['next'] = '';
        }
        else
        {
            $pages['next'] = PHP_SELF2 . ($page) . PHP_EXT;
        }

        nel_render_index_navigation($render, $pages);
        nel_render_footer($render, true);

        if (!$write)
        {
            if ($page >= $dataforce['current_page'])
            {
                $page = $counttree;
            }

            echo $render->outputRenderSet();
            die();
        }
        else
        {
            $logfilename = ($page === 1) ? PHP_SELF2 . PHP_EXT : PHP_SELF2 . ($page - 1) . PHP_EXT;
            nel_write_file($logfilename, $render->outputRenderSet(), FILE_PERM);
        }

        ++ $page;
        unset($render);
    }

    if($write)
    {
        nel_session_set_ignored('render', false);
    }
}
