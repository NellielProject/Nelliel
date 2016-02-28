<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Genrerates the main thread listings
//
function nel_main_nel_thread_generator($dataforce)
{
    $dbh = nel_get_db_handle();
    $gen_data = array();
    $gen_data['insert_hr'] = FALSE;
    $dataforce['dotdot'] = '';

    $result = $dbh->query('SELECT thread_id FROM ' . THREAD_TABLE . ' WHERE archive_status=0 ORDER BY sticky desc, last_update desc');
    $front_page_list = $result->fetchAll(PDO::FETCH_COLUMN);
    unset($result);

    $treeline = array(0);
    $counttree = count($front_page_list);

    // Special handling when there's no content
    if ($counttree === 0)
    {
        $render = new nel_render();
        $render->add_data('header_type', 'NORMAL');
        nel_render_header($dataforce, $render, $treeline);
        nel_render_posting_form($dataforce, $render);
        $render->add_data('prev_nav', '');
        $render->add_data('next_nav', '');
        $render->add_data('page_nav', '');
        nel_render_footer($render, FALSE, TRUE, TRUE, FALSE, TRUE);

        if (nel_session_ignored())
        {
            nel_write_file(PHP_SELF2 . PHP_EXT, $render->output(FALSE), 0644);
        }
        else
        {
            $render->output(TRUE);
        }

        return;
    }

    $thread_counter = 0;
    $page = 1;
    $gen_data['post_counter'] = -1;

    while ($thread_counter < $counttree)
    {
        $render = new nel_render();
        $dataforce['omitted_done'] = TRUE;
        $render->add_data('header_type', 'NORMAL');
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
                $gen_data['insert_hr'] = TRUE;
                nel_render_post($dataforce, $render, FALSE, FALSE, $gen_data, $treeline);
                $gen_data['insert_hr'] = FALSE;
                $gen_data['post_counter'] = -1;
            }
            else
            {
                ++ $gen_data['post_counter'];
            }
        }

        // if not in res display mode
        $prev = $page - 1;
        $next = $page + 1;

        $render->add_data('page_nav', ' ');
        $page_count = (int) ceil($counttree / BS_THREADS_PER_PAGE);
        $render->add_data('main_page', TRUE);

        if ($page === 1)
        {
            $render->add_data('prev_nav', 'Previous');
        }
        else if ($page === 2)
        {
            $render->add_data('prev_nav', '<a href="' . PHP_SELF2 . PHP_EXT . '">Previous</a> ');
        }
        else
        {
            $render->add_data('prev_nav', '<a href="' . PHP_SELF2 . ($page - 2) . PHP_EXT . '">Previous</a>');
        }

        $render->add_data('next_nav', ($page === $page_count || $dataforce['max_pages'] === 1) ? 'Next' : '<a href="' . PHP_SELF2 . ($page) . PHP_EXT . '">Next</a>');
        $i = 0;

        while ($i < $page_count)
        {
            if ($i === 0)
            {
                $render->add_data('page_nav', $render->retrieve_data('page_nav') . (($page > 1) ? '[<a href="' . PHP_SELF2 . PHP_EXT . '">0</a>] ' : '[0] '));
            }
            else if ($i === ($page - 1) || $dataforce['max_pages'] === 1)
            {
                $render->add_data('page_nav', $render->retrieve_data('page_nav') . '[' . ($i) . '] ');
            }
            else
            {
                $render->add_data('page_nav', $render->retrieve_data('page_nav') . '[<a href="' . PHP_SELF2 . ($i) . PHP_EXT . '">' . ($i) . '</a>] ');
            }

            ++ $i;
        }

        nel_render_footer($render, FALSE, TRUE, TRUE, FALSE, TRUE);

        if (!nel_session_ignored())
        {
            if ($page >= $dataforce['current_page'])
            {
                $page = $counttree;
            }

            $render->output(TRUE);
            die();
        }
        else
        {
            $logfilename = ($page === 1) ? PHP_SELF2 . PHP_EXT : PHP_SELF2 . ($page - 1) . PHP_EXT;
            nel_write_file($logfilename, $render->output(FALSE), 0644);
        }

        ++ $page;
        unset($render);
    }
}

?>