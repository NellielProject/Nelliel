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
    $references = nel_parameters_and_data()->boardReferences($board_id);
    $board_settings = nel_parameters_and_data()->boardSettings($board_id);
    $file_handler = new \Nelliel\FileHandler();

    if ($write)
    {
        nel_sessions()->sessionIsIgnored('render', true);
    }

    $dotdot = '../../../';
    $render = new NellielTemplates\RenderCore();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'thread.html');
    nel_language()->i18nDom($dom, nel_parameters_and_data()->boardSettings($board_id, 'board_language'));
    $expand_dom = $render->newDOMDocument();
    $collapse_dom = $render->newDOMDocument();
    $render->startRenderTimer();
    $dom->getElementById('form-post-index')->extSetAttribute('action',
            $dotdot . PHP_SELF . '?module=threads&board_id=' . $board_id);
    $prepared = $dbh->prepare('SELECT * FROM "' . $references['thread_table'] . '" WHERE "thread_id" = ? LIMIT 1');
    $gen_data['thread'] = $dbh->executePreparedFetch($prepared, array($response_to), PDO::FETCH_ASSOC);
    $prepared = $dbh->prepare(
            'SELECT * FROM "' . $references['post_table'] . '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC');
    $treeline = $dbh->executePreparedFetchAll($prepared, array($response_to), PDO::FETCH_ASSOC);

    if (empty($treeline))
    {
        nel_sessions()->sessionIsIgnored('render', false);
        return;
    }

    $gen_data['post_counter'] = 0;
    $gen_data['expand_post'] = FALSE;
    $gen_data['first100'] = FALSE;
    $gen_params = array();
    $gen_params['posts_ending'] = false;
    $gen_params['index_rendering'] = false;
    $gen_params['abbreviate'] = false;
    $hr_added = false;

    while ($gen_data['post_counter'] < $gen_data['thread']['post_count'])
    {
        if (!isset($treeline[$gen_data['post_counter']]))
        {
            ++ $gen_data['post_counter'];
            continue;
        }

        $gen_data['post'] = $treeline[$gen_data['post_counter']];

        if ($gen_data['post_counter'] === 0)
        {
            nel_render_board_header($board_id, $render, $dotdot, $treeline);
            nel_render_posting_form($board_id, $render, $response_to, $dotdot);
        }

        if ($gen_data['post_counter'] == $gen_data['thread']['post_count'] - 1)
        {
            $gen_params['posts_ending'] = true;
        }

        if ($gen_data['post']['has_file'] == 1)
        {
            $prepared = $dbh->prepare(
                    'SELECT * FROM "' . $references['file_table'] . '" WHERE "post_ref" = ? ORDER BY "file_order" ASC');
            $gen_data['files'] = $dbh->executePreparedFetchAll($prepared, array($gen_data['post']['post_number']),
                    PDO::FETCH_ASSOC);
        }

        if ($gen_data['post_counter'] === 99)
        {
            $render_temp = clone $render;
            nel_render_insert_hr($dom);
            $hr_added = true;
            nel_render_board_footer($board_id, $render_temp, $dotdot, true);
            $file_handler->writeFile($references['page_path'] . $response_to . '/' . $response_to . '-0-100.html',
                    $render_temp->outputRenderSet(), FILE_PERM, true);
            unset($render_temp);
        }

        if ($gen_data['post']['op'] == 1)
        {
            $base_new_post_node = nel_render_post($board_id, $gen_params, false, $gen_data, $dom);
            $expand_div = $dom->getElementById('thread-expand-');
            $expand_div->changeId('thread-expand-' . $gen_data['thread']['thread_id']);
            $omitted_element = $expand_div->getElementsByClassName('omitted-posts')->item(0);

            if ($gen_data['thread']['post_count'] > $board_settings['abbreviate_thread'])
            {
                $omitted_count = $gen_data['thread']['post_count'] - $board_settings['abbreviate_thread'];
                $omitted_element->firstChild->setContent($omitted_count);
            }
            else
            {
                $omitted_element->removeSelf();
            }

            $import_node = $collapse_dom->importNode($expand_div->cloneNode(true), true);
            $collapse_dom->appendChild($import_node);
            $expand_div->removeSelf();
        }
        else
        {
            $base_new_post_node = nel_render_post($board_id, $gen_params, true, $gen_data, $dom);

            if ($gen_data['thread']['post_count'] > $board_settings['abbreviate_thread'])
            {
                if ($gen_data['post_counter'] > $gen_data['thread']['post_count'] - $board_settings['abbreviate_thread'])
                {
                    $import_node = $collapse_dom->importNode($base_new_post_node, true);
                    $collapse_dom->getElementById('thread-expand-' . $gen_data['thread']['thread_id'])->appendChild(
                            $import_node);
                }
            }

            $import_node = $expand_dom->importNode($base_new_post_node, true);
            $expand_dom->appendChild($import_node);
        }

        $new_post_element = nel_render_post_adjust_relative($base_new_post_node, $gen_data);
        $imported = $dom->importNode($new_post_element, true);
        $dom->getElementById('thread-')->appendChild($imported);
        ++ $gen_data['post_counter'];
    }

    $dom->getElementById('post-id-')->removeSelf();
    $dom->getElementById('thread-')->changeId('thread-' . $response_to);

    if (!$hr_added)
    {
        nel_render_insert_hr($dom);
    }

    nel_render_thread_form_bottom($board_id, $dom);
    $render->appendHTMLFromDOM($dom);
    $render->appendHTMLFromDOM($collapse_dom, 'collapse');
    $render->appendHTMLFromDOM($expand_dom, 'expand');
    nel_render_board_footer($board_id, $render, $dotdot, true);

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
        /*if ($dataforce['expand'])
         {
         echo $render->outputRenderSet('expand');
         }
         else if ($dataforce['collapse'])
         {
         echo $render->outputRenderSet('collapse');
         }
         else
         {
         echo $render->outputRenderSet();
         }

         nel_clean_exit();*/
        // TODO: Modmode stuff
    }

    if ($write)
    {
        nel_sessions()->sessionIsIgnored('render', false);
    }
}
