<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/posting_form.php';
require_once INCLUDE_PATH . 'output/post.php';

function nel_main_thread_generator($board, $response_to, $write, $page = 0)
{
    $database = nel_database();
    $authorization = new \Nelliel\Auth\Authorization($database);
    $translator = new \Nelliel\Language\Translator();
    $session = new \Nelliel\Session($authorization);
    $file_handler = new \Nelliel\FileHandler();
    $board->renderInstance(new NellielTemplates\RenderCore());
    $thread_table = $gen_data = array();
    $dotdot = ($write) ? '../' : '';

    if ($write)
    {
        $session->isIgnored('render', true);
    }

    $result = $database->query(
            'SELECT "thread_id" FROM "' . $board->reference('thread_table') .
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
        $board->renderInstance()->startRenderTimer();
        nel_render_board_header($board, $dotdot, $treeline);
        nel_render_posting_form($board, $board->renderInstance(), $response_to, $dotdot);
        nel_render_general_footer($board->renderInstance(), $board, $dotdot, true);;

        if ($write)
        {
            $file_handler->writeFile($board->reference('board_directory') . '/' . PHP_SELF2 . PHP_EXT,
                    $board->renderInstance()->outputRenderSet(), FILE_PERM);
            $session->isIgnored('render', false);
        }
        else
        {
            echo $board->renderInstance()->outputRenderSet();
        }

        return;
    }

    $thread_counter = $page * $board->setting('threads_per_page');
    $post_counter = -1;

    while ($thread_counter < $counttree)
    {
        $dom = $board->renderInstance()->newDOMDocument();
        $board->renderInstance()->loadTemplateFromFile($dom, 'thread.html');
        $board->renderInstance()->startRenderTimer();
        $translator->translateDom($dom, $board->setting('board_language'));
        $dom->getElementById('form-content-action')->extSetAttribute('action',
                $dotdot . PHP_SELF . '?module=threads&area=general&board_id=' . $board->id());
        nel_render_board_header($board, $dotdot, $treeline);
        nel_render_posting_form($board, $board->renderInstance(), $response_to, $dotdot);
        $sub_page_thread_counter = 0;

        while ($sub_page_thread_counter < $board->setting('threads_per_page'))
        {
            if ($post_counter === -1)
            {
                $current_thread_id = $front_page_list[$thread_counter];
                $thread_element = $dom->getElementById('thread-nci_0_0_0')->cloneNode();
                $thread_element->changeId('thread-nci_' . $current_thread_id . '_0_0');
                $dom->getElementById('form-content-action')->appendChild($thread_element);
                $post_append_target = $thread_element;
                $query = 'SELECT * FROM "' . $board->reference('thread_table') . '" WHERE "thread_id" = ? LIMIT 1';
                $prepared = $database->prepare($query);
                $gen_data['thread'] = $database->executePreparedFetch($prepared, array($current_thread_id), PDO::FETCH_ASSOC);
                $post_count = $gen_data['thread']['post_count'];
                $abbreviate = $post_count > $board->setting('abbreviate_thread');
                $query = 'SELECT * FROM "' . $board->reference('post_table') .
                        '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC';
                $prepared = $database->prepare($query);
                $treeline = $database->executePreparedFetchAll($prepared, array($current_thread_id), PDO::FETCH_ASSOC);

                $gen_data['thread']['first100'] = $post_count > 100;
                $post_counter = 0;
            }

            $gen_data['abbreviate'] = $abbreviate;
            $gen_data['post'] = $treeline[$post_counter];

            if ($gen_data['post']['has_file'] == 1)
            {
                $query = 'SELECT * FROM "' . $board->reference('content_table') .
                        '" WHERE "post_ref" = ? ORDER BY "content_order" ASC';
                $prepared = $database->prepare($query);
                $gen_data['files'] = $database->executePreparedFetchAll($prepared, array($gen_data['post']['post_number']),
                        PDO::FETCH_ASSOC);
            }

            $new_post_element = nel_render_post($board, $gen_data, $dom);
            $imported = $dom->importNode($new_post_element, true);
            $post_append_target->appendChild($imported);

            if ($gen_data['post']['op'] == 1)
            {
                $thread_content_id = \Nelliel\ContentID::createIDString($gen_data['thread']['thread_id']);
                $expand_div = $dom->getElementById('thread-expand-nci_0_0_0')->cloneNode(true);
                $expand_div->changeId('thread-expand-' . $thread_content_id);
                $post_append_target->appendChild($expand_div);
                $post_append_target = $expand_div;
                $omitted_element = $expand_div->getElementsByClassName('omitted-posts')->item(0);

                if ($abbreviate)
                {
                    $post_counter = $post_count - $board->setting('abbreviate_thread');
                    $omitted_count = $post_count - $board->setting('abbreviate_thread');
                    $omitted_element->firstChild->setContent($omitted_count);
                }
                else
                {
                    $omitted_element->remove();
                }
            }

            if (empty($treeline[$post_counter + 1]))
            {
                $sub_page_thread_counter = ($thread_counter == $counttree - 1) ? $board->setting('threads_per_page') : ++ $sub_page_thread_counter;
                ++ $thread_counter;
                nel_render_insert_hr($dom);
                $post_counter = -1;
            }
            else
            {
                ++ $post_counter;
            }
        }

        $dom->getElementById('post-id-nci_0_0_0')->remove();
        $dom->getElementById('thread-nci_0_0_0')->remove();
        $gen_data['posts_ending'] = true;
        $page_count = (int) ceil($counttree / $board->setting('threads_per_page'));
        $pages = array();
        $modmode_base = 'imgboard.php?module=render&area=view-index&section=';

        if ($page === 0)
        {
            $prev = '';
        }
        else if ($page === 1)
        {
            $prev = ($write) ? PHP_SELF2 . PHP_EXT : $modmode_base . '0&board_id=' . $board->id();
        }
        else
        {
            $prev = ($write) ? PHP_SELF2 . ($page - 1) . PHP_EXT : $modmode_base . ($page - 1) . '&board_id=' . $board->id();
        }

        if ($page === ($page_count - 1) || $board->setting('page_limit') === 1)
        {
            $next = '';
        }
        else
        {
            $next = ($write) ? PHP_SELF2 . ($page + 1) . PHP_EXT : $modmode_base . ($page + 1) . '&board_id=' . $board->id();
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

        nel_render_index_navigation($board, $dom, $board->renderInstance(), $pages);
        nel_render_thread_form_bottom($board, $dom);
        $board->renderInstance()->appendHTMLFromDOM($dom);
        nel_render_general_footer($board->renderInstance(), $board, $dotdot, true);

        if (!$write)
        {
            echo $board->renderInstance()->outputRenderSet();
            nel_clean_exit();
        }
        else
        {
            $logfilename = ($page === 0) ? $board->reference('board_directory') . '/' . PHP_SELF2 . PHP_EXT : $board->reference('board_directory') .
                    '/' . PHP_SELF2 . '-' . $page . PHP_EXT;
            $file_handler->writeFile($logfilename, $board->renderInstance()->outputRenderSet(), FILE_PERM, true);
        }

        ++ $page;
        //unset($board->renderInstance());
    }

    if ($write)
    {
        $session->isIgnored('render', false);
    }
}
