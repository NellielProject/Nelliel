<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_rules_list($board_id)
{
    $board_settings = nel_parameters_and_data()->boardSettings($board_id);
    $render = new NellielTemplates\RenderCore();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'posting_rules.html');
    $rules_div = $dom->getElementById('rules-div');
    $rules_list_element = $dom->getElementsByClassName('rules-list')->item(0);
    $rules_item_element = $dom->getElementsByClassName('rules-item')->item(0);
    $filetype_rules = $dom->copyNode($rules_list_element, $rules_div, 'append');

    foreach (nel_parameters_and_data()->filetypeSettings($board_id) as $key => $value)
    {
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
            $current_list_item = $dom->copyNode($rules_item_element, $filetype_rules, 'append');
            $current_list_item->setContent(
                    sprintf(_gettext('Supported %s file types: '), $key) . substr($list_set, 0, -2));
        }
    }

    $filetype_rules->getElementsByClassName('rules-item')->item(0)->remove();
    $rules_list_element->remove();
    $post_limits = $dom->copyNode($rules_list_element, $rules_div, 'append');
    $post_limits->getElementsByClassName('rules-item')->item(0)->remove();
    $size_limit = $dom->copyNode($rules_item_element, $post_limits, 'append');
    $size_limit->setContent(sprintf(_gettext('Maximum file size allowed is %dKB'), $board_settings['max_filesize']));
    $thumbnail_limit = $dom->copyNode($rules_item_element, $post_limits, 'append');
    $thumbnail_limit->setContent(
            sprintf(_gettext('Images greater than %d x %d pixels will be thumbnailed.'), $board_settings['max_width'],
                    $board_settings['max_height']));
    return $rules_div;
}