<?php

namespace Nelliel\Language;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Language
{
    private $gettext;

    function __construct($gettext)
    {
        $this->gettext = $gettext;
    }

    public function loadLanguage($file = null)
    {
        if (empty($file))
        {
            $file = LOCALE_FILE_PATH . DEFAULT_LOCALE . '/LC_MESSAGES/nelliel.po';
        }

        if (!file_exists($file))
        {
            $file = LOCALE_FILE_PATH . 'en_US/LC_MESSAGES/nelliel.po';
        }

        $language_array = array();
        $loaded = false;
        $file_id = $hash = md5_file($file);
        $cache_handler = new \Nelliel\CacheHandler();

        if ($cache_handler->checkHash($file, $hash) && USE_INTERNAL_CACHE)
        {
            if (file_exists(CACHE_FILE_PATH . 'language/' . DEFAULT_LOCALE . '/LC_MESSAGES/nelliel_po.php'))
            {
                include CACHE_FILE_PATH . 'language/' . DEFAULT_LOCALE . '/LC_MESSAGES/nelliel_po.php';
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
                $cache_handler->writeCacheFile(CACHE_FILE_PATH . 'language/' . DEFAULT_LOCALE . '/LC_MESSAGES/', 'nelliel_po.php', '$language_array = ' .
                    var_export($language_array, true) . ';');
            }
        }

        $this->gettext->addDomainFromArray($language_array);
    }

    public function extractLanguageStrings($domain, $user, $file)
    {
        if (!$user->domainPermission($domain, 'perm_extract_gettext'))
        {
            nel_derp(390, _gettext('You are not allowed to extract the gettext strings.'));
        }

        $extractor = new \Nelliel\Language\LanguageExtractor();
        $file_handler = new \Nelliel\FileHandler();
        $file_handler->writeFile($file, $extractor->assemblePoString());
    }
}