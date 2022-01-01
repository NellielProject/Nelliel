<?php
declare(strict_types = 1);

namespace SmallPHPGettext;

class SmallPHPGettext
{
    use Helpers;
    private $default_directory;
    private $default_plural_rule = '$plurals=2;$plural=$n!=1';
    private $translations = array();
    private $version = '3.0';
    private $default_domain = 'messages';
    private $domain_directories = array();
    private $default_category;
    private $category_to_string = [0 => 'LC_ALL', 1 => 'LC_COLLATE', 2 => 'LC_CTYPE', 3 => 'LC_MONETARY',
        4 => 'LC_NUMERIC', 5 => 'LC_TIME', 6 => 'LC_MESSAGES'];
    private $default_language = 'en_US';
    private $languages = array();

    function __construct()
    {
        $this->default_directory = realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . '/locale';
        // LC_MESSAGES may be used to test for the presence of libintl so we won't define it
        $this->default_category = defined('LC_MESSAGES') ? LC_MESSAGES : 6;
        $this->languages = [LC_ALL => $this->default_language, LC_COLLATE => $this->default_language,
            LC_CTYPE => $this->default_language, LC_MONETARY => $this->default_language,
            LC_NUMERIC => $this->default_language, LC_TIME => $this->default_language, 6 => $this->default_language];
    }

    /**
     * Defines a set of globally available alias functions to do translation.
     */
    public function registerFunctions(): void
    {
        include_once 'gettext_functions.php';
        access_small_php_gettext($this);
    }

    public function gettext(string $msgid): string
    {
        return $this->singularMessage($msgid, $this->default_domain, $this->default_category);
    }

    public function ngettext(string $msgid1, string $msgid2, int $n): string
    {
        return $this->pluralMessage($msgid1, $msgid2, $n, $this->default_domain, $this->default_category);
    }

    public function pgettext(string $context, string $msgid): string
    {
        return $this->singularMessage($msgid, $this->default_domain, $this->default_category, $context);
    }

    public function npgettext(string $context, string $msgid1, string $msgid2, int $n): string
    {
        return $this->pluralMessage($msgid1, $msgid2, $n, $this->default_domain, $this->default_category, $context);
    }

    public function dgettext(string $domain, string $msgid): string
    {
        return $this->singularMessage($msgid, $domain, $this->default_category);
    }

    public function dngettext(string $domain, string $msgid1, string $msgid2, int $n): string
    {
        return $this->pluralMessage($msgid1, $msgid2, $n, $domain, $this->default_category);
    }

    public function dcgettext(string $domain, string $msgid, int $category): string
    {
        return $this->singularMessage($msgid, $domain, $category);
    }

    public function dcngettext(string $domain, string $msgid1, string $msgid2, int $n, int $category): string
    {
        return $this->pluralMessage($msgid1, $msgid2, $n, $domain, $category);
    }

    /**
     * Gets the current message domain after optionally setting it.
     *
     * @param string $domain [optional] The domain to set. If null, only returns currently set domain.
     * @return string The currently set message domain.
     */
    public function textdomain(string $domain = null): string
    {
        if (!is_null($domain))
        {
            $this->default_domain = $domain;
        }

        return $this->default_domain;
    }

    /**
     * Gets the current base directory for a message domain after optionally setting it.
     *
     * @param string $domain The message domain.
     * @param string $directory [optional] The base directory to be bound to the domain. If null, only returns the current directory.
     * @return string The currently set base directory or empty string if none is set.
     */
    public function bindtextdomain(string $domain, string $directory = null): string
    {
        if (!is_null($directory))
        {
            $this->domain_directories[$domain] = $directory;
        }

        return $this->domain_directories[$domain] ?? '';
    }

    /**
     * Gets the current language for the category after optionally setting it.
     *
     * @param int $category The category to use.
     * @param string $language [optional] The language to set. If null, only returns the current language.
     * @return string The language currently being used or empty string if not set.
     */
    public function language(int $category, string $language = null): string
    {
        if (!is_null($language))
        {
            if ($category === LC_ALL)
            {
                for ($i = 1; $i <= 6; $i ++)
                {
                    $this->languages[$i] = $language;
                }
            }
            else
            {
                $this->languages[$category] = $language;
            }
        }

        return $this->languages[$category] ?? '';
    }

    /**
     * Gets the current default category after optionally setting it.
     *
     * @param int $category [optional] The category to set as default. If null, only returns currently set category.
     * @return int The currently set default category.
     */
    public function defaultCategory(int $category = null): int
    {
        if (!is_null($category))
        {
            $this->default_category = $category;
        }

        return $this->default_category;
    }

    /**
     * Loads a translation from a file at the standard location.
     *
     * @param string $domain The domain to load for.
     * @param int $category [optional] The category to use. Uses default if not specified.
     * @return bool True if successful, false if file does not exist or there were other problems.
     */
    public function loadTranslation(string $domain, int $category = null): bool
    {
        $category = $category ?? $this->default_category;
        $file = $this->getStandardPath($this->languages[$category], $domain, $category, false);
        return $this->loadTranslationFromFile($file, $domain, $category);
    }

    /**
     * Loads a set of translations from the given array.
     *
     * @param array $translation Array of translations.
     * @param string $domain The domain to load for.
     * @param int $category [optional] The category to use. Uses default if not specified.
     * @return bool True if sucessful, false if there were problems.
     */
    public function loadTranslationFromArray(array $translation, string $domain, int $category = null): bool
    {
        $category = $category ?? $this->default_category;
        // Parse the plural rule here instead of trusting an outside version
        $translation['plural_rule'] = $this->parsePluralRule($translation['headers']['Plural-Forms'] ?? '');
        $this->translations[$category][$domain] = $translation;
        return true;
    }

    /**
     * Loads a translation from a specific .po file.
     *
     * @param string $file Path to the file.
     * @param string $domain The domain to use.
     * @param int $category [optional] The category to use. Uses default if not specified.
     * @return bool True if successful, false if file does not exist or there were other problems.
     */
    public function loadTranslationFromFile(string $file, string $domain, int $category = null): bool
    {
        if (!file_exists($file))
        {
            return false;
        }

        $category = $category ?? $this->default_category;
        $po = new ParsePo();
        $translation = $po->parseFile($file, $domain);
        return $this->loadTranslationFromArray($translation, $domain, $category);
    }

    /**
     * Checks if a translation is loaded for the given domain and category.
     *
     * @param string $domain The domain to check.
     * @param int $category [optional] The category to check. Uses default if not specified.
     * @return bool True if loaded, false if not.
     */
    public function translationLoaded(string $domain, int $category = null): bool
    {
        $category = $category ?? $this->default_category;
        return isset($this->translations[$category][$domain]) && is_array($this->translations[$category][$domain]);
    }

    /**
     * Gets the translation stored for the given domain and category.
     *
     * @param string $domain The domain to check.
     * @param int $category [optional] The category to check. Uses default if not specified.
     * @return array The translation array. If nothing available, will return an empty array.
     */
    public function getTranslation(string $domain, int $category = null): array
    {
        $category = $category ?? $this->default_category;
        return $this->translations[$category][$domain] ?? array();
    }

    /**
     * Get the standard path for a Po file based on the parameters.
     *
     * @param string $language The locale
     * @param string $domain The domain to use.
     * @param int $category The category to use.
     * @param bool $relative True returns the relative path, false returns absolute path.
     * @return string The file path.
     */
    public function getStandardPath(string $language, string $domain, int $category, bool $relative): string
    {
        $base_directory = '';

        if (!$relative)
        {
            $base_directory = $this->domain_directories[$domain] ?? $this->default_directory;
        }

        return $base_directory . '/' . $language . '/' . $this->category_to_string[$category] . '/' . $domain . '.po';
    }

    private function singularMessage(string $msgid, string $domain, int $category, string $context = null): string
    {
        $message = '';
        $valid = $this->translationLoaded($domain, $category);

        if (!$valid)
        {
            $valid = $this->loadTranslation($domain, $category);
        }

        if ($valid)
        {
            $po_msgid = $this->poEncode($msgid);

            if (!is_null($context))
            {
                $message = $this->translations[$category][$domain]['translations'][$po_msgid]['contexts'][$context]['msgstr'] ?? '';
            }
            else
            {
                $message = $this->translations[$category][$domain]['translations'][$po_msgid]['msgstr'] ?? '';
            }
        }

        if ($message !== '')
        {
            return $this->poDecode($message);
        }
        else
        {
            return $msgid;
        }
    }

    private function pluralMessage(string $msgid1, string $msgid2, int $n, string $domain, int $category,
            string $context = null): string
    {
        $message = '';
        $valid = $this->translationLoaded($domain, $category);

        if (!$valid)
        {
            $valid = $this->loadTranslation($domain, $category);
        }

        if ($valid)
        {
            $po_msgid1 = $this->poEncode($msgid1);

            if (!is_null($context))
            {
                $translation = $this->translations[$category][$domain]['translations'][$po_msgid1]['contexts'][$context] ?? null;
            }
            else
            {
                $translation = $this->translations[$category][$domain]['translations'][$po_msgid1] ?? null;
            }

            if (!is_null($translation))
            {
                $plural_rule = $this->translations[$category][$domain]['plural_rule'] ?? $this->default_plural_rule;
                $index = eval($plural_rule);
                $message = $translation['plurals'][$index] ?? '';
            }
        }

        if ($message !== '')
        {
            return $this->poDecode($message);
        }
        else
        {
            return ($n === 1) ? $msgid1 : $msgid2;
        }
    }
}