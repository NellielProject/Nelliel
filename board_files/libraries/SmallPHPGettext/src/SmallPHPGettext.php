<?php

namespace SmallPHPGettext;

class SmallPHPGettext
{
    private $charset = 'UTF-8';
    private $default_category = 'LC_MESSAGES';
    private $default_domain = 'messages';
    private $default_context = 'default';
    private $categories = array();
    private $version;

    function __construct()
    {
        $this->categories[$this->default_category] = array();
        $this->categories[$this->default_category][$this->default_domain] = array();
        $this->version = '1.0.3';
    }

    public function registerFunctions()
    {
        include_once __DIR__ . '/gettext_functions.php';
        access_small_php_gettext($this);
    }

    public function gettext($msgid)
    {
        return $this->singularMessage($msgid, 'default', $this->default_domain, $this->default_category);
    }

    public function ngettext($msgid1, $msgid2, $n)
    {
        return $this->pluralMessage($msgid1, $msgid2, $n, 'default', $this->default_domain, $this->default_category);
    }

    public function pgettext($context, $msgid)
    {
        return $this->singularMessage($msgid, $context, $this->default_domain, $this->default_category);
    }

    public function npgettext($context, $msgid1, $msgid2, $n)
    {
        return $this->pluralMessage($msgid1, $msgid2, $n, $context, $this->default_domain, $this->default_category);
    }

    public function dgettext($domain, $msgid)
    {
        return $this->singularMessage($msgid, 'default', $domain, $this->default_category);
    }

    public function dngettext($domain, $msgid1, $msgid2, $n)
    {
        return $this->pluralMessage($msgid1, $msgid2, $n, 'default', $domain, $this->default_category);
    }

    public function dcgettext($domain, $msgid, $category)
    {
        return $this->singularMessage($msgid, 'default', $domain, $category);
    }

    public function dcngettext($domain, $msgid1, $msgid2, $n, $category)
    {
        return $this->pluralMessage($msgid1, $msgid2, $n, 'default', $domain, $category);
    }

    public function addDomainFromArray($domain, $category = null)
    {
        $category = (!empty($category)) ? $category : $this->default_category;
        $this->categories[$category][$domain['name']] = $domain;
    }

    public function addDomainFromFile($file, $category = null)
    {
        $po = new ParsePo();
        $domain = $po->parseFile($file);
        $this->addDomainFromArray($domain, $category);
    }

    public function getDomain($domain, $category = null)
    {
        $category = (!empty($category)) ? $category : $this->default_category;
        return (isset($this->categories[$category][$domain])) ? $this->categories[$category][$domain] : null;
    }

    public function getDefaultDomain()
    {
        return $this->default_domain;
    }

    public function setDefaultDomain($domain)
    {
        $this->default_domain = $domain;
    }

    public function getDefaultCategory()
    {
        return $this->default_category;
    }

    public function setDefaultCategory($category)
    {
        $this->default_category = $category;
    }

    public function getDefaultContext()
    {
        return $this->default_context;
    }

    public function setDefaultContext($context)
    {
        $this->default_context = $context;
    }

    private function singularMessage($msgid, $context, $domain, $category)
    {
        $message = '';
        $context = (!empty($context)) ? $context : $this->default_context;
        $domain = (!empty($domain)) ? $domain : $this->default_domain;
        $category = (!empty($category)) ? $category : $this->default_category;

        if (isset($this->categories[$category]))
        {
            $category = $this->categories[$category];

            if (isset($category[$domain]['contexts'][$context][$msgid]['msgstr']))
            {
                $message = $category[$domain]['contexts'][$context][$msgid]['msgstr'];
            }
        }

        return (!empty($message)) ? $message : $msgid;
    }

    private function pluralMessage($msgid1, $msgid2, $n, $context, $domain, $category)
    {
        $message = '';
        $context = (!empty($context)) ? $context : $this->default_context;
        $domain = (!empty($domain)) ? $domain : $this->default_domain;
        $category = (!empty($category)) ? $category : $this->default_category;

        if (isset($this->categories[$category]))
        {
            $category = $this->categories[$category];

            if (isset($category[$domain]['contexts'][$context][$msgid1]))
            {
                $translation = $category[$domain]['contexts'][$context][$msgid1];
                $plural_rule = $category[$domain]['plural_rule'];
                eval($plural_rule);

                if ($plural === 0)
                {
                    $message = (isset($translation['plurals'][$plural])) ? $translation['plurals'][$plural] : $msgid1;
                    return (!empty($message)) ? $message : $msgid1;
                }
                else
                {
                    $message = (isset($translation['plurals'][$plural])) ? $translation['plurals'][$plural] : $msgid2;
                    return (!empty($message)) ? $message : $msgid2;
                }
            }
        }
    }
}