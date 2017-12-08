<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_stext($text)
{
    return nel_get_language(BOARD_LANGUAGE, 'singular', $text);
}

function nel_ptext($text, $num)
{
    if ($num <= 1)
    {
        return nel_get_language(BOARD_LANGUAGE, 'singular', $text);
    }
    else if ($num > 1)
    {
        return nel_get_language(BOARD_LANGUAGE, 'plural', $text);
    }
}

function nel_process_neltext_attribute($temp_dom, $attribute)
{
    $temp_dom->loadHTML($attribute->value);
    $final_text = '';

    foreach ($temp_dom->getElementsByTagName('i18n') as $element)
    {
        $plural_element = $element->getElementsByTagName('plural')->item(0);

        if (!is_null($plural_element))
        {
            $variable = $plural_element->getAttribute('var');
            $text = $plural_element->nodeValue;
            $final_text .= nel_ptext($text, $$variable);
        }
        else
        {
            $text = $element->nodeValue;
            $final_text .= nel_stext($text);
        }
    }

    $attribute->value = $final_text;
}

function nel_process_neltext_content($dom, $node)
{
    $plural_element = $node->getElementsByTagName('plural')->item(0);

    if (!is_null($plural_element))
    {
        $variable = $plural_element->getAttribute('var');
        $text = $plural_element->getContent();
        $new_text = nel_ptext($text, $$variable);
    }
    else
    {
        $text = $node->getContent();
        $new_text = nel_stext($text);
    }

    $node->setContent($new_text, 'replace');
}