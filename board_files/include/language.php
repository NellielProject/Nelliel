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


    if(!isset($lang_arrays[$language][$form][$text]))
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
    $node_list = $dom->getElementsByAttributeName('data-i18n');

    foreach($node_list as $node)
    {
        if(!$node->hasChildNodes())
        {
            continue;
        }

        $singular_element = $dom->doXPathQuery('.//*[@data-singular]', $node)->item(0);

        if(!is_null($singular_element))
        {
            $text = $singular_element->getContent();
            $singular_element->setContent(nel_stext($text));
        }
    }
}