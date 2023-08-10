<?php
declare(strict_types = 1);

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\DomainSite;

// TODO: Remove when minimum moves to PHP 7.4
if (!function_exists('hash_equals')) {

    function hash_equals(string $known_string, string $user_string)
    {
        if (strlen($known_string) != utf8_strlen($user_string)) {
            return false;
        } else {
            $res = $known_string ^ $user_string;
            $return = 0;

            for ($i = utf8_strlen($res) - 1; $i >= 0; $i --) {
                $return |= ord($res[$i]);
            }

            return !$return;
        }
    }
}

function nel_password_hash(string $password, int $algorithm, array $options = array(), bool $new_hash = false)
{
    static $hashes = array();

    if (isset($options['pepper'])) {
        $password = nel_pre_hash($password, (string) $options['pepper']);
    }

    if (!$new_hash && array_key_exists($password, $hashes)) {
        return $hashes[$password];
    }

    $hash = password_hash($password, $algorithm, $options);

    if ($hash !== false) {
        $hashes[$password] = $hash;
    }

    return $hash;
}

function nel_password_verify(string $password, string $hash, string $pepper = null, bool $new_pass = false): bool
{
    static $passwords = array();

    if (!is_null($pepper)) {
        $password = nel_pre_hash($password, $pepper);
    }

    if (!$new_pass && array_key_exists($hash, $passwords) && hash_equals($passwords[$hash], $password)) {
        return true;
    }

    if ($new_pass || !isset($passwords[$hash])) {
        $passwords[$hash] = $password;
    }

    return password_verify($password, $hash);
}

function nel_password_needs_rehash(string $password, int $algorithm, array $options = array()): bool
{
    $site_domain = new DomainSite(nel_database('core'));

    if (!$site_domain->setting('do_password_rehash')) {
        return false;
    }

    return password_needs_rehash($password, $algorithm);
}

function nel_ip_hash(string $ip_address, bool $new_hash = false)
{
    static $hashes = array();

    if (!$new_hash && array_key_exists($ip_address, $hashes)) {
        return $hashes[$ip_address];
    }

    // Bcrypt provides salting that can compensate for the small IPv4 space but we can't properly compare IP hashes when salted in the normal manner.
    // So for this specific case we compromise: pass a constant value for the salt, then keep only the output hash so it functions as a pepper.
    // Based on NPFChan
    $full_hash = crypt($ip_address,
        '$2y$' . nel_crypt_config()->IPHashOptions()['cost'] . '$' .
        str_replace('+', '/', nel_crypt_config()->IPHashOptions()['pepper']) . '$');
    $modified_hash = preg_replace('/[.\/]/', '_', $full_hash);
    $hashes[$ip_address] = utf8_substr($modified_hash, -31);

    return $hashes[$ip_address];
}

function nel_pre_hash(string $string, string $pepper, string $algorithm = 'sha256'): string
{
    $hmac = hash_hmac($algorithm, $string, $pepper, true);
    return base64_encode($hmac);
}
