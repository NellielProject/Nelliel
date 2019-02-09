<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/posting_form.php';
require_once INCLUDE_PATH . 'output/post.php';

function nel_main_thread_generator(\Nelliel\Domain $domain, $response_to, $write, $page = 0)
{
    $database = nel_database();
    $authorization = new \Nelliel\Auth\Authorization($database);
    $translator = new \Nelliel\Language\Translator();
    $session = new \Nelliel\Session($authorization);
    $file_handler = new \Nelliel\FileHandler();
    $domain->renderInstance(new \Nelliel\RenderCore());
    $thread_table = $gen_data = array();
    $dotdot = ($write) ? '../' : '';
    $result = $database->query(
            'SELECT "thread_id" FROM "' . $domain->reference('threads_table') .
            '" WHERE "archive_status" = 0 ORDER BY "sticky" DESC, "last_bump_time" DESC, "last_bump_time_milli" DESC');
    $front_page_list = $result->fetchAll(PDO::FETCH_COLUMN);
    unset($result);

    $treeline = array(0);
    $counttree = count($front_page_list);
    $gen_data['posts_ending'] = false;
    $gen_data['index_rendering'] = true;
    $json_index = new \Nelliel\API\JSON\JSONIndex($domain, $file_handler);

    // Special handling when there's no content
    if ($counttree === 0)
    {
        $domain->renderInstance()->startRenderTimer();
        nel_render_board_header($domain, $dotdot, $treeline, true);
        nel_render_posting_form($domain, $response_to, $dotdot);
        nel_render_general_footer($domain, $dotdot, true);
        ;

        if ($write)
        {
            $file_handler->writeFile($domain->reference('board_directory') . '/' . MAIN_INDEX . PAGE_EXT,
                    $domain->renderInstance()->outputRenderSet(), FILE_PERM);
            $json_index->writeStoredData($domain->reference('board_directory') . '/', sprintf('index-%d', $page));
        }
        else
        {
            echo $domain->renderInstance()->outputRenderSet();
        }

        return;
    }

    $thread_counter = 0;

    while ($thread_counter < $counttree)
    {
        $domain->renderInstance(new \Nelliel\RenderCore());
        $dom = $domain->renderInstance()->newDOMDocument();
        $domain->renderInstance()->loadTemplateFromFile($dom, 'thread.html');
        $domain->renderInstance()->startRenderTimer();
        $translator->translateDom($dom, $domain->setting('language'));
        $dom->getElementById('form-content-action')->extSetAttribute('action',
                $dotdot . MAIN_SCRIPT . '?module=threads&board_id=' . $domain->id());
        nel_render_board_header($domain, $dotdot, $treeline, true);
        nel_render_posting_form($domain, $response_to, $dotdot);
        $sub_page_thread_counter = 0;
        $post_counter = -1;

        while ($sub_page_thread_counter < $domain->setting('threads_per_page'))
        {
            $json_content = new \Nelliel\API\JSON\JSONContent($domain, $file_handler);

            if ($post_counter === -1)
            {
                $json_thread = new \Nelliel\API\JSON\JSONThread($domain, $file_handler);
                $current_thread_id = $front_page_list[$thread_counter];
                $thread_element = $dom->getElementById('thread-cid_0_0_0')->cloneNode();
                $thread_element->changeId('thread-cid_' . $current_thread_id . '_0_0');
                $dom->getElementById('form-content-action')->appendChild($thread_element);
                $post_append_target = $thread_element;
                $query = 'SELECT * FROM "' . $domain->reference('threads_table') . '" WHERE "thread_id" = ?';
                $prepared = $database->prepare($query);
                $gen_data['thread'] = $database->executePreparedFetch($prepared, [$current_thread_id],
                        PDO::FETCH_ASSOC);
                $json_thread->prepareData($gen_data['thread'], true);
                $post_count = $gen_data['thread']['post_count'];
                $abbreviate = $post_count > $domain->setting('abbreviate_thread');
                $query = 'SELECT * FROM "' . $domain->reference('posts_table') .
                        '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC';
                $prepared = $database->prepare($query);
                $treeline = $database->executePreparedFetchAll($prepared, [$current_thread_id], PDO::FETCH_ASSOC);

                $gen_data['thread']['first100'] = $post_count > 100;
                $post_counter = 0;
            }

            $gen_data['abbreviate'] = $abbreviate;
            $gen_data['post'] = $treeline[$post_counter];
            $json_post = new \Nelliel\API\JSON\JSONPost($domain, $file_handler);
            $json_post->prepareData($gen_data['post'], true);

            if ($gen_data['post']['has_file'] == 1)
            {
                $query = 'SELECT * FROM "' . $domain->reference('content_table') .
                        '" WHERE "post_ref" = ? ORDER BY "content_order" ASC';
                $prepared = $database->prepare($query);
                $gen_data['files'] = $database->executePreparedFetchAll($prepared, [
                    $gen_data['post']['post_number']], PDO::FETCH_ASSOC);

                foreach ($gen_data['files'] as $content_data)
                {
                    $json_post->addContentData($json_content->prepareData($content_data));
                }
            }

            $new_post_element = nel_render_post($domain, $gen_data, $dom);
            $imported = $dom->importNode($new_post_element, true);
            $post_append_target->appendChild($imported);

            if ($gen_data['post']['op'] == 1)
            {
                $thread_content_id = \Nelliel\ContentID::createIDString($gen_data['thread']['thread_id']);
                $expand_div = $dom->getElementById('thread-expand-cid_0_0_0')->cloneNode(true);
                $expand_div->changeId('thread-expand-' . $thread_content_id);
                $post_append_target->appendChild($expand_div);
                $post_append_target = $expand_div;
                $omitted_element = $expand_div->getElementsByClassName('omitted-posts')->item(0);

                if ($abbreviate)
                {
                    $post_counter = $post_count - $domain->setting('abbreviate_thread');
                    $omitted_count = $post_count - $domain->setting('abbreviate_thread');
                    $omitted_element->firstChild->setContent($omitted_count);
                }
                else
                {
                    $omitted_element->remove();
                }
            }

            $json_thread->addPostData($json_post->retrieveData(true));

            if (empty($treeline[$post_counter + 1]))
            {
                $json_index->addThreadData($json_thread->retrieveData(true));
                $sub_page_thread_counter = ($thread_counter == $counttree - 1) ? $domain->setting('threads_per_page') : ++ $sub_page_thread_counter;
                ++ $thread_counter;
                nel_render_insert_hr($dom);
                $post_counter = -1;
            }
            else
            {
                ++ $post_counter;
            }
        }

        $gen_data['index']['thread_count'] = $thread_counter;
        $dom->getElementById('post-id-cid_0_0_0')->remove();
        $dom->getElementById('thread-cid_0_0_0')->remove();
        $gen_data['posts_ending'] = true;
        $page_count = (int) ceil($counttree / $domain->setting('threads_per_page'));
        $pages = array();
        $modmode_base = 'imgboard.php?module=render&action=view-index&modmode=true&index=';
        $index_filename = 'index' . PAGE_EXT;
        $index_format = $domain->setting('index_filename_format');
        $last_page = $page_count - 1;
        $nav_pieces = array();
        $nav_pieces['prev']['text'] = _gettext('Previous');

        if ($page === 0)
        {
            $nav_pieces[0]['link'] = '';
        }
        else
        {
            $nav_pieces[0]['link'] = 'index' . PAGE_EXT;
        }

        $nav_pieces[0]['text'] = 0;

        for ($i = 1; $i < $page_count; ++ $i)
        {
            if ($i === $page)
            {
                $nav_pieces[$i]['link'] = '';
            }
            else
            {
                $nav_pieces[$i]['link'] = sprintf($index_format, $i) . PAGE_EXT;
            }

            $nav_pieces[$i]['text'] = $i;
        }

        $nav_pieces['next']['text'] = _gettext('Next');
        $nav_pieces['prev']['link'] = $nav_pieces[0]['link'];

        if ($page === $last_page)
        {
            $nav_pieces[$last_page]['link'] = '';
            $nav_pieces['next']['link'] = $nav_pieces[$last_page]['link'];
        }
        else
        {
            $nav_pieces['next']['link'] = $nav_pieces[$page + 1]['link'];
        }

        nel_render_index_navigation($domain, $dom, $nav_pieces);
        nel_render_thread_form_bottom($domain, $dom);
        $domain->renderInstance()->appendHTMLFromDOM($dom);
        nel_render_general_footer($domain, $dotdot, true);

        if ($page > 0)
        {
            $index_filename = sprintf($index_format, $page) . PAGE_EXT;
        }
        else
        {
            $index_filename = 'index' . PAGE_EXT;
        }

        if (!$write)
        {
            echo $domain->renderInstance()->outputRenderSet();
            nel_clean_exit();
        }
        else
        {
            $file_handler->writeFile($domain->reference('board_directory') . '/' . $index_filename,
                    $domain->renderInstance()->outputRenderSet(), FILE_PERM, true);
            $json_index->prepareData($gen_data['index'], true);
            $json_index->writeStoredData($domain->reference('board_directory') . '/', sprintf('index-%d', $page));
        }

        ++ $page;
    }
}
