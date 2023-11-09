<?php
declare(strict_types = 1);

defined('NELLIEL_VERSION') or die('NOPE.AVI');

// TODO: Remove when minimum moves to PHP 7.4
if (!function_exists('hash_equals')) {

    function hash_equals(string $known_string, string $user_string): bool
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

function nel_password_hash(string $password, string $algorithm, array $options = array(), bool $new_hash = false)
{
    static $hashes = array();

    if (isset($options['pepper'])) {
        $password = nel_prehash($password, $options['pepper'], 'sha256');
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
        $password = nel_prehash($password, $pepper, 'sha256');
    }

    if (!$new_pass && array_key_exists($hash, $passwords) && hash_equals($passwords[$hash], $password)) {
        return true;
    }

    if ($new_pass || !isset($passwords[$hash])) {
        $passwords[$hash] = $password;
    }

    return password_verify($password, $hash);
}

function nel_password_needs_rehash(string $password, string $algorithm, array $options = array()): bool
{
    if (!nel_site_domain()->setting('do_password_rehash')) {
        return false;
    }

    return password_needs_rehash($password, $algorithm);
}

function nel_ip_hash(string $ip_address, bool $new_hash = false): string
{
    static $hashes = array();

    if (!$new_hash && array_key_exists($ip_address, $hashes)) {
        return $hashes[$ip_address];
    }

    if (!nel_crypt_config()->IPHashOptions()['strong_hashing']) {
        return nel_prehash($ip_address, nel_crypt_config()->IPHashOptions()['pepper'], 'sha256');
    }

    // Bcrypt provides salting that can compensate for the small IPv4 space but we can't compare IP hashes when salted in the normal manner.
    // So for this specific case we pass a constant value for the salt, then keep only the output hash so it functions as a pepper.
    // Based on NPFChan

    $pepper = nel_crypt_config()->IPHashOptions()['pepper'];

    if (nel_crypt_config()->IPHashOptions()['ip_strong_algorithm'] === PASSWORD_BCRYPT) {
        $pepper = preg_replace('/[^\$\.\/0-9A-Za-z]/', '/', $pepper);
    }

    $full_hash = crypt($ip_address, '$2y$' . nel_crypt_config()->IPHashOptions()['cost'] . '$' . $pepper . '$');
    $modified_hash = preg_replace('/[.\/]/', '_', $full_hash);
    $hashes[$ip_address] = utf8_substr($modified_hash, -31);

    return $hashes[$ip_address];
}

function nel_prehash(string $string, string $pepper, string $algorithm = 'sha256'): string
{
    $hmac = hash_hmac($algorithm, $string, $pepper, true);
    return base64_encode($hmac);
}
