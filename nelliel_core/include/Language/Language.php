<?php
declare(strict_types = 1);

namespace Nelliel\Language;

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

    function __construct($gettext_instance = null)
    {
        if (!is_null($gettext_instance))
        {
            self::$gettext = $gettext_instance;
        }

        if (is_null(self::$gettext))
        {
            self::$gettext = new \SmallPHPGettext\SmallPHPGettext();
            self::$gettext->textdomain('nelliel');
            self::$gettext->bindtextdomain('nelliel', NEL_DEFAULT_TEXTDOMAIN_BIND);
            self::$gettext->registerFunctions();
        }
    }

    public function loadLanguage(string $locale, string $domain, string $category): bool
    {
        $file = '/' . $locale . '/' . $category . '/' . $domain . '.po';
        $full_path = self::$gettext->bindtextdomain($domain) . $file;
        $cache_file = 'language/' . $file . '.php';
        $cache_handler = new CacheHandler();
        $translation_array = array();
        $hash = '';

        if (file_exists($full_path))
        {
            $hash = md5_file($full_path);
        }

        if (NEL_USE_FILE_CACHE && $cache_handler->checkHash($file, $hash))
        {
            if (file_exists(NEL_CACHE_FILES_PATH . $cache_file))
            {
                include NEL_CACHE_FILES_PATH . $cache_file;
                return self::$gettext->addTranslationFromArray($translation_array, $domain, $category);
            }
        }

        $translation_array = self::$gettext->getTranslation($domain, $category);

        if (NEL_USE_FILE_CACHE)
        {
            $cache_handler->updateHash($file, $hash);
            $cache_handler->writeArrayToFile('translation_array', $translation_array, $cache_file);
        }

        return self::$gettext->translationLoaded($domain, $category);
    }

    public function extractLanguageStrings(Domain $domain, AuthUser $user, string $default_textdomain,
            string $default_category)
    {
        if (!$user->checkPermission($domain, 'perm_extract_gettext'))
        {
            nel_derp(660, _gettext('You are not allowed to extract the gettext strings.'));
        }

        $extractor = new \Nelliel\Language\LanguageExtractor($domain, self::$gettext);
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