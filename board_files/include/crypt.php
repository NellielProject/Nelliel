<?php

define('NEL_PASSWORD_MD5', 100);
define('NEL_PASSWORD_SHA256', 101);
define('NEL_PASSWORD_SHA512', 102);

function nel_password_hash($password, $algorithm, array $options = array())
{
    if ($algorithm < 100)
    {
        if (empty($options))
        {
            $options['cost'] = BCRYPT_COST;
        }

        return password_hash($password, $algorithm, $options);
    }
    else
    {
        return nel_crypt($password, $options);
    }
}

function nel_password_verify($password, $hash)
{
    return password_verify($password, $hash);
}

function nel_password_needs_rehash($hash, $algorithm, array $options = array())
{
    $id = nel_get_crypt_algorithm_id($hash);

    if (!DO_PASSWORD_REHASH || $id === $algorithm)
    {
        return false;
    }

    if ($id < 100)
    {
        return password_needs_rehash($password, $algorithm);
    }
    else
    {
        return true;
    }
}

function nel_get_crypt_algorithm_id($hash)
{
    if (substr($hash, 0, 4) === '$2y$') // Latest Blowfish
    {
        return PASSWORD_BCRYPT;
    }

    if (substr($hash, 0, 3) === '$1$') // MD5
    {
        return NEL_PASSWORD_MD5;
    }

    if (substr($hash, 0, 3) === '$5$') // SHA256
    {
        return NEL_PASSWORD_SHA256;
    }

    if (substr($hash, 0, 3) === '$6$') // SHA512
    {
        return NEL_PASSWORD_SHA512;
    }

    return 0; // Unknown
}

function nel_crypt($password, $algorithm = 900, array $options = array())
{
    if (!array_key_exists('salt', $options))
    {
        $options['salt'] = nel_gen_salt(16);
    }

    if ($algorithm === NEL_PASSWORD_SHA256)
    {
        return crypt($password, '$5$rounds=' . CRYPT_SHA_COST . '$' . $options['salt'] . '$');
    }

    if ($algorithm === NEL_PASSWORD_SHA512)
    {
        return crypt($password, '$6$rounds=' . CRYPT_SHA_COST . '$' . $options['salt'] . '$');
    }
}

function nel_best_available_hashing()
{
    if (PasswordCompat\binary\check())
    {
        if (USE_PASSWORD_DEFAULT)
        {
           return PASSWORD_DEFAULT;
        }
        else
        {
            return PASSWORD_BCRYPT;
        }
    }
    else if (SHA2_FALLBACK)
    {
        if (defined('CRYPT_SHA512') && CRYPT_SHA512 == 1)
        {
            return NEL_PASSWORD_SHA512;
        }
        else if (defined('CRYPT_SHA256') && CRYPT_SHA256 == 1)
        {
            return NEL_PASSWORD_SHA256;
        }
    }
    else
    {
        return 0;
    }
}

function nel_gen_salt($length)
{
    $salt = '';
    $good = false;

    if (function_exists('mcrypt_create_iv'))
    {
        $salt = mcrypt_create_iv($raw_salt_len, MCRYPT_DEV_URANDOM);
        $good = ($salt !== false) ? true : false;
    }

    if ($good === false && function_exists('openssl_random_pseudo_bytes'))
    {
        $strong = false;
        $salt = openssl_random_pseudo_bytes($length, $strong);
        $good = ($salt !== false && $strong) ? true : false;
    }

    if ($good === false)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789/\\][{}\'";:?.>,<!@#$%^&*()-_=+|';

        for ($i = 0; $i < $length; $i ++)
        {
            $salt .= $charset[mt_rand(0, strlen($charset) - 1)];
        }

        $good = ($salt !== '') ? true : false;
    }

    $base_64 = base64_encode($salt);
    $salt = rtrim($base_64, '=');
    $salt = strtr($salt, '+', '.');
    return $salt;
}