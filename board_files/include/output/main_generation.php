<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/posting_form.php';
require_once INCLUDE_PATH . 'output/post.php';

function nel_main_thread_generator($board_id, $response_to, $write, $page = 0)
{
    $dbh = nel_database();
    $language = new \Nelliel\language\Language(nel_authorize());
    $sessions = new \Nelliel\Sessions();
    $references = nel_parameters_and_data()->boardReferences($board_id);
    $board_settings = nel_parameters_and_data()->boardSettings($board_id);
    $file_handler = new \Nelliel\FileHandler();
    $thread_table = $gen_data = array();
    $dotdot = ($write) ? '../' : '';

    if ($write)
    {
        $sessions->sessionIsIgnored('render', true);
    }

    $result = $dbh->query(
            'SELECT "thread_id" FROM "' . $references['thread_table'] .
            '" WHERE "archive_status" = 0 ORDER BY "sticky" DESC, "last_bump_time" DESC, "last_bump_time_milli" DESC');
    $front_page_list = $result->fetchAll(PDO::FETCH_COLUMN);
    unset($result);

    $treeline = array(0);
    $counttree = count($front_page_list);
    $gen_data['posts_ending'] = false;
    $gen_data['index_rendering'] = true;

    // Special handling when there's no content
    if ($counttree === 0)
    {
        $render = new NellielTemplates\RenderCore();
        $render->startRenderTimer();
        $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
        nel_render_board_header($board_id, $render, $dotdot, $treeline);
        nel_render_posting_form($board_id, $render, $response_to, $dotdot);
        nel_render_general_footer($render, $board_id, $dotdot, true);;

        if ($write)
        {
            $file_handler->writeFile($references['board_directory'] . '/' . PHP_SELF2 . PHP_EXT,
                    $render->outputRenderSet(), FILE_PERM);
            $sessions->sessionIsIgnored('render', false);
        }
        else
        {
            echo $render->outputRenderSet();
        }

        return;
    }

    $thread_counter = $page * $board_settings['threads_per_page'];
    $post_counter = -1;

    while ($thread_counter < $counttree)
    {
        $render = new NellielTemplates\RenderCore();
        $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
        $dom = $render->newDOMDocument();
        $render->loadTemplateFromFile($dom, 'thread.html');
        $render->startRenderTimer();
        $language->i18nDom($dom, nel_parameters_and_data()->boardSettings($board_id, 'board_language'));
        $dom->getElementById('form-post-index')->extSetAttribute('action',
                $dotdot . PHP_SELF . '?module=threads&area=general&board_id=' . $board_id);
        nel_render_board_header($board_id, $render, $dotdot, $treeline);
        nel_render_posting_form($board_id, $render, $response_to, $dotdot);
        $sub_page_thread_counter = 0;

        while ($sub_page_thread_counter < $board_settings['threads_per_page'])
        {
            if ($post_counter === -1)
            {
                $current_thread_id = $front_page_list[$thread_counter];
                $thread_element = $dom->getElementById('thread-')->cloneNode();
                $thread_element->changeId('thread-' . $current_thread_id);
                $dom->getElementById('outer-div')->appendChild($thread_element);
                $post_append_target = $thread_element;
                $query = 'SELECT * FROM "' . $references['thread_table'] . '" WHERE "thread_id" = ? LIMIT 1';
                $prepared = $dbh->prepare($query);
                $gen_data['thread'] = $dbh->executePreparedFetch($prepared, array($current_thread_id), PDO::FETCH_ASSOC);
                $post_count = $gen_data['thread']['post_count'];
                $abbreviate = $post_count > $board_settings['abbreviate_thread'];
                $query = 'SELECT * FROM "' . $references['post_table'] .
                        '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC';
                $prepared = $dbh->prepare($query);
                $treeline = $dbh->executePreparedFetchAll($prepared, array($current_thread_id), PDO::FETCH_ASSOC);

                $gen_data['thread']['first100'] = $post_count > 100;
                $post_counter = 0;
            }

            $gen_data['abbreviate'] = $abbreviate;
            $gen_data['post'] = $treeline[$post_counter];

            if ($gen_data['post']['has_file'] == 1)
            {
                $query = 'SELECT * FROM "' . $references['file_table'] .
                        '" WHERE "post_ref" = ? ORDER BY "file_order" ASC';
                $prepared = $dbh->prepare($query);
                $gen_data['files'] = $dbh->executePreparedFetchAll($prepared, array($gen_data['post']['post_number']),
                        PDO::FETCH_ASSOC);
            }

            $new_post_element = nel_render_post($board_id, $gen_data, $dom);
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
                    $post_counter = $post_count - $board_settings['abbreviate_thread'];
                    $omitted_count = $post_count - $board_settings['abbreviate_thread'];
                    $omitted_element->firstChild->setContent($omitted_count);
                }
                else
                {
                    $omitted_element->remove();
                }
            }

            if (empty($treeline[$post_counter + 1]))
            {
                $sub_page_thread_counter = ($thread_counter == $counttree - 1) ? $board_settings['threads_per_page'] : ++ $sub_page_thread_counter;
                ++ $thread_counter;
                nel_render_insert_hr($dom);
                $post_counter = -1;
            }
            else
            {
                ++ $post_counter;
            }
        }

        $dom->getElementById('post-id-')->remove();
        $dom->getElementById('thread-')->remove();
        $gen_data['posts_ending'] = true;
        $page_count = (int) ceil($counttree / $board_settings['threads_per_page']);
        $pages = array();
        $modmode_base = 'imgboard.php?module=render&area=view-index&section=';

        if ($page === 0)
        {
            $prev = '';
        }
        else if ($page === 1)
        {
            $prev = ($write) ? PHP_SELF2 . PHP_EXT : $modmode_base . '0&board_id=' . $board_id;
        }
        else
        {
            $prev = ($write) ? PHP_SELF2 . ($page - 1) . PHP_EXT : $modmode_base . ($page - 1) . '&board_id=' . $board_id;
        }

        if ($page === ($page_count - 1) || $board_settings['page_limit'] === 1)
        {
            $next = '';
        }
        else
        {
            $next = ($write) ? PHP_SELF2 . ($page + 1) . PHP_EXT : $modmode_base . ($page + 1) . '&board_id=' . $board_id;
        }

        $pages[_gettext('Previous')] = $prev;
        $i = 0;

        while ($i < $page_count)
        {
            if ($i === 0)
            {
                $pages[$i] = $prev;
            }
            else if ($i === ($page_count - 1))
            {
                $pages[$i] = $next;
            }
            else
            {
                $pages[$i] = PHP_SELF2 . ($i) . PHP_EXT;
            }

            ++ $i;
        }

        $pages[_gettext('Next')] = $next;

        nel_render_index_navigation($board_id, $dom, $render, $pages);
        nel_render_thread_form_bottom($board_id, $dom);
        $render->appendHTMLFromDOM($dom);
        nel_render_general_footer($render, $board_id, $dotdot, true);

        if (!$write)
        {
            echo $render->outputRenderSet();
            nel_clean_exit();
        }
        else
        {
            $logfilename = ($page === 0) ? $references['board_directory'] . '/' . PHP_SELF2 . PHP_EXT : $references['board_directory'] .
                    '/' . PHP_SELF2 . $page . PHP_EXT;
            $file_handler->writeFile($logfilename, $render->outputRenderSet(), FILE_PERM, true);
        }

        ++ $page;
        unset($render);
    }

    if ($write)
    {
        $sessions->sessionIsIgnored('render', false);
    }
}
