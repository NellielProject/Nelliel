<?php

namespace Nelliel\Language;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\AuthUser;

class Language
{
    private static $gettext;
    private static $gettext_helpers;

    function __construct($gettext_instance = null)
    {
        if (!is_null($gettext_instance))
        {
            self::$gettext = $gettext_instance;
        }

        if (is_null(self::$gettext))
        {
            $gettext = new \SmallPHPGettext\SmallPHPGettext();
            self::$gettext = $gettext;
            $gettext->bindtextdomain('nelliel', DEFAULT_TEXTDOMAIN_BIND);
            $gettext->registerFunctions();
        }

        if (is_null(self::$gettext_helpers))
        {
            self::$gettext_helpers = new \SmallPHPGettext\Helpers();
        }
    }

    public function loadLanguage(string $locale, string $domain, int $category)
    {
        $category_string = self::$gettext_helpers->categoryToString($category);
        $file = self::$gettext->bindtextdomain($domain) . '/' . $locale . '/' . $category_string . '/' . $domain . '.po';
        $file_id = $locale . '/' . $category_string . '/' . $domain . '.po';
        $cache_file = 'language/' . $locale . '/' . $category_string . '/' . $domain . '_po.php';
        $cache_handler = new \Nelliel\CacheHandler();
        $language_array = array();
        $loaded = false;
        $hash = '';

        if (file_exists($file))
        {
            $hash = md5_file($file);
        }

        if (USE_INTERNAL_CACHE && $cache_handler->checkHash($file_id, $hash))
        {
            if (file_exists(CACHE_FILE_PATH . $cache_file))
            {
                include CACHE_FILE_PATH . $cache_file;
                $loaded = true;
            }
        }

        if (!$loaded)
        {
            $po_parser = new \SmallPHPGettext\ParsePo();
            $language_array = $po_parser->parseFile($file, $domain);

            if (USE_INTERNAL_CACHE)
            {
                $cache_handler->updateHash($file_id, $hash);
                $cache_handler->writeCacheFile(CACHE_FILE_PATH, $cache_file,
                        '$language_array = ' . var_export($language_array, true) . ';');
            }

            $loaded = true;
        }

        self::$gettext->addTranslationsFromArray($language_array, $category);
    }

    public function extractLanguageStrings(Domain $domain, AuthUser $user, string $default_textdomain,
            int $default_category)
    {
        if (!$user->checkPermission($domain, 'perm_extract_gettext'))
        {
            nel_derp(390, _gettext('You are not allowed to extract the gettext strings.'));
        }

        $extractor = new \Nelliel\Language\LanguageExtractor($domain);
        $file_handler = new \Nelliel\FileHandler();
        $extracted = $extractor->assemblePoString($default_textdomain, $default_category);

        foreach ($extracted as $category_str => $domain_output)
        {
            foreach ($domain_output as $out_domain => $output)
            {
                $directory = LANGUAGES_FILE_PATH . 'extracted/' . date('Y-m-d_H-i-s') . '/' . $category_str;
                $file_handler->createDirectory($directory, DIRECTORY_PERM, true);
                $file = $directory . '/' . $out_domain . '.pot';
                $file_handler->writeFile($file, $output);
            }
        }
    }

    public function accessGettext()
    {
        return self::$gettext;
    }
}