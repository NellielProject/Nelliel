<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/posting_form.php';
require_once INCLUDE_PATH . 'output/post.php';

function nel_thread_generator($board, $write, $thread_id)
{
    $database = nel_database();
    $authorization = new \Nelliel\Auth\Authorization($database);
    $translator = new \Nelliel\Language\Translator();
    $session = new \Nelliel\Session($authorization);
    $site_settings = nel_parameters_and_data()->siteSettings();
    $file_handler = new \Nelliel\FileHandler();

    if ($write)
    {
        $session->isIgnored('render', true);
    }

    $dotdot = ($write) ? '../../../' : '';
    $render = new NellielTemplates\RenderCore();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'thread.html');
    $translator->translateDom($dom, $board->setting('board_language'));
    $expand_dom = $render->newDOMDocument();
    $collapse_dom = $render->newDOMDocument();
    $render->startRenderTimer();
    $dom->getElementById('form-content-action')->extSetAttribute('action',
            $dotdot . PHP_SELF . '?module=threads&area=general&board_id=' . $board->id());
    $prepared = $database->prepare('SELECT * FROM "' . $board->reference('thread_table') . '" WHERE "thread_id" = ? LIMIT 1');
    $gen_data['thread'] = $database->executePreparedFetch($prepared, array($thread_id), PDO::FETCH_ASSOC);
    $prepared = $database->prepare(
            'SELECT * FROM "' . $board->reference('post_table') . '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC');
    $treeline = $database->executePreparedFetchAll($prepared, array($thread_id), PDO::FETCH_ASSOC);

    if (empty($treeline))
    {
        $session->isIgnored('render', false);
        return;
    }

    $json_thread = new \Nelliel\API\JSON\JSONThread($board->id(), $file_handler);
    $json_thread->addThreadData($json_thread->prepareData($gen_data['thread']));
    $json_post = new \Nelliel\API\JSON\JSONPost($board->id(), $file_handler);
    $json_content = new \Nelliel\API\JSON\JSONContent($board->id(), $file_handler);
    $post_counter = 0;
    $gen_data['posts_ending'] = false;
    $gen_data['index_rendering'] = false;
    $gen_data['abbreviate'] = false;
    $hr_added = false;
    $total_posts = $gen_data['thread']['post_count'];
    $abbreviate = $total_posts > $board->setting('abbreviate_thread');

    while ($post_counter < $total_posts)
    {
        if (!isset($treeline[$post_counter]))
        {
            ++ $post_counter;
            continue;
        }

        $gen_data['post'] = $treeline[$post_counter];
        $json_post_data = $json_post->prepareData($gen_data['post']);
        $json_post->storeData($json_post_data);

        if ($post_counter === 0)
        {
            nel_render_board_header($board, $render, $dotdot, $treeline);
            nel_render_posting_form($board, $render, $thread_id, $dotdot);
        }

        if ($post_counter == $total_posts - 1)
        {
            $gen_data['posts_ending'] = true;
        }

        if ($gen_data['post']['has_file'] == 1)
        {
            $query = 'SELECT * FROM "' . $board->reference('content_table') .
                    '" WHERE "post_ref" = ? ORDER BY "content_order" ASC';
            $prepared = $database->prepare($query);
            $gen_data['files'] = $database->executePreparedFetchAll($prepared, array($gen_data['post']['post_number']),
                    PDO::FETCH_ASSOC);

            foreach ($gen_data['files'] as $content_data)
            {
                $json_post->addContentData($json_content->prepareData($content_data));
            }
        }

        if ($post_counter === 99)
        {
            $render_temp = clone $render;
            nel_render_insert_hr($dom);
            $hr_added = true;
            nel_render_general_footer($render, $board, $dotdot, true);
            $file_handler->writeFile(
                    $board->reference('page_path') . $thread_id . '/thread-' . $thread_id . '-0-100.html',
                    $render_temp->outputRenderSet(), FILE_PERM, true);
            unset($render_temp);
        }

        if ($gen_data['post']['op'] == 1)
        {
            $new_post_node = nel_render_post($board, $gen_data, $dom);
            $expand_div = $dom->getElementById('thread-expand-nci_0_0_0');
            $expand_div->changeId(
                    'thread-expand-' . \Nelliel\ContentID::createIDString($gen_data['thread']['thread_id']));
            $omitted_element = $expand_div->getElementsByClassName('omitted-posts')->item(0);

            if ($abbreviate)
            {
                $omitted_element->firstChild->setContent($total_posts - $board->setting('abbreviate_thread'));
            }
            else
            {
                $omitted_element->remove();
            }

            $import_node = $collapse_dom->importNode($expand_div->cloneNode(true), true);
            $collapse_dom->appendChild($import_node);
            $expand_div->remove();
        }
        else
        {
            $new_post_node = nel_render_post($board, $gen_data, $dom);

            if ($abbreviate && $post_counter > $total_posts - $board->setting('abbreviate_thread'))
            {
                $import_node = $collapse_dom->importNode($new_post_node, true);
                $collapse_dom->getElementById(
                        'thread-expand-' . \Nelliel\ContentID::createIDString($gen_data['thread']['thread_id']))->appendChild(
                        $import_node);
            }

            $import_node = $expand_dom->importNode($new_post_node, true);
            $expand_dom->appendChild($import_node);
        }

        $imported = $dom->importNode($new_post_node, true);
        $dom->getElementById('thread-nci_0_0_0')->appendChild($imported);
        ++ $post_counter;
        $json_thread->addPostData($json_post->getStoredData());
    }

    $dom->getElementById('post-id-nci_0_0_0')->remove();
    $dom->getElementById('thread-nci_0_0_0')->changeId('thread-nci_' . $thread_id . '_0_0');

    if (!$hr_added)
    {
        nel_render_insert_hr($dom);
    }

    nel_render_thread_form_bottom($board, $dom);
    $render->appendHTMLFromDOM($dom);
    $render->appendHTMLFromDOM($collapse_dom, 'collapse');
    $render->appendHTMLFromDOM($expand_dom, 'expand');
    nel_render_general_footer($render, $board, $dotdot, true);

    if ($write)
    {
        $file_handler->writeFile($board->reference('page_path') . $thread_id . '/thread-' . $thread_id . '.html',
                $render->outputRenderSet(), FILE_PERM, true);
        $file_handler->writeFile($board->reference('page_path') . $thread_id . '/thread-' . $thread_id . '-expand.html',
                $render->outputRenderSet('expand'), FILE_PERM, true);
        $file_handler->writeFile($board->reference('page_path') . $thread_id . '/thread-' . $thread_id . '-collapse.html',
                $render->outputRenderSet('collapse'), FILE_PERM, true);
        $json_thread->writeStoredData($board->reference('page_path') . $thread_id . '/', sprintf('thread-%d', $thread_id));
    }
    else
    {
        echo $render->outputRenderSet();
        nel_clean_exit();
    }

    if ($write)
    {
        $session->isIgnored('render', false);
    }
}
