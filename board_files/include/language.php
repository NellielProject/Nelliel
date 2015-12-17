<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_get_language($language, $form, $text)
{
    static $lang_arrays;

    if (!isset($lang_arrays[$language]))
    {
        include LANGUAGE_PATH . 'lang.' . $language . '.php';
        $lang_arrays[$language]['singular'] = $lang_singular;
        $lang_arrays[$language]['plural'] = $lang_plural;
    }
    
    return $lang_arrays[$language][$form][$text];
}

function stext($text)
{
    return nel_get_language(BOARD_LANGUAGE, 'singular', $text);
}

function ptext($text, $num)
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