<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/post.php';

function nel_main_thread_generator(\Nelliel\Domain $domain, $response_to, $write, $page = 0)
{
    $database = nel_database();
    $translator = new \Nelliel\Language\Translator();
    $session = new \Nelliel\Session();
    $file_handler = new \Nelliel\FileHandler();
    $thread_table = $gen_data = array();
    $site_domain = new \Nelliel\DomainSite(new \Nelliel\CacheHandler(), $database, $translator);
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
    $output_posting_form = new \Nelliel\Output\OutputPostingForm($domain);
    $output_header = new \Nelliel\Output\OutputHeader($domain);

    // Special handling when there's no content
    if ($counttree === 0)
    {
        $domain->renderInstance()->startRenderTimer();
        $output_header->render(
                ['header_type' => 'board', 'dotdot' => $dotdot, 'treeline' => $treeline, 'index_render' => true]);
        $output_posting_form->render(['dotdot' => $dotdot, 'response_to' => $response_to]);
        nel_render_general_footer($domain, $dotdot, true);

        if ($write)
        {
            $file_handler->writeFile($domain->reference('board_directory') . '/' . MAIN_INDEX . PAGE_EXT,
                    $domain->renderInstance()->outputRenderSet(), FILE_PERM);
            $json_index->writeStoredData($domain->reference('board_directory') . '/', sprintf('index-%d', $page + 1));
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
        $domain->renderInstance()->clearRenderSet();
        $dom = $domain->renderInstance()->newDOMDocument();
        $domain->renderInstance()->loadTemplateFromFile($dom, 'thread.html');
        $domain->renderInstance()->startRenderTimer();
        $translator->translateDom($dom, $domain->setting('language'));
        $dom->getElementById('form-content-action')->extSetAttribute('action',
                $dotdot . MAIN_SCRIPT . '?module=threads&board_id=' . $domain->id());
        $output_header->render(
                ['header_type' => 'board', 'dotdot' => $dotdot, 'treeline' => $treeline, 'index_render' => true]);
        $output_posting_form->render(['dotdot' => $dotdot, 'response_to' => $response_to]);
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
                $json_thread->storeData($json_thread->prepareData($gen_data['thread']), 'thread');
                $post_count = $gen_data['thread']['post_count'];
                $abbreviate = $post_count > $domain->setting('abbreviate_thread');
                $query = 'SELECT * FROM "' . $domain->reference('posts_table') .
                        '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC';
                $prepared = $database->prepare($query);
                $treeline = $database->executePreparedFetchAll($prepared, [$current_thread_id], PDO::FETCH_ASSOC);

                $gen_data['thread']['first100'] = $post_count > 100;
                $post_counter = 0;
            }

            if (empty($treeline[$post_counter]))
            {
                $sub_page_thread_counter = ($thread_counter == $counttree - 1) ? $domain->setting('threads_per_page') : ++ $sub_page_thread_counter;
                ++ $thread_counter;
                $post_counter = -1;
                continue;
            }

            $gen_data['abbreviate'] = $abbreviate;
            $gen_data['post'] = $treeline[$post_counter];
            $json_post = new \Nelliel\API\JSON\JSONPost($domain, $file_handler);
            $json_post->storeData($json_post->prepareData($gen_data['post']), 'post');

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

            $json_thread->addPostData($json_post->retrieveData());

            if (empty($treeline[$post_counter + 1]))
            {
                $json_index->addThreadData($json_thread->retrieveData());
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
        $index_format = $site_domain->setting('index_filename_format');

        // Set up the array of navgation elements
        $nav_pieces = array();
        $nav_pieces['prev']['text'] = _gettext('Previous');
        $prev_filename = ($page < 2) ? 'index' : $index_format;
        $nav_pieces['prev']['link'] = ($page !== 0) ? sprintf($prev_filename, ($page)) . PAGE_EXT : '';

        for ($i = 1; $i < $page_count; ++ $i)
        {
            $link_filename = ($i === 1) ? 'index' : $index_format;
            $nav_pieces[$i]['link'] = ($i !== $page + 1) ? sprintf($link_filename, $i) . PAGE_EXT : '';
            $nav_pieces[$i]['text'] = $i;
        }

        $nav_pieces['next']['text'] = _gettext('Next');
        $nav_pieces['next']['link'] = ($page !== $page_count - 2) ? sprintf($index_format, ($page + 2)) . PAGE_EXT : '';

        nel_render_index_navigation($domain, $dom, $nav_pieces);
        nel_render_thread_form_bottom($domain, $dom);
        $domain->renderInstance()->appendHTMLFromDOM($dom);
        nel_render_general_footer($domain, $dotdot, true);

        $index_filename = ($page > 0) ? sprintf($index_format, ($page + 1)) . PAGE_EXT : 'index' . PAGE_EXT;

        if (!$write)
        {
            echo $domain->renderInstance()->outputRenderSet();
            nel_clean_exit();
        }
        else
        {
            $file_handler->writeFile($domain->reference('board_directory') . '/' . $index_filename,
                    $domain->renderInstance()->outputRenderSet(), FILE_PERM, true);
            $json_index->storeData($json_index->prepareData($gen_data['index']), 'index');
            $json_index->writeStoredData($domain->reference('board_directory') . '/', sprintf('index-%d', $page + 1));
        }

        ++ $page;
    }
}
