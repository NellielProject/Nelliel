<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_thread_generator($dataforce, $write, $write_id)
{
    $dbh = nel_database();

    if($write)
    {
        nel_session_is_ignored('render', true);
    }

    $dataforce['dotdot'] = '../../';
    $render = new NellielTemplates\RenderCore();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'thread.html');
    nel_process_i18n($dom);
    $expand_dom = $render->newDOMDocument();
    $collapse_dom = $render->newDOMDocument();
    $render->startRenderTimer();
    $dom->getElementById('form-post-index')->extSetAttribute('action', $dataforce['dotdot'] . PHP_SELF);

    $dataforce['response_id'] = $write_id;
    $prepared = $dbh->prepare('SELECT * FROM "' . THREAD_TABLE . '" WHERE "thread_id" = ?');
    $gen_data['thread'] = $dbh->executePreparedFetch($prepared, array($write_id), PDO::FETCH_ASSOC);

    $prepared = $dbh->prepare('SELECT * FROM "' . POST_TABLE . '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC');
    $treeline = $dbh->executePreparedFetchAll($prepared, array($write_id), PDO::FETCH_ASSOC);

    if (empty($treeline))
    {
        return;
    }

    $gen_data['post_counter'] = 0;
    $gen_data['expand_post'] = FALSE;
    $dataforce['omitted_done'] = TRUE;
    $gen_data['first100'] = FALSE;
    $dataforce['posts_ending'] = false;
    $dataforce['index_rendering'] = false;
    $dataforce['abbreviate'] = false;

    while ($gen_data['post_counter'] < $gen_data['thread']['post_count'])
    {
        $gen_data['post'] = $treeline[$gen_data['post_counter']];

        if ($gen_data['post_counter'] === 0)
        {
            nel_render_header($dataforce, $render, $treeline);
            nel_render_posting_form($dataforce, $render);
        }

        if($gen_data['post_counter'] == $gen_data['thread']['post_count'] - 1)
        {
            $dataforce['posts_ending'] = true;
        }

        if ($gen_data['post']['has_file'] == 1)
        {
            $prepared = $dbh->prepare('SELECT * FROM "' . FILE_TABLE . '" WHERE "post_ref" = ? ORDER BY "file_order" ASC');
            $gen_data['files'] = $dbh->executePreparedFetchAll($prepared, array($gen_data['post']['post_number']), PDO::FETCH_ASSOC);
        }

        if ($gen_data['post_counter'] === 99)
        {
            $render_temp = clone $render;
            nel_render_insert_hr($dom);
            nel_render_footer($render_temp, true);
            nel_write_file(PAGE_PATH . $write_id . '/' . $write_id. '-0-100.html', $render_temp->outputRenderSet(), FILE_PERM, true);
            unset($render_temp);
        }

        if ($gen_data['post']['op'] == 1)
        {
            $base_new_post_node = nel_render_post($dataforce, $render, FALSE, FALSE, $gen_data, $treeline, $dom);

            $expand_div = $dom->getElementById('thread-expand-')->cloneNode(true);
            $expand_div->changeId('thread-expand-' . $gen_data['thread']['thread_id']);
            $omitted_element = $expand_div->getElementsByClassName('omitted-posts')->item(0);

            if ($gen_data['thread']['post_count'] > BS_ABBREVIATE_THREAD)
            {
                $omitted_count = $gen_data['thread']['post_count'] - BS_ABBREVIATE_THREAD;
                $omitted_element->firstChild->setContent($omitted_count);
            }
            else
            {
                $omitted_element->removeSelf();
            }

            //nel_process_i18n($expand_div);
            $import_node = $collapse_dom->importNode($expand_div, true);
            $collapse_dom->appendChild($import_node);
        }
        else
        {
            $expand_div = $dom->getElementById('thread-expand-');

            if(!is_null($expand_div))
            {
                $expand_div->removeSelf();
            }

            $base_new_post_node = nel_render_post($dataforce, $render, TRUE, FALSE, $gen_data, $treeline, $dom);

            if ($gen_data['thread']['post_count'] > BS_ABBREVIATE_THREAD)
            {
                if ($gen_data['post_counter'] > $gen_data['thread']['post_count'] - BS_ABBREVIATE_THREAD)
                {
                    $import_node = $collapse_dom->importNode($base_new_post_node, true);
                    $collapse_dom->getElementById('thread-expand-' . $gen_data['thread']['thread_id'])->appendChild($import_node);
                }
            }

            $import_node = $expand_dom->importNode($base_new_post_node, true);
            $expand_dom->appendChild($import_node);
        }

        $new_post_element = nel_render_post_adjust_relative($base_new_post_node, $gen_data);
        $imported = $dom->importNode($new_post_element, true);
        $dom->getElementById('outer-div')->appendChild($imported);
        ++ $gen_data['post_counter'];
    }

    $dom->getElementById('post-id-')->removeSelf();
    nel_render_insert_hr($dom);
    $render->appendHTMLFromDOM($dom);
    $render->appendHTMLFromDOM($collapse_dom, 'collapse');
    $render->appendHTMLFromDOM($expand_dom, 'expand');
    nel_render_footer($render, true);

    if ($write)
    {
        nel_write_file(PAGE_PATH . $write_id . '/' . $write_id . '.html', $render->outputRenderSet(), FILE_PERM, true);
        nel_write_file(PAGE_PATH . $write_id . '/' . $write_id . '-expand.html', $render->outputRenderSet('expand'), FILE_PERM, true);
        nel_write_file(PAGE_PATH . $write_id . '/' . $write_id . '-collapse.html', $render->outputRenderSet('collapse'), FILE_PERM, true);
    }
    else
    {
        if ($dataforce['expand'])
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

        die();
    }

    if($write)
    {
        nel_session_is_ignored('render', false);
    }
}
