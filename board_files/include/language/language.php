<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'language/neltext.php';

//
// Handles language functions
//

function nel_load_language_library($file = null)
{
    if(empty($file))
    {
        $file = LOCALE_PATH . DEFAULT_LOCALE . '/LC_MESSAGES/nelliel.po';
    }

    $lang_lib = new \SmallPHPGettext\SmallPHPGettext();
    $lang_lib->addDomainFromFile($file);
    $lang_lib->registerFunctions();
    nel_get_language_instance($lang_lib);
}

function nel_get_language_instance($instance = null)
{
    static $language_instance;

    if (!empty($sessions))
    {
        $language_instance = $instance;
    }

    return $language_instance;
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

    if (!isset($lang_arrays[$language][$form][$text]))
    {
        return '???';
    }

    return $lang_arrays[$language][$form][$text];
}

function nel_process_i18n($dom, $language = 'en-us')
{
    if(empty($language))
    {
        $language = 'en-us';
    }

    $content_node_list = $dom->getElementsByAttributeName('data-i18n');
    $attribute_node_list = $dom->getElementsByAttributeName('data-i18n-attributes');

    foreach ($attribute_node_list as $node)
    {
        if ($node->getAttribute('data-i18n') === 'neltext')
        {
            nel_process_neltext_attribute($language, $node);
        }

        $node->removeAttribute('data-i18n-attributes');
    }

    foreach ($content_node_list as $node)
    {
        if ($node->getAttribute('data-i18n') === 'neltext')
        {
            nel_process_neltext_content($language, $node);
        }

        $node->removeAttribute('data-i18n');
    }
}