<?php
declare(strict_types = 1);

namespace Nelliel\Language;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Auth\AuthUser;
use Nelliel\Domains\Domain;
use Nelliel\Utility\CacheHandler;
use SmallPHPGettext\SmallPHPGettext;

class Language
{
    private static $gettext;
    private const LC_MESSAGES = 6;

    function __construct($gettext_instance = null)
    {
        if (!is_null($gettext_instance)) {
            self::$gettext = $gettext_instance;
        }

        if (is_null(self::$gettext)) {
            self::$gettext = new SmallPHPGettext();
            self::$gettext->textdomain('nelliel');
            self::$gettext->bindtextdomain('nelliel', NEL_LOCALE_FILES_PATH);
            self::$gettext->defaultCategory(self::LC_MESSAGES);
            self::$gettext->language(self::LC_MESSAGES, NEL_DEFAULT_LOCALE);
            self::$gettext->registerFunctions();
        }
    }

    public function loadLanguage(string $language, string $domain, int $category): bool
    {
        if (self::$gettext->translationLoaded($domain, $category, false)) {
            return true;
        }

        $po_absolute_path = self::$gettext->getStandardPath($language, $domain, $category, false);

        if (NEL_USE_FILE_CACHE) {
            $locale_cache_path = 'locale/';
            $po_relative_path = self::$gettext->getStandardPath($language, $domain, $category, true);
            $cache_file = utf8_str_replace('.po', '.php', $po_relative_path);
            $cache_handler = new CacheHandler();
            $translation_array = array();
            $hash = '';

            if (file_exists($po_absolute_path)) {
                $hash = md5_file($po_absolute_path);
            }

            if ($cache_handler->checkHash($po_relative_path, $hash)) {
                $translation_array = $cache_handler->loadArrayFromFile('translation_array',
                    $locale_cache_path . $cache_file);

                if (!empty($translation_array)) {
                    return self::$gettext->loadTranslationFromArray($translation_array, $domain, $category);
                }
            }

            $loaded = self::$gettext->loadTranslation($domain, $category);
            $cache_handler->updateHash($po_relative_path, $hash);
            $cache_handler->writeArrayToFile('translation_array', self::$gettext->getTranslation($domain, $category),
                $locale_cache_path . $cache_file);
            return $loaded;
        }

        return self::$gettext->loadTranslationFromFile($po_absolute_path, $domain, $category);
    }

    public function extractLanguageStrings(Domain $domain, AuthUser $user, string $default_textdomain,
        int $default_category)
    {
        if (!$user->checkPermission($domain, 'perm_extract_gettext')) {
            nel_derp(510, _gettext('You are not allowed to extract the gettext strings.'), 403);
        }

        $extractor = new LanguageExtractor($domain, self::$gettext);
        $file_handler = nel_utilities()->fileHandler();
        $extracted = $extractor->assemblePoString($default_textdomain, $default_category);

        foreach ($extracted as $category_id => $domain_output) {
            foreach ($domain_output as $out_domain => $output) {
                $directory = NEL_LANGUAGES_FILES_PATH . 'extracted/' . date('Y-m-d_H-i-s') . '/' . 'LC_MESSAGES';
                $file_handler->createDirectory($directory);
                $file = $directory . '/' . $out_domain . '.pot';
                $file_handler->writeFile($file, $output);
            }
        }
    }

    public function changeLanguage(string $locale): void
    {
        self::$gettext->language(self::LC_MESSAGES, $locale);
    }

    public function accessGettext()
    {
        return self::$gettext;
    }
}