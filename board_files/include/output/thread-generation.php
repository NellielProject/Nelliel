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
        nel_session_set_ignored('render', true);
    }

    $dataforce['dotdot'] = '../../';
    $render = new NellielTemplates\RenderCore();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'thread.html');
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
            nel_write_file(PAGE_PATH . $write_id . '/' . $write_id. '-0-100.html', $render_temp->outputRenderSet(), FILE_PERM);
            unset($render_temp);
        }

        if ($gen_data['post']['op'] == 1)
        {
            $new_post_element = nel_render_post($dataforce, $render, FALSE, FALSE, $gen_data, $treeline, $dom);
        }
        else
        {
            $new_post_element = nel_render_post($dataforce, $render, TRUE, FALSE, $gen_data, $treeline, $dom);
            $partial_post_element = nel_render_post($dataforce, $render, TRUE, TRUE, $gen_data, $treeline, $dom);

            if ($gen_data['thread']['post_count'] > BS_ABBREVIATE_THREAD)
            {
                if ($gen_data['post_counter'] > $gen_data['thread']['post_count'] - BS_ABBREVIATE_THREAD)
                {
                    $import_node = $collapse_dom->importNode($partial_post_element, true);
                    $collapse_dom->appendChild($import_node);
                }
            }

            $import_node = $expand_dom->importNode($partial_post_element, true);
            $expand_dom->appendChild($import_node);
        }

        $dom->getElementById('outer-div')->appendChild($new_post_element);
        ++ $gen_data['post_counter'];
    }

    $dom->getElementById('post-id-')->removeSelf();
    $render->appendHTMLFromDOM($dom);
    $render->appendHTMLFromDOM($collapse_dom, 'collapse');
    $render->appendHTMLFromDOM($expand_dom, 'expand');
    nel_render_insert_hr($dom);
    nel_render_footer($render, true);

    if ($write)
    {
        nel_write_file(PAGE_PATH . $write_id . '/' . $write_id . '.html', $render->outputRenderSet(), FILE_PERM);
        nel_write_file(PAGE_PATH . $write_id . '/' . $write_id . '-expand.html', $render->outputRenderSet('expand'), FILE_PERM);
        nel_write_file(PAGE_PATH . $write_id . '/' . $write_id . '-collapse.html', $render->outputRenderSet('collapse'), FILE_PERM);
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
        nel_session_set_ignored('render', false);
    }
}
