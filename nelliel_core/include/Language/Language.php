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
            self::$gettext->bindtextdomain('nelliel', NEL_LANGUAGES_FILES_PATH);
            self::$gettext->registerFunctions();
        }
    }

    public function loadLanguage(string $locale, string $domain, int $category): bool
    {
        if (self::$gettext->translationLoaded($domain, $category, false))
        {
            return true;
        }

        $po_absolute_path = self::$gettext->getStandardPath($locale, $domain, $category, false);

        if (NEL_USE_FILE_CACHE)
        {
            $po_relative_path = self::$gettext->getStandardPath($locale, $domain, $category, true);
            $cache_file = str_replace('.po', '.php', $po_relative_path);
            $cache_handler = new CacheHandler();
            $translation_array = array();
            $hash = '';

            if (file_exists($po_absolute_path))
            {
                $hash = md5_file($po_absolute_path);
            }

            if ($cache_handler->checkHash($po_relative_path, $hash) && file_exists(NEL_CACHE_FILES_PATH . $cache_file))
            {
                include NEL_CACHE_FILES_PATH . $cache_file;
                return self::$gettext->addTranslationFromArray($translation_array, $domain, $category);
            }
            else
            {
                // No valid Po file or cache file to work with
                if ($hash === '')
                {
                    return false;
                }

                $translation_array = self::$gettext->getTranslationFromFile($po_absolute_path, $domain, $category);
                $cache_handler->updateHash($po_relative_path, $hash);
                $cache_handler->writeArrayToFile('translation_array', $translation_array, $cache_file);
                return self::$gettext->addTranslationFromArray($translation_array, $domain, $category);
            }
        }

        return self::$gettext->addTranslationFromFile($po_absolute_path, $domain, $category);
    }

    public function extractLanguageStrings(Domain $domain, AuthUser $user, string $default_textdomain,
            int $default_category)
    {
        if (!$user->checkPermission($domain, 'perm_extract_gettext'))
        {
            nel_derp(660, _gettext('You are not allowed to extract the gettext strings.'));
        }

        $extractor = new \Nelliel\Language\LanguageExtractor($domain, self::$gettext);
        $file_handler = nel_utilities()->fileHandler();
        $extracted = $extractor->assemblePoString($default_textdomain, $default_category);

        foreach ($extracted as $category_id => $domain_output)
        {
            foreach ($domain_output as $out_domain => $output)
            {
                $directory = NEL_LANGUAGES_FILES_PATH . 'extracted/' . date('Y-m-d_H-i-s') . '/' .
                        self::$gettext->categoryToString($category_id);
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