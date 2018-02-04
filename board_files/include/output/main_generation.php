<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Genrerates the main thread listings
//
function nel_main_thread_generator($dataforce, $board_id, $write)
{
    $dbh = nel_database();
    $references = nel_board_references($board_id);
    $board_settings = nel_board_settings($board_id);
    $file_handler = nel_file_handler();
    $thread_table = $gen_data = array();
    $dataforce['dotdot'] = '../';

    if ($write)
    {
        nel_session_is_ignored('render', true);
    }

    $result = $dbh->query('SELECT "thread_id" FROM "' . $references['thread_table'] .
         '" WHERE "archive_status" = 0 ORDER BY "sticky" DESC, "last_bump_time" DESC');
    $front_page_list = $result->fetchAll(PDO::FETCH_COLUMN);
    unset($result);

    $treeline = array(0);
    $counttree = count($front_page_list);
    $dataforce['posts_ending'] = false;
    $dataforce['index_rendering'] = true;

    // Special handling when there's no content
    if ($counttree === 0)
    {
        $render = new NellielTemplates\RenderCore();
        $render->startRenderTimer();
        $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
        nel_render_board_header($board_id, $dataforce, $render, $treeline);
        nel_render_posting_form($board_id, $dataforce, $render);
        nel_render_footer($board_id, $render, true);

        if ($write)
        {
            $file_handler->writeFile($references['directory'] . '/' . PHP_SELF2 . PHP_EXT, $render->outputRenderSet(), FILE_PERM);
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
        $dom = $render->newDOMDocument();
        $render->loadTemplateFromFile($dom, 'thread.html');
        $render->startRenderTimer();
        nel_process_i18n($dom, nel_board_settings($board_id, 'board_language'));
        $dom->getElementById('form-post-index')->extSetAttribute('action', $dataforce['dotdot'] . PHP_SELF);
        $dataforce['omitted_done'] = TRUE;
        nel_render_board_header($board_id, $dataforce, $render, $treeline);
        nel_render_posting_form($board_id, $dataforce, $render);
        $sub_page_thread_counter = 0;
        $gen_data['first100'] = FALSE;

        while ($sub_page_thread_counter < $board_settings['threads_per_page'])
        {
            if ($gen_data['post_counter'] === -1)
            {
                $current_thread_id = $front_page_list[$thread_counter];
                $thread_element = $dom->getElementById('thread-')->cloneNode();
                $thread_element->changeId('thread-' . $current_thread_id);
                $dom->getElementById('outer-div')->appendChild($thread_element);
                $post_append_target = $thread_element;
                $prepared = $dbh->prepare('SELECT * FROM "' . $references['thread_table'] .
                     '" WHERE "thread_id" = ? LIMIT 1');
                $gen_data['thread'] = $dbh->executePreparedFetch($prepared, array($current_thread_id), PDO::FETCH_ASSOC);

                $prepared = $dbh->prepare('SELECT * FROM "' . $references['post_table'] .
                     '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC');
                $treeline = $dbh->executePreparedFetchAll($prepared, array($current_thread_id), PDO::FETCH_ASSOC);

                $gen_data['thread']['expand_post'] = ($gen_data['thread']['post_count'] >
                     $board_settings['abbreviate_thread']) ? TRUE : FALSE;
                $gen_data['thread']['first100'] = ($gen_data['thread']['post_count'] > 100) ? TRUE : FALSE;
                $gen_data['post_counter'] = 0;
            }

            $abbreviate = ($gen_data['thread']['post_count'] > $board_settings['abbreviate_thread']) ? true : false;
            $dataforce['abbreviate'] = $abbreviate;
            $gen_data['post'] = $treeline[$gen_data['post_counter']];

            if ($gen_data['post']['has_file'] == 1)
            {
                $prepared = $dbh->prepare('SELECT * FROM "' . $references['file_table'] .
                     '" WHERE "post_ref" = ? ORDER BY "file_order" ASC');
                $gen_data['files'] = $dbh->executePreparedFetchAll($prepared, array($gen_data['post']['post_number']), PDO::FETCH_ASSOC);
            }

            if ($gen_data['post']['op'] == 1)
            {
                if ($abbreviate)
                {
                    $gen_data['post_counter'] = $gen_data['thread']['post_count'] - $board_settings['abbreviate_thread'];
                    $dataforce['omitted_done'] = FALSE;
                    $new_post_element = nel_render_post($board_id, $dataforce, $render, FALSE, FALSE, $gen_data, $treeline, $dom);
                    $dataforce['omitted_done'] = TRUE;
                }
                else
                {
                    $new_post_element = nel_render_post($board_id, $dataforce, $render, FALSE, FALSE, $gen_data, $treeline, $dom);
                }
            }
            else
            {
                $new_post_element = nel_render_post($board_id, $dataforce, $render, TRUE, TRUE, $gen_data, $treeline, $dom);
            }

            $imported = $dom->importNode($new_post_element, true);
            $post_append_target->appendChild($imported);

            if ($gen_data['post']['op'] == 1)
            {
                $expand_div = $dom->getElementById('thread-expand-')->cloneNode(true);
                $expand_div->changeId('thread-expand-' . $gen_data['thread']['thread_id']);
                $post_append_target->appendChild($expand_div);
                $post_append_target = $expand_div;
                $omitted_element = $expand_div->getElementsByClassName('omitted-posts')->item(0);

                if ($abbreviate)
                {
                    $omitted_count = $gen_data['thread']['post_count'] - $board_settings['abbreviate_thread'];
                    $omitted_element->firstChild->setContent($omitted_count);
                }
                else
                {
                    $omitted_element->removeSelf();
                }
            }

            if (empty($treeline[$gen_data['post_counter'] + 1]))
            {
                $sub_page_thread_counter = ($thread_counter == $counttree - 1) ? $board_settings['threads_per_page'] : ++ $sub_page_thread_counter;
                ++ $thread_counter;
                nel_render_insert_hr($dom);
                $gen_data['post_counter'] = -1;
            }
            else
            {
                ++ $gen_data['post_counter'];
            }
        }

        $dom->getElementById('post-id-')->removeSelf();
        $dom->getElementById('thread-')->removeSelf();

        $dataforce['posts_ending'] = true;

        // if not in res display mode
        $prev = $page - 1;
        $next = $page + 1;

        $page_count = (int) ceil($counttree / $board_settings['threads_per_page']);
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

        while ($i < $page_count)
        {
            if ($i === 0)
            {
                $pages[$i] = (($page > 1) ? PHP_SELF2 . PHP_EXT : '');
            }
            else if ($i === ($page - 1) || $board_settings['page_limit'] === 1)
            {
                $pages[$i] = '';
            }
            else
            {
                $pages[$i] = PHP_SELF2 . ($i) . PHP_EXT;
            }

            ++ $i;
        }

        if ($page === $page_count || $board_settings['page_limit'] === 1)
        {
            $pages['next'] = '';
        }
        else
        {
            $pages['next'] = PHP_SELF2 . ($page) . PHP_EXT;
        }

        nel_render_index_navigation($board_id, $dom, $render, $pages);
        nel_render_thread_form_bottom($board_id, $dom);
        $render->appendHTMLFromDOM($dom);
        nel_render_footer($board_id, $render, true);

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
            $logfilename = ($page === 1) ? $references['directory'] . '/' . PHP_SELF2 . PHP_EXT : $references['directory']. '/' . PHP_SELF2 .
                 ($page - 1) . PHP_EXT;
            $file_handler->writeFile($logfilename, $render->outputRenderSet(), FILE_PERM, true);
        }

        ++ $page;
        unset($render);
    }

    if ($write)
    {
        nel_session_is_ignored('render', false);
    }
}
