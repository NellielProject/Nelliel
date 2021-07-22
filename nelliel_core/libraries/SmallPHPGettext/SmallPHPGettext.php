<?php
declare(strict_types = 1);

namespace SmallPHPGettext;

class SmallPHPGettext
{
    use Helpers;
    private $domain_codesets = array();
    private $default_codeset = 'UTF-8';
    private $default_context;
    private $default_locale_directory = '';
    private $default_plural_rule = '$plurals=2;$plural=$n!=1';
    private $locale = 'en_US';
    private $default_category = 'LC_MESSAGES';
    private $default_domain = 'messages';
    private $domain;
    private $domain_directories = array();
    private $translations = array();
    private $version = '2.1';

    function __construct()
    {
        $this->domain = $this->default_domain;
        $this->domain_directories[$this->domain] = '';
        $this->domain_codesets[$this->domain] = $this->default_codeset;
        $this->translations[$this->default_category] = array();
        $this->translations[$this->default_category][$this->domain] = array();
    }

    /**
     * Defines a set of globally available functions to do translation.
     */
    public function registerFunctions()
    {
        include_once 'gettext_functions.php';
        access_small_php_gettext($this);
    }

    public function gettext(string $msgid): string
    {
        return $this->singularMessage($msgid, $this->domain, $this->default_category, null);
    }

    public function ngettext(string $msgid1, string $msgid2, int $n): string
    {
        return $this->pluralMessage($msgid1, $msgid2, $n, $this->domain, $this->default_category, null);
    }

    public function pgettext(string $context, string $msgid): string
    {
        return $this->singularMessage($msgid, $this->domain, $this->default_category, $context);
    }

    public function npgettext(string $context, string $msgid1, string $msgid2, int $n): string
    {
        return $this->pluralMessage($msgid1, $msgid2, $n, $this->domain, $this->default_category, $context);
    }

    public function dgettext(string $domain, string $msgid): string
    {
        return $this->singularMessage($msgid, $domain, $this->default_category, null);
    }

    public function dngettext(string $domain, string $msgid1, string $msgid2, int $n): string
    {
        return $this->pluralMessage($msgid1, $msgid2, $n, $domain, $this->default_category, null);
    }

    public function dcgettext(string $domain, string $msgid, int $category): string
    {
        return $this->singularMessage($msgid, $domain, $category, null);
    }

    public function dcngettext(string $domain, string $msgid1, string $msgid2, int $n, int $category): string
    {
        return $this->pluralMessage($msgid1, $msgid2, $n, $domain, $category, null);
    }

    /**
     * Gets or sets the current message domain.
     *
     * @param string [optional] $domain The message domain.
     * @return string The currently set domain.
     */
    public function textdomain(string $domain = null): string
    {
        if (!is_null($domain))
        {
            $this->domain = $domain;
        }

        return $this->domain;
    }

    /**
     * Binds a directory to the specified message domain.
     *
     * @param string $domain The message domain.
     * @param string [optional] $directory The directory to be bound to the domain. If null, returns the current directory.
     * @return string The currently set domain directory or the default locale directory if a directory has never been set.
     */
    public function bindtextdomain(string $domain, string $directory = null): string
    {
        if (!is_null($directory))
        {
            $this->domain_directories[$domain] = $directory;
        }

        if (isset($this->domain_directories[$domain]))
        {
            return $this->domain_directories[$domain];
        }
        else
        {
            return $this->default_locale_directory;
        }
    }

    /**
     * Gets or sets the encoding for the specified domain.
     *
     * @param string $domain The message domain.
     * @param string [optional] $codeset The encoding to use for the domain. If null, returns the current encoding.
     * @return string The encoding currently set for the domain.
     */
    public function bind_textdomain_codeset(string $domain, string $codeset = null)
    {
        if (!is_null($codeset))
        {
            $this->domain_codesets[$domain] = $codeset;
        }

        return $this->domain_codesets[$domain] ?? $this->domain_codesets[$this->domain];
    }

    /**
     * Gets or sets the locale.
     *
     * @param string [optional] $locale The locale to set. If null, returns the current locale.
     * @return string The locale currently being used.
     */
    public function locale(string $locale = null): string
    {
        if (!is_null($locale))
        {
            $this->locale = $locale;
        }

        return $this->locale;
    }

    /**
     * Stores a set of translations from the given array for the specified category.
     *
     * @param array $translations Array of translations.
     * @param int $category The category for the translations.
     * @return bool True if sucessfully stored, false if something went wrong.
     */
    public function addTranslationFromArray(array $translations, string $domain, string $category): bool
    {
        // Re-parse to be safe since this method can accept data from outside
        $translations['plural_rule'] = $this->parsePluralRule($translations['headers']['Plural-Forms'] ?? '');
        $this->translations[$category][$domain] = $translations;
        return isset($this->translations[$category][$domain]);
    }

    /**
     * Stores a set of translations from the given .po file for the specified category.
     *
     * @param string $file Path to the file.
     * @param int $category The category for the translations.
     * @return bool True if sucessfully stored, false if something went wrong.
     */
    public function addTranslationFromFile(string $file, string $domain, string $category): bool
    {
        $po = new ParsePo();
        $translations = $po->parseFile($file, $domain);
        return $this->addTranslationFromArray($translations, $domain, $category);
    }

    /**
     * Checks if a translation is loaded for the given domain and category.
     *
     * @param string $domain The domain to check.
     * @param int $category The category to check.
     * @return bool True if loaded, false if not.
     */
    public function translationLoaded(string $domain, string $category, bool $load = false): bool
    {
        $loaded = isset($this->translations[$category][$domain]) && is_array($this->translations[$category][$domain]);

        if ($load && !$loaded)
        {
            return $this->loadTranslation($domain, $category);
        }

        return $loaded;
    }

    /**
     * Gets the translation stored for the given domain and category.
     *
     * @param string $domain The domain to check.
     * @param int $category The category to check.
     * @return array The translation array. If nothing has been loaded, will return an empty array.
     */
    public function getTranslation(string $domain, string $category): array
    {
        if (!$this->translationLoaded($domain, $category))
        {
            $this->loadTranslation($domain, $category);
        }

        if (isset($this->translations[$category][$domain]) && is_array($this->translations[$category][$domain]))
        {
            return $this->translations[$category][$domain];
        }

        return array();
    }

    private function loadTranslation(string $domain, string $category): bool
    {
        if (isset($this->domain_directories[$domain]) && file_exists($this->domain_directories[$domain]))
        {
            $file = $this->domain_directories[$domain] . '/' . $this->locale . '/' . $category . '/' . $domain . '.po';
            return $this->addTranslationFromFile($file, $domain, $category);
        }

        return false;
    }

    private function singularMessage(string $msgid, string $domain, string $category, string $context = null): string
    {
        $po_msgid = $this->poEncode($msgid);
        $valid = $this->translationLoaded($domain, $category, true);
        $message = '';

        if ($valid)
        {
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

    private function pluralMessage(string $msgid1, string $msgid2, int $n, string $domain, string $category,
            string $context = null): string
    {
        $po_msgid1 = $this->poEncode($msgid1);
        $valid = $this->translationLoaded($domain, $category, true);
        $message = '';

        if ($valid)
        {
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