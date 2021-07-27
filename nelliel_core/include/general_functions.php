<?php
defined('NELLIEL_VERSION') or die('NOPE.AVI');

function nel_clean_exit()
{
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $authorization->saveUsers();
    $authorization->saveRoles();
    $redirect = new \Nelliel\Redirect();
    $redirect->go();

    die();
}

function nel_redirect(string $url, int $delay, bool $output = true)
{
    $redirect = '<meta http-equiv="refresh" content="' . $delay . ';URL=' . $url . '">';

    if ($output)
    {
        echo $redirect;
    }
    else
    {
        return $redirect;
    }
}

function nel_get_microtime(bool $convert_int = true)
{
    $time = microtime();
    $return_time = ['time' => $time];
    $split_time = explode(' ', $time);

    if ($convert_int)
    {
        $return_time = ['time' => intval($split_time[1]), 'milli' => intval(round($split_time[0], 3) * 1000),
            'micro' => intval($split_time[0] * 1000000)];
    }
    else
    {
        $return_time = ['time' => (float) $split_time[1], 'milli' => round($split_time[0], 3),
            'micro' => (float) $split_time[0] * 1000000];
    }

    return $return_time;
}

function nel_utf8_to_numeric_html_entities(&$input, bool $non_ascii_only = true)
{
    $regex = ($non_ascii_only) ? '#([^[:ascii:]])#Su' : '#(.)#Su';

    $input = preg_replace_callback($regex, function ($matches)
    {
        return '&#' . utf8_ord($matches[0]) . ';';
    }, $input);
}

function nel_numeric_html_entities_to_utf8(&$input)
{
    $input = preg_replace_callback('#&\#[0-9]+;#Su',
            function ($matches)
            {
                return utf8_chr(intval(substr($matches[0], 2, -1)));
            }, $input);
}

function nel_cast_to_datatype($value, $datatype, $null_empty = true)
{
    if (nel_true_empty($value))
    {
        if ($null_empty)
        {
            return null;
        }
        else
        {
            return $value;
        }
    }
    else if ($datatype === 'bool' || $datatype === 'boolean')
    {
        return (bool) $value;
    }
    else if ($datatype === 'int' || $datatype === 'integer')
    {
        return intval($value);
    }
    else if ($datatype === 'str' || $datatype === 'string')
    {
        return print_r($value, true);
    }
    else
    {
        return $value;
    }
}

function nel_true_empty($variable)
{
    return is_null($variable) || $variable === '' || $variable === array();
}

function nel_random_alphanumeric($length)
{
    if ($length <= 0)
    {
        return '';
    }

    $base = str_repeat('abcdefghijklmnopqrstuvwxyz0123456789', ceil($length / 36));
    $random = str_shuffle($base);
    return substr($random, 0, $length);
}

function nel_form_input_default(array $input)
{
    $value = '';

    if (isset($input['default']))
    {
        $value = $input['default'];
    }

    if (isset($input['value']))
    {
        $value = $input['value'];
    }

    return $value;
}

function nel_prepare_ip_for_storage(?string $ip_address, bool $unhashed_check = true)
{
    if ($unhashed_check && !nel_site_domain()->setting('store_unhashed_ip'))
    {
        return null;
    }

    $packed_ip_address = @inet_pton($ip_address);

    if ($packed_ip_address === false)
    {
        return null;
    }

    return $packed_ip_address;
}

function nel_prepare_hash_for_storage(?string $hash)
{
    if (is_null($hash))
    {
        return null;
    }

    return hex2bin($hash);
}

function nel_convert_ip_from_storage(?string $ip_address)
{
    if (is_null($ip_address))
    {
        return null;
    }

    $unpacked_ip_address = @inet_ntop($ip_address);

    if ($unpacked_ip_address === false)
    {
        return null;
    }

    return $unpacked_ip_address;
}

function nel_convert_hash_from_storage(?string $hash)
{
    if (is_null($hash))
    {
        return null;
    }

    return bin2hex($hash);
}

function nel_truncate_hash(string $hash, int $length = 12)
{
    return substr($hash, 0, $length);
}

function nel_convert_global_ID(?string $id, bool $to_null): ?string
{
    if (nel_true_empty($id) || $id === \Nelliel\Domains\Domain::GLOBAL)
    {
        if ($to_null)
        {
            return null;
        }
        else
        {
            return \Nelliel\Domains\Domain::GLOBAL;
        }
    }

    return $id;
}

function nel_filter_global_ID(?string $id, string $permission, \Nelliel\Auth\AuthUser $user): ?string
{
    if (!$user->checkPermission(nel_global_domain(), $permission, false))
    {
        nel_derp();
    }

    return nel_convert_global_ID($id, true);
}
