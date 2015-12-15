<?php

function get_language($language, $form, $text)
{
    static $lang_arrays;

    if (!isset($lang_arrays[$language]))
    {
        include INCLUDE_PATH . 'lang.' . $language . '.php';
        $lang_arrays[$language]['singular'] = $lang_singular;
        $lang_arrays[$language]['plural'] = $lang_plural;
    }
    
    return $lang_arrays[$language][$form][$text];
}

function stext($text)
{
    return get_language('en-us', 'singular', $text);
}

function ptext($text, $num)
{
    if ($num <= 1)
    {
        return get_language('en-us', 'singular', $text);
    }
    else if ($num > 1)
    {
        return get_language('en-us', 'plural', $text);
    }
}