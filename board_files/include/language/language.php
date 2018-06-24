<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

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

function nel_extract_language($file)
{
    $extractor = new \Nelliel\LanguageExtractor();
    $file_handler = new \Nelliel\FileHandler();
    $file_handler->writeFile($file, $extractor->assemblePoString());
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
        if ($node->getAttribute('data-i18n') === 'gettext')
        {
            nel_process_gettext_attribute($node);
        }

        $node->removeAttribute('data-i18n-attributes');
    }

    foreach ($content_node_list as $node)
    {
        if ($node->getAttribute('data-i18n') === 'gettext')
        {
            nel_process_gettext_content($node);
        }

        $node->removeAttribute('data-i18n');
    }
}

function nel_process_gettext_attribute($node)
{
    $attribute_list = explode(',', $node->getAttribute('data-i18n-attributes'));
    $new_text = '';

    foreach ($attribute_list as $attribute_name)
    {
        $attribute_name = trim($attribute_name);
        $attribute_value = $node->getAttribute($attribute_name);
        $new_text = _gettext($attribute_value);
        $attribute_node = $node->ownerDocument->createAttribute($attribute_name);
        $attribute_node->value = $new_text;
        $node->setAttributeNode($attribute_node);
    }
}

function nel_process_gettext_content($node)
{
    $new_text = '';
    $text = $node->getContent();
    $new_text = _gettext($text);
    $node->setContent($new_text, 'replace');
}