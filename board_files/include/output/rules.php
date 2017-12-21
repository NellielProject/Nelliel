<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_rules_list($list)
{
    $render = new NellielTemplates\RenderCore();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'posting_rules.html');

    $rules_list_element = $dom->getElementsByClassName('rules-list')->item(0);
    $rules_item_element = $dom->getElementsByClassName('rules-item')->item(0);

    foreach ($list as $key => $value)
    {
        $current_list_item = $rules_item_element->cloneNode(true);
        $list_set = '';

        foreach($value as $name => $setting)
        {
            if($name == $key || $setting === false)
            {
                continue;
            }

            $list_set .= utf8_strtoupper($name) . ', ';
        }

        if($list_set !== '')
        {
            $current_list_item->firstChild->setContent('FILES_' . utf8_strtoupper($key));
            $current_list_item->firstChild->nextSibling->setContent(substr($list_set, 0, -2));
            $rules_list_element->appendChild($current_list_item);
        }
    }

    $rules_item_element->removeSelf();
    return $rules_list_element;
}