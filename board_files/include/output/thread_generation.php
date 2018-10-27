<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/posting_form.php';
require_once INCLUDE_PATH . 'output/post.php';

function nel_thread_generator($board_id, $write, $response_to)
{
    $dbh = nel_database();
    $authorization = new \Nelliel\Auth\Authorization($dbh);
    $language = new \Nelliel\Language\Language($authorization);
    $sessions = new \Nelliel\Sessions($authorization);
    $references = nel_parameters_and_data()->boardReferences($board_id);
    $board_settings = nel_parameters_and_data()->boardSettings($board_id);
    $file_handler = new \Nelliel\FileHandler();

    if ($write)
    {
        $sessions->isIgnored('render', true);
    }

    $dotdot = ($write) ? '../../../' : '';
    $render = new NellielTemplates\RenderCore();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'thread.html');
    $language->i18nDom($dom, nel_parameters_and_data()->boardSettings($board_id, 'board_language'));
    $expand_dom = $render->newDOMDocument();
    $collapse_dom = $render->newDOMDocument();
    $render->startRenderTimer();
    $dom->getElementById('form-post-index')->extSetAttribute('action',
            $dotdot . PHP_SELF . '?module=threads&area=general&board_id=' . $board_id);
    $prepared = $dbh->prepare('SELECT * FROM "' . $references['thread_table'] . '" WHERE "thread_id" = ? LIMIT 1');
    $gen_data['thread'] = $dbh->executePreparedFetch($prepared, array($response_to), PDO::FETCH_ASSOC);
    $prepared = $dbh->prepare(
            'SELECT * FROM "' . $references['post_table'] . '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC');
    $treeline = $dbh->executePreparedFetchAll($prepared, array($response_to), PDO::FETCH_ASSOC);

    if (empty($treeline))
    {
        $sessions->isIgnored('render', false);
        return;
    }

    $post_counter = 0;
    $gen_data['posts_ending'] = false;
    $gen_data['index_rendering'] = false;
    $gen_data['abbreviate'] = false;
    $hr_added = false;
    $total_posts = $gen_data['thread']['post_count'];
    $abbreviate = $total_posts > $board_settings['abbreviate_thread'];

    while ($post_counter < $total_posts)
    {
        if (!isset($treeline[$post_counter]))
        {
            ++ $post_counter;
            continue;
        }

        $gen_data['post'] = $treeline[$post_counter];

        if ($post_counter === 0)
        {
            nel_render_board_header($board_id, $render, $dotdot, $treeline);
            nel_render_posting_form($board_id, $render, $response_to, $dotdot);
        }

        if ($post_counter == $total_posts - 1)
        {
            $gen_data['posts_ending'] = true;
        }

        if ($gen_data['post']['has_file'] == 1)
        {
            $query = 'SELECT * FROM "' . $references['file_table'] . '" WHERE "post_ref" = ? ORDER BY "file_order" ASC';
            $prepared = $dbh->prepare($query);
            $gen_data['files'] = $dbh->executePreparedFetchAll($prepared, array($gen_data['post']['post_number']),
                    PDO::FETCH_ASSOC);
        }

        if ($post_counter === 99)
        {
            $render_temp = clone $render;
            nel_render_insert_hr($dom);
            $hr_added = true;
            nel_render_general_footer($render, $board_id, $dotdot, true);
            $file_handler->writeFile($references['page_path'] . $response_to . '/' . $response_to . '-0-100.html',
                    $render_temp->outputRenderSet(), FILE_PERM, true);
            unset($render_temp);
        }

        if ($gen_data['post']['op'] == 1)
        {
            $new_post_node = nel_render_post($board_id, $gen_data, $dom);
            $expand_div = $dom->getElementById('thread-expand-');
            $expand_div->changeId('thread-expand-' . $gen_data['thread']['thread_id']);
            $omitted_element = $expand_div->getElementsByClassName('omitted-posts')->item(0);

            if ($abbreviate)
            {
                $omitted_element->firstChild->setContent($total_posts - $board_settings['abbreviate_thread']);
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
            $new_post_node = nel_render_post($board_id, $gen_data, $dom);

            if ($abbreviate && $post_counter > $total_posts - $board_settings['abbreviate_thread'])
            {
                $import_node = $collapse_dom->importNode($new_post_node, true);
                $collapse_dom->getElementById('thread-expand-' . $gen_data['thread']['thread_id'])->appendChild(
                        $import_node);
            }

            $import_node = $expand_dom->importNode($new_post_node, true);
            $expand_dom->appendChild($import_node);
        }

        $imported = $dom->importNode($new_post_node, true);
        $dom->getElementById('thread-')->appendChild($imported);
        ++ $post_counter;
    }

    $dom->getElementById('post-id-')->remove();
    $dom->getElementById('thread-')->changeId('thread-' . $response_to);

    if (!$hr_added)
    {
        nel_render_insert_hr($dom);
    }

    nel_render_thread_form_bottom($board_id, $dom);
    $render->appendHTMLFromDOM($dom);
    $render->appendHTMLFromDOM($collapse_dom, 'collapse');
    $render->appendHTMLFromDOM($expand_dom, 'expand');
    nel_render_general_footer($render, $board_id, $dotdot, true);

    if ($write)
    {
        $file_handler->writeFile($references['page_path'] . $response_to . '/' . $response_to . '.html',
                $render->outputRenderSet(), FILE_PERM, true);
        $file_handler->writeFile($references['page_path'] . $response_to . '/' . $response_to . '-expand.html',
                $render->outputRenderSet('expand'), FILE_PERM, true);
        $file_handler->writeFile($references['page_path'] . $response_to . '/' . $response_to . '-collapse.html',
                $render->outputRenderSet('collapse'), FILE_PERM, true);
    }
    else
    {
        echo $render->outputRenderSet();
        nel_clean_exit();
    }

    if ($write)
    {
        $sessions->isIgnored('render', false);
    }
}
