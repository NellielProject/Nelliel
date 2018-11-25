<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_rules_list($board_id)
{
    $board_settings = nel_parameters_and_data()->boardSettings($board_id);
    $filetypes = new \Nelliel\FileTypes(nel_database());
    $render = new NellielTemplates\RenderCore();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'posting_rules.html');
    $form_rules_list = $dom->getElementById('form-rules-list');
    $rules_nodes = $form_rules_list->getElementsByAttributeName('data-parse-id', true);
    $base_list_item = $dom->createElement('li');
    $base_list_item->setAttributeNode($dom->createFullAttribute('class', 'rules-item'));
    $filetype_rules = $dom->copyNode($rules_nodes['rules-list'], $form_rules_list, 'append');

    foreach ($filetypes->filetypeSettings($board_id) as $type => $formats)
    {
        if(!$filetypes->typeIsEnabled($board_id, $type))
        {
            continue;
        }

        $list_set = '';

        foreach ($formats as $name => $setting)
        {
            if ($name == $type || $setting === false)
            {
                continue;
            }

            $list_set .= utf8_strtoupper($name) . ', ';
        }

        if ($list_set !== '')
        {
            $current_list_item = $dom->copyNode($base_list_item, $filetype_rules, 'append');
            $current_list_item->setContent(
                    sprintf(_gettext('Supported %s file types: '), $type) . substr($list_set, 0, -2));
            $filetype_rules->appendChild($current_list_item);
        }
    }

    $post_limits = $dom->copyNode($rules_nodes['rules-list'], $form_rules_list, 'append');
    $size_limit = $dom->copyNode($base_list_item, $post_limits, 'append');
    $size_limit->setContent(sprintf(_gettext('Maximum file size allowed is %dKB'), $board_settings['max_filesize']));
    $thumbnail_limit = $dom->copyNode($base_list_item, $post_limits, 'append');
    $thumbnail_limit->setContent(
            sprintf(_gettext('Images greater than %d x %d pixels will be thumbnailed.'), $board_settings['max_width'],
                    $board_settings['max_height']));
    $rules_nodes['rules-list']->remove();
    return $form_rules_list;
}