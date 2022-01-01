<?php
use SmallPHPGettext\SmallPHPGettext;

/**
 * Gets the SmallPHPGettext instance after optionally changing it.
 *
 * @param SmallPHPGettext [optional] $instance
 * @return \SmallPHPGettext\SmallPHPGettext
 */
function access_small_php_gettext(?SmallPHPGettext $instance = null)
{
    static $current_instance;

    if (!empty($instance))
    {
        $current_instance = $instance;
    }

    return $current_instance;
}

if (!function_exists('__'))
{
    function __(string $msgid)
    {
        return access_small_php_gettext()->gettext($msgid);
    }
}

if (!function_exists('_gettext'))
{
    function _gettext(string $msgid)
    {
        return access_small_php_gettext()->gettext($msgid);
    }
}

if (!function_exists('_ngettext'))
{
    function _ngettext(string $msgid1, string $msgid2, $n)
    {
        return access_small_php_gettext()->ngettext($msgid1, $msgid2, $n);
    }
}

if (!function_exists('_pgettext'))
{
    function _pgettext(string $context, string $msgid)
    {
        return access_small_php_gettext()->pgettext($context, $msgid);
    }
}

if (!function_exists('_npgettext'))
{
    function _npgettext(string $context, string $msgid1, string $msgid2, int $n)
    {
        return access_small_php_gettext()->npgettext($context, $msgid1, $msgid2, $n);
    }
}

if (!function_exists('_dgettext'))
{
    function _dgettext(string $domain, string $msgid)
    {
        return access_small_php_gettext()->dgettext($domain, $msgid);
    }
}

if (!function_exists('_dngettext'))
{
    function _dngettext(string $domain, string $msgid1, string $msgid2, int $n)
    {
        return access_small_php_gettext()->dngettext($domain, $msgid1, $msgid2, $n);
    }
}

if (!function_exists('_dcgettext'))
{
    function _dcgettext(string $domain, string $msgid, int $category)
    {
        return access_small_php_gettext()->dcgettext($domain, $msgid, $category);
    }
}

if (!function_exists('_dcngettext'))
{
    function _dcngettext(string $domain, string $msgid1, string $msgid2, int $n, int $category)
    {
        return access_small_php_gettext()->dcngettext($domain, $msgid1, $msgid2, $n, $category);
    }
}
