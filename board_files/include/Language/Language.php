<?php

namespace Nelliel\Language;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Language
{
    private $authorization;

    function __construct($authorization)
    {
        $this->authorization = $authorization;
    }

    public function loadLanguage($file = null)
    {
        $gettext = new \SmallPHPGettext\SmallPHPGettext();

        if (empty($file))
        {
            $file = LOCALE_PATH . DEFAULT_LOCALE . '/LC_MESSAGES/nelliel.po';
        }

        $language_array = array();
        $loaded = false;
        $file_id = $hash = md5_file($file);
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

        $gettext->addDomainFromArray($language_array);
        $gettext->registerFunctions();
    }

    public function extractLanguageStrings($file)
    {
        $session = new \Nelliel\Sessions($authorization);
        $user = $session->sessionUser();

        if (!$user->boardPerm('', 'perm_extract_gettext'))
        {
            nel_derp(390, _gettext('You are not allowed to extract the gettext strings.'));
        }

        $extractor = new \Nelliel\Language\LanguageExtractor();
        $file_handler = new \Nelliel\FileHandler();
        $file_handler->writeFile($file, $extractor->assemblePoString());
    }

    public function i18nDom($dom, $language = 'en_US')
    {
        // TODO: Access domain when $language is passed
        $content_node_list = $dom->getElementsByAttributeName('data-i18n');
        $attribute_node_list = $dom->getElementsByAttributeName('data-i18n-attributes');

        foreach ($attribute_node_list as $node)
        {
            if ($node->getAttribute('data-i18n') === 'gettext')
            {
                $this->gettextAttribute($node);
            }

            $node->removeAttribute('data-i18n-attributes');
        }

        foreach ($content_node_list as $node)
        {
            if ($node->getAttribute('data-i18n') === 'gettext')
            {
                $this->gettextContent($node);
            }

            $node->removeAttribute('data-i18n');
        }
    }

    private function gettextAttribute($node)
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

    private function gettextContent($node)
    {
        $new_text = '';
        $text = $node->getContent();
        $new_text = _gettext($text);
        $node->setContent($new_text);
    }
}