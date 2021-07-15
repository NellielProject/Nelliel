<?php

declare(strict_types=1);

namespace Nelliel\Modules\Language;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Auth\AuthUser;
use Nelliel\Utility\CacheHandler;

class Language
{
    private static $gettext;
    private static $gettext_helpers;

    function __construct($gettext_instance = null)
    {
        if (!defined('LC_MESSAGES'))
        {
            define('LC_MESSAGES', 6);
        }

        if (!is_null($gettext_instance))
        {
            self::$gettext = $gettext_instance;
        }

        if (is_null(self::$gettext))
        {
            $gettext = new \SmallPHPGettext\SmallPHPGettext();
            self::$gettext = $gettext;
            $gettext->bindtextdomain('nelliel', NEL_DEFAULT_TEXTDOMAIN_BIND);
            $gettext->registerFunctions();
        }

        if (is_null(self::$gettext_helpers))
        {
            self::$gettext_helpers = new \SmallPHPGettext\Helpers();
        }
    }

    public function loadLanguage(string $locale, string $domain_id, int $category)
    {
        $category_string = self::$gettext_helpers->categoryToString($category);
        $file = self::$gettext->bindtextdomain($domain_id) . '/' . $locale . '/' . $category_string . '/' . $domain_id .
                '.po';
        $file_id = $locale . '/' . $category_string . '/' . $domain_id . '.po';
        $cache_file = 'language/' . $locale . '/' . $category_string . '/' . $domain_id . '_po.php';
        $cache_handler = new CacheHandler();
        $language_array = array();
        $loaded = false;
        $hash = '';

        if (file_exists($file))
        {
            $hash = md5_file($file);
        }

        if (NEL_USE_FILE_CACHE && $cache_handler->checkHash($file_id, $hash))
        {
            if (file_exists(NEL_CACHE_FILES_PATH . $cache_file))
            {
                include NEL_CACHE_FILES_PATH . $cache_file;
                $loaded = true;
            }
        }

        if (!$loaded)
        {
            $po_parser = new \SmallPHPGettext\ParsePo();
            $language_array = $po_parser->parseFile($file, $domain_id);

            if (NEL_USE_FILE_CACHE)
            {
                $cache_handler->updateHash($file_id, $hash);
                $cache_handler->writeArrayToFile('language_array', $language_array, $cache_file);
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
            nel_derp(660, _gettext('You are not allowed to extract the gettext strings.'));
        }

        $extractor = new \Nelliel\Modules\Language\LanguageExtractor($domain);
        $file_handler = nel_utilities()->fileHandler();
        $extracted = $extractor->assemblePoString($default_textdomain, $default_category);

        foreach ($extracted as $category_str => $domain_output)
        {
            foreach ($domain_output as $out_domain => $output)
            {
                $directory = NEL_LANGUAGES_FILES_PATH . 'extracted/' . date('Y-m-d_H-i-s') . '/' . $category_str;
                $file_handler->createDirectory($directory, NEL_DIRECTORY_PERM, true);
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