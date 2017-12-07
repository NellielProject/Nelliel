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

function nel_process_neltext($dom, $node)
{
    $plural = $dom->doXPathQuery('(.//*[@data-plural])[1]', $node)->item(0);

    if (!is_null($plural))
    {
        $text = $plural->getContent();
        $variable = $plural->getAttribute('data-plural');
        $new_text = nel_ptext($text, $$variable);
    }
    else
    {
        $text = $node->getContent();
        $new_text = nel_stext($text);
    }

    $node->setContent($new_text, 'replace', 'none');
    $node->removeAttribute('data-i18n');
}

function nel_process_i18n($dom)
{
    $content_node_list = $dom->getElementsByAttributeName('data-i18n');
    $attribute_node_list = $dom->getElementsByAttributeName('data-i18n-attributes');

    foreach ($content_node_list as $node)
    {
        if ($node->getAttribute('data-i18n') === 'neltext')
        {
            nel_process_neltext($dom, $node);
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
            $node->extSetAttribute($attribute, nel_stext($text));
            //$attr = $dom->createFullAttribute($attribute, nel_stext($text));
            //$node->appendChild($attr);
        }
    }
}