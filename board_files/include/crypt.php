<?php

//
// Most of these functions are basically wrappers to extend or simplify PHP password and crypt functions
//
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
            $res = $known_string ^ $user_string;
            $return = 0;

            for ($i = strlen($res) - 1; $i >= 0; $i --)
            {
                $return |= ord($res[$i]);
            }

            return !$return;
        }
    }
}

function nel_set_password_algorithm($choice)
{
    if(defined('NEL_PASSWORD_ALGORITHM'))
    {
        return;
    }

    if ($choice === 'BCRYPT' && defined('PASSWORD_BCRYPT'))
    {
        define('NEL_PASSWORD_ALGORITHM', PASSWORD_BCRYPT);
    }
    else if ($choice === 'ARGON2I' && defined('PASSWORD_ARGON2I'))
    {
        define('NEL_PASSWORD_ALGORITHM', PASSWORD_ARGON2I);
    }
    else if (defined('PASSWORD_DEFAULT'))
    {
        define('NEL_PASSWORD_ALGORITHM', PASSWORD_DEFAULT);
    }
    else
    {
        nel_derp(201, _gettext('No acceptable password hashing algorithm has been found. We can\'t function like this.'));
    }
}

function nel_password_hash($password, $algorithm, $options = array())
{
    switch ($algorithm)
    {
        case 1:
            $options['cost'] = isset($options['cost']) ? $options['cost'] : NEL_PASSWORD_BCRYPT_COST;
            return password_hash($password, $algorithm, $options);

        case 2:
            $options['memory_cost'] = isset($options['memory_cost']) ? $options['memory_cost'] : NEL_PASSWORD_ARGON2_MEMORY_COST;
            $options['time_cost'] = isset($options['time_cost']) ? $options['time_cost'] : NEL_PASSWORD_ARGON2_TIME_COST;
            $options['threads'] = isset($options['threads']) ? $options['threads'] : NEL_PASSWORD_ARGON2_THREADS;
            return password_hash($password, $algorithm, $options);

        default:
            return false;
    }
}

function nel_password_verify($password, $hash)
{
    return password_verify($password, $hash);
}

function nel_password_needs_rehash($hash, $algorithm, $options = array())
{
    if (!nel_parameters()->siteSettings('do_password_rehash'))
    {
        return false;
    }

    return password_needs_rehash($password, $algorithm);
}

function nel_password_info($hash)
{
    $info = password_get_info($hash);
    return $info;
}

function nel_salted_hash_info($hash)
{
    $available = hash_algos();
    $info = array();
    $pieces = explode('$', $hash);

    $info['algoName'] = 'unknown';
    $info['salt'] = '';
    $info['hash'] = '';

    if (in_array($pieces[1], $available))
    {
        $info['algoName'] = $pieces[1];
        $info['salt'] = $pieces[2];
        $info['hash'] = $pieces[3];
    }

    return $info;
}

function nel_generate_salted_hash($algorithm, $string, $salt = null, $salt_length = 16)
{
    if (is_null($salt))
    {
        $salt = nel_gen_salt($salt_length);
    }

    $full_string = $salt . $string;
    $hash = hash($algorithm, $full_string, false);
    return '$' . $algorithm . '$' . $salt . '$' . $hash;
}

function nel_verify_salted_hash($string, $hash)
{
    $info = nel_salted_hash_info($hash);

    if ($info['algoName'] === 'unknown')
    {
        return false;
    }

    $new_hash = nel_generate_salted_hash($info['algoName'], $string, $info['salt']);
    return hash_equals($hash, $new_hash);
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
