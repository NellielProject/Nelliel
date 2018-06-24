<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_rules_list($board_id)
{
    $board_settings = nel_board_settings($board_id);
    $render = new NellielTemplates\RenderCore();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'posting_rules.html');
    $rules_div = $dom->getElementById('rules-div');
    $rules_list_element = $dom->getElementsByClassName('rules-list')->item(0);
    $rules_item_element = $dom->getElementsByClassName('rules-item')->item(0);
    $filetype_rules = $rules_list_element->cloneNode();

    foreach (nel_filetype_settings($board_id) as $key => $value)
    {
        $current_list_item = $rules_item_element->cloneNode(true);
        $list_set = '';

        foreach ($value as $name => $setting)
        {
            if ($name == $key || $setting === false)
            {
                continue;
            }

            $list_set .= utf8_strtoupper($name) . ', ';
        }

        if ($list_set !== '')
        {
            $current_list_item->firstChild->setContent('FILES_' . utf8_strtoupper($key));
            $current_list_item->firstChild->nextSibling->setContent(substr($list_set, 0, -2));
            $filetype_rules->appendChild($current_list_item);
        }
    }

    $post_limits = $rules_list_element->cloneNode();
    $size_limit = $rules_item_element->cloneNode(true);
    $size_limit->firstChild->setContent('Maximum file size allowed is ');
    $size_limit->firstChild->nextSibling->setContent($board_settings['max_filesize'] . 'KB'); // TODO: Settable units
    $post_limits->appendChild($size_limit);
    $thumbnail_limit = $rules_item_element->cloneNode(true);
    $thumbnail_limit->firstChild->setContent('Images greater than ');
    $thumbnail_limit->firstChild->nextSibling->setContent($board_settings['max_width'] . ' x ' .
         $board_settings['max_height'] . nel_stext(' pixels will be thumbnailed.'));
    $post_limits->appendChild($thumbnail_limit);

    $rules_div->appendChild($filetype_rules);
    $rules_div->appendChild($post_limits);

    $rules_list_element->removeSelf();
    return $rules_div;
}