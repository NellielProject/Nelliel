<?php

require_once LIBRARY_PATH . 'password_compat/password.php';

//
// Most of these functions are basically wrappers to extend or simplify PHP password and crypt functions
//
define('NEL_PASSWORD_MD5', 100);
define('NEL_PASSWORD_SHA256', 101);
define('NEL_PASSWORD_SHA512', 102);

if (!function_exists('hash_equals'))
{
    function hash_equals($known_string, $user_string)
    {
        if (strlen($known_string) != strlen($user_string))
        {
            return false;
        }
        else
        {
            $res = $known_string^ $user_string;
            $return = 0;

            for ($i = strlen($res) - 1; $i >= 0; $i --)
            {
                $return |= ord($res[$i]);
            }

            return !$return;
        }
    }
}

function nel_verify_hash_algorithm()
{
    if(defined('NELLIEL_PASS_ALGORITHM'))
    {
        return;
    }

    $best_hashing = nel_best_available_hashing();

    if($best_hashing === 0)
    {
        nel_derp(201, nel_stext('ERROR_201'));
    }
    else
    {
        define('NELLIEL_PASS_ALGORITHM', $best_hashing);
    }
}

function nel_password_hash($password, $algorithm, array $options = array())
{
    nel_verify_hash_algorithm();

    if ($algorithm === 1)
    {
        if (!array_key_exists('cost', $options))
        {
            $options['cost'] = PASSWORD_BCRYPT_COST;
        }

        return password_hash($password, $algorithm, $options);
    }

    if ($algorithm >= 100)
    {
        return nel_crypt($password, $algorithm, 'PASSWORD', $options);
    }
}

function nel_password_verify($password, $hash)
{
    return password_verify($password, $hash);
}

function nel_password_needs_rehash($hash, $algorithm, array $options = array())
{
    if (!nel_site_settings('do_password_rehash'))
    {
        return false;
    }

    $info = nel_password_info($hash);

    if ($info['algo'] < 100)
    {
        return password_needs_rehash($password, $algorithm);
    }
    else
    {
        if ($info['algo'] < $algorithm)
        {
            return true;
        }
        else if ($info['algo'] === $algorithm && $info['options']['cost'] < $options['cost'])
        {
            return true;
        }
    }

    return false;
}

function nel_password_info($hash)
{
    $return = array();
    $info = password_get_info($hash);

    if ($info['algo'] === 0)
    {
        $id = substr($hash, 0, 3);
        list ($cost) = sscanf($hash, $id);

        if ($id === '$1$')
        {
            $return['algo'] = NEL_PASSWORD_MD5;
            $return['algoName'] = 'md5';
            $return['options']['cost'] = 1000;
        }
        else if ($id === '$5$')
        {
            $return['algo'] = NEL_PASSWORD_SHA256;
            $return['algoName'] = 'sha256';
            $return['options']['cost'] = $cost;
        }
        else if ($id === '$6$')
        {
            $return['algo'] = NEL_PASSWORD_SHA512;
            $return['algoName'] = 'sha512';
            $return['options']['cost'] = $cost;
        }
    }
    else
    {
        $return = $info;
    }

    return $return;
}

function nel_salted_hash_info($hash)
{
    $available = hash_algos();
    $info = array();
    $pieces = explode('$', $hash);

    if (in_array($pieces[0], $available))
    {
        $info['algoName'] = $pieces[0];
        $info['salt'] = $pieces[1];
        $info['hash'] = $pieces[2];
    }
    else
    {
        $info['algoName'] = 'unknown';
        $info['salt'] = '';
        $info['hash'] = '';
    }

    return $info;
}

function nel_generate_salted_hash($algorithm, $string, $salt = null)
{
    if(is_null($salt))
    {
        $salt = nel_gen_salt(16);
    }

    $full_string = $salt . $string;
    $hash = hash($algorithm, $full_string, false);
    return $algorithm . '$' . $salt . '$' . $hash;
}

function nel_verify_salted_hash($string, $hash)
{
    $info = nel_salted_hash_info($hash);

    if($info['algoName'] === 'unknown')
    {
        return false;
    }

    $new_hash = nel_generate_salted_hash($info['algoName'], $string, $info['salt']);
    return hash_equals($hash, $new_hash);
}

function nel_get_crypt_cost($algorithm, $type)
{
    if ($algorithm === 1)
    {
        return constant($type . '_BCRYPT_COST');
    }

    if ($algorithm === 100 || $algorithm === 101)
    {
        return constant($type . '_SHA2_COST');
    }

    return 0;
}

function nel_crypt($password, $algorithm, $type, array $options = array())
{
    if (!array_key_exists('salt', $options))
    {
        $options['salt'] = nel_gen_salt(16);
    }

    if (!array_key_exists('cost', $options))
    {
        $options['cost'] = nel_get_crypt_cost($algorithm, $type);
    }

    if ($algorithm === PASSWORD_BCRYPT)
    {
        return crypt($password, '$2y$' . $options['cost'] . '$' . $options['salt'] . '$');
    }

    if ($algorithm === NEL_PASSWORD_SHA256)
    {
        return crypt($password, '$5$rounds=' . $options['cost'] . '$' . $options['salt'] . '$');
    }

    if ($algorithm === NEL_PASSWORD_SHA512)
    {
        return crypt($password, '$6$rounds=' . $options['cost'] . '$' . $options['salt'] . '$');
    }
}

function nel_best_available_hashing()
{
    if (PasswordCompat\binary\check())
    {
        if (nel_site_settings('use_password_default_algorithm'))
        {
            return PASSWORD_DEFAULT;
        }
        else
        {
            return PASSWORD_BCRYPT;
        }
    }
    else if (nel_site_settings('do_sha2_fallback') && defined('CRYPT_SHA512') && CRYPT_SHA512 == 1)
    {
        return NEL_PASSWORD_SHA512;
    }
    else if (nel_site_settings('do_sha2_fallback') && defined('CRYPT_SHA256') && CRYPT_SHA256 == 1)
    {
        return NEL_PASSWORD_SHA256;
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
        $salt = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
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
