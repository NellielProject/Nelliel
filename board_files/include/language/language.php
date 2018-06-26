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
    if (empty($file))
    {
        $file = LOCALE_PATH . DEFAULT_LOCALE . '/LC_MESSAGES/nelliel.po';
    }

    $language_array = array();
    $loaded = false;
    $file_id =
    $hash = md5_file($file);
    $cache_handler = new \Nelliel\CacheHandler();

    if ($cache_handler->checkHash($file, $hash) && USE_INTERNAL_CACHE)
    {
        if (file_exists(CACHE_PATH . 'language/' . DEFAULT_LOCALE . '/LC_MESSAGES/nelliel_po.php'))
        {
            include CACHE_PATH . 'language/' . DEFAULT_LOCALE . '/LC_MESSAGES/nelliel_po.php';
            $loaded = true;
        }
    }

    if (!$loaded)
    {
        $po_parser = new \SmallPHPGettext\ParsePo();
        $language_array = $po_parser->parseFile($file);

        if (USE_INTERNAL_CACHE)
        {
            $cache_handler->updateHash($file, $hash);
            $cache_handler->writeCacheFile(CACHE_PATH . 'language/' . DEFAULT_LOCALE . '/LC_MESSAGES/', 'nelliel_po.php', '$language_array = ' .
            var_export($language_array, true) . ';');
        }
    }

    $lang_lib = new \SmallPHPGettext\SmallPHPGettext();
    $lang_lib->addDomainFromArray($language_array);
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

function nel_process_i18n($dom, $language = 'en-us')
{
    if (empty($language))
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