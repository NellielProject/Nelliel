<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Handles language functions
//
function nel_get_language($language, $form, $text)
{
    static $lang_arrays;

    if (!isset($lang_arrays[$language]))
    {
        include LANGUAGE_PATH . 'lang.' . $language . '.php';
        $lang_arrays[$language]['singular'] = $lang_singular;
        $lang_arrays[$language]['plural'] = $lang_plural;
    }

    if (!isset($lang_arrays[$language][$form][$text]))
    {
        return '???';
    }

    return $lang_arrays[$language][$form][$text];
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

function nel_process_i18n($dom)
{
    $content_node_list = $dom->getElementsByAttributeName('data-i18n');
    $attribute_node_list = $dom->getElementsByAttributeName('data-i18n-attributes');

    foreach ($content_node_list as $node)
    {
        $node->removeAttribute('data-i18n');

        if (!$node->hasChildNodes())
        {
            continue;
        }

        $singular_elements = $dom->doXPathQuery('.//*[@data-singular]', $node);

        foreach ($singular_elements as $element)
        {
            $text = $element->getContent();
            $new_text = $dom->createTextNode(nel_stext($text), 'none');
            $element->parentNode->replaceChild($new_text, $element);
        }
    }

    foreach ($attribute_node_list as $node)
    {
        $attribute_list = $node->getAttribute('data-i18n-attributes');
        $attributes = explode(',', $attribute_list);
        $node->removeAttribute('data-i18n-attributes');

        foreach ($attributes as $attribute)
        {
            $attribute = trim($attribute);

            if (!$node->hasAttribute($attribute))
            {
                continue;
            }

            $text = $node->getAttribute($attribute);
            $node->setAttribute($attribute, nel_stext($text), 'none');
        }
    }
}