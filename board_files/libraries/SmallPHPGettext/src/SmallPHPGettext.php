<?php

namespace SmallPHPGettext;

class SmallPHPGettext
{
    private $domain_codesets = array();
    private $default_codeset = 'UTF-8';
    private $default_context;
    private $default_plural_rule = '$plurals=2;$plural=$n!=1';
    private $locale = 'en_US';
    private $default_category = 6; // LC_MESSAGES
    private $domain = 'messages';
    private $domain_directories = array();
    private $translations = array();
    private $version;
    private $helpers;

    function __construct()
    {
        $this->helpers = new Helpers();
        $this->domain_directories[$this->domain] = '';
        $this->domain_codesets[$this->domain] = $this->default_codeset;
        $category_str = $this->helpers->categoryToString($this->default_category);
        $this->translations[$category_str] = array();
        $this->translations[$category_str][$this->domain] = array();
        $this->version = '2.0';
    }

    public function registerFunctions()
    {
        include_once 'gettext_functions.php';
        access_small_php_gettext($this);
    }

    public function gettext(string $msgid)
    {
        return $this->singularMessage($msgid, $this->domain, $this->default_category, null);
    }

    public function ngettext(string $msgid1, string $msgid2, int $n)
    {
        return $this->pluralMessage($msgid1, $msgid2, $n, $this->domain, $this->default_category, null);
    }

    public function pgettext(string $context, string $msgid)
    {
        return $this->singularMessage($msgid, $this->domain, $this->default_category, $context);
    }

    public function npgettext(string $context, string $msgid1, string $msgid2, int $n)
    {
        return $this->pluralMessage($msgid1, $msgid2, $n, $this->domain, $this->default_category, $context);
    }

    public function dgettext(string $domain, string $msgid)
    {
        return $this->singularMessage($msgid, $domain, $this->default_category, null);
    }

    public function dngettext(string $domain, string $msgid1, string $msgid2, int $n)
    {
        return $this->pluralMessage($msgid1, $msgid2, $n, $domain, $this->default_category, null);
    }

    public function dcgettext(string $domain, string $msgid, int $category)
    {
        return $this->singularMessage($msgid, $domain, $category, null);
    }

    public function dcngettext(string $domain, string $msgid1, string $msgid2, int $n, int $category)
    {
        return $this->pluralMessage($msgid1, $msgid2, $n, $domain, $category, null);
    }

    public function addTranslationsFromArray(array $translations, int $category)
    {
        $category_str = $this->helpers->categoryToString($category);
        $this->translations[$category_str][$translations['domain']] = $translations;
        return isset($this->translations[$category_str][$translations['domain']]);
    }

    public function addTranslationsFromFile(string $file, int $category)
    {
        $po = new ParsePo();
        $translations = $po->parseFile($file);
        return $this->addTranslationsFromArray($translations, $category);
    }

    public function translationLoaded(string $domain, int $category)
    {
        $category_str = $this->helpers->categoryToString($category);
        return isset($this->translations[$category_str][$domain]) && !empty($this->translations[$category_str][$domain]);
    }

    public function getTranslations(string $domain, int $category)
    {
        $category_str = $this->helpers->categoryToString($category);
        return (isset($this->translations[$category_str][$domain])) ? $this->translations[$category_str][$domain] : null;
    }

    public function textdomain(string $domain = null)
    {
        if (!is_null($domain))
        {
            $this->domain = $domain;
        }

        return $this->domain;
    }

    public function bindtextdomain(string $domain, string $directory = null)
    {
        if (!is_null($directory))
        {
            $this->domain_directories[$domain] = $directory;
        }

        if (isset($this->domain_directories[$domain]))
        {
            return realpath($this->domain_directories[$domain]);
        }

        return $directory;
    }

    public function bind_textdomain_codeset(string $domain, string $codeset = null)
    {
        if (!is_null($codeset))
        {
            $this->domain_codesets[$domain] = $codeset;
        }

        return $this->domain_codesets[$domain];
    }

    public function locale(string $locale = null)
    {
        if (!is_null($locale))
        {
            $this->locale = $locale;
        }

        return $this->locale;
    }

    private function domainLoaded(string $domain, int $category, bool $load = true)
    {
        $category_str = $this->helpers->categoryToString($category);

        if (isset($this->translations[$category_str][$domain]))
        {
            return true;
        }
        else
        {
            if ($domain != $this->domain)
            {
                $this->textdomain($domain);
            }

            if ($load)
            {
                if (isset($this->domain_directories[$domain]) && file_exists($this->domain_directories[$domain]))
                {
                    $file = $this->domain_directories[$domain] . '/' . $this->locale . '/' . $category_str . '/' .
                            $domain . '.po';
                    return $this->addTranslationsFromFile($file, $category);
                }
            }

            return false;
        }
    }

    private function singularMessage(string $msgid, string $domain, int $category, string $context = null)
    {
        $po_msgid = $this->helpers->stringToPo($msgid);
        $category_str = $this->helpers->categoryToString($category);
        $valid = $this->domainLoaded($domain, $category, true);
        $message = '';

        if ($valid)
        {
            if (!is_null($context))
            {
                $message = $this->translations[$category_str][$domain]['translations'][$po_msgid]['contexts'][$context]['msgstr'] ?? '';
            }
            else
            {
                $message = $this->translations[$category_str][$domain]['translations'][$po_msgid]['msgstr'] ?? '';
            }
        }

        if ($message !== '')
        {
            return $this->helpers->poToString($message);
        }
        else
        {
            return $msgid;
        }
    }

    private function pluralMessage(string $msgid1, string $msgid2, int $n, string $domain, int $category,
            string $context = null)
    {
        $po_msgid1 = $this->helpers->stringToPo($msgid1);
        $category_str = $this->helpers->categoryToString($category);
        $valid = $this->domainLoaded($domain, $category, true);
        $message = '';

        if ($valid)
        {
            if (!is_null($context))
            {
                $translation = $this->translations[$category_str][$domain]['translations'][$po_msgid1]['contexts'][$context] ?? null;
            }
            else
            {
                $translation = $this->translations[$category_str][$domain]['translations'][$po_msgid1] ?? null;
            }

            if (!is_null($translation))
            {
                $plural_rule = $this->translations[$category_str][$domain]['plural_rule'] ?? $this->default_plural_rule;
                eval($plural_rule);
                $message = $translation['plurals'][$plural] ?? '';
            }
        }

        if ($message !== '')
        {
            return $this->helpers->poToString($message);
        }
        else
        {
            return ($n === 1) ? $msgid1 : $msgid2;
        }
    }
}