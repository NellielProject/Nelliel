<?php

function access_small_php_gettext($instance = null)
{
    static $current_instance;

    if(!empty($instance))
    {
        $current_instance = $instance;
    }

    return $current_instance;
}

function _gettext($msgid)
{
    return access_small_php_gettext()->gettext($msgid);
}

function _ngettext($msgid1, $msgid2, $n)
{
    return access_small_php_gettext()->ngettext($msgid1, $msgid2, $n);
}

function _pgettext($context, $msgid)
{
    return access_small_php_gettext()->pgettext($context, $msgid);
}

function _npgettext($context, $msgid1, $msgid2, $n)
{
    return access_small_php_gettext()->npgettext($context, $msgid1, $msgid2, $n);
}

function _dgettext($domain, $msgid)
{
    return access_small_php_gettext()->dgettext($domain, $msgid);
}

function _dngettext($domain, $msgid1, $msgid2, $n)
{
    return access_small_php_gettext()->dngettext($domain, $msgid1, $msgid2, $n);
}

function _dcgettext($domain, $msgid, $category)
{
    return access_small_php_gettext()->dcgettext($domain, $msgid, $category);
}

function _dcngettext($domain, $msgid1, $msgid2, $n, $category)
{
    return access_small_php_gettext()->dcngettext($domain, $msgid1, $msgid2, $n, $category);
}