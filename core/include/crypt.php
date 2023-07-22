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

function nel_set_password_algorithm(string $algorithm): void
{
    if (defined('NEL_PASSWORD_ALGORITHM')) {
        return;
    }

    if ($algorithm === 'ARGON2') {
        if (defined('PASSWORD_ARGON2ID')) {
            define('NEL_PASSWORD_ALGORITHM', PASSWORD_ARGON2ID);
            return;
        } else if (defined('PASSWORD_ARGON2I')) {
            define('NEL_PASSWORD_ALGORITHM', PASSWORD_ARGON2I);
            return;
        }
    }

    if (!defined('NEL_PASSWORD_ALGORITHM') || $algorithm === 'BCRYPT') {
        if (defined('PASSWORD_BCRYPT')) {
            define('NEL_PASSWORD_ALGORITHM', PASSWORD_BCRYPT);
            return;
        }
    }

    if (defined('PASSWORD_DEFAULT')) {
        define('NEL_PASSWORD_ALGORITHM', PASSWORD_DEFAULT);
    } else {
        nel_derp(101, _gettext("No acceptable password hashing algorithm has been found. We can't function like this."));
    }
}

function nel_password_hash(string $password, int $algorithm, array $options = array(), bool $new_hash = false)
{
    static $hashes = array();

    if (!$new_hash && array_key_exists($password, $hashes)) {
        return $hashes[$password];
    }

    switch ($algorithm) {
        case PASSWORD_BCRYPT:
            $options['cost'] = $options['cost'] ?? NEL_PASSWORD_BCRYPT_COST;
            $hash = password_hash($password, $algorithm, $options);
            break;

        case PASSWORD_ARGON2I:
        case PASSWORD_ARGON2ID:
            $options['memory_cost'] = $options['memory_cost'] ?? NEL_PASSWORD_ARGON2_MEMORY_COST;
            $options['time_cost'] = $options['time_cost'] ?? NEL_PASSWORD_ARGON2_TIME_COST;
            $options['threads'] = $options['threads'] ?? NEL_PASSWORD_ARGON2_THREADS;
            $hash = password_hash($password, $algorithm, $options);
            break;

        default:
            return false;
    }

    if ($hash !== false) {
        $hashes[$password] = $hash;
    }

    return $hash;
}

function nel_password_verify(string $password, string $hash): bool
{
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

    // Bcrypt provides salting to compensate for the small IPv4 space but we can't properly compare IP hashes when salted in the normal manner.
    // So for this specific case we compromise: pass a constant value for the salt, then keep only the output hash so it functions as a pepper.
    // Based on NPFChan
    $full_hash = crypt($ip_address,
        '$2y$' . NEL_IP_HASH_BCRYPT_COST . '$' . str_replace('+', '/', NEL_IP_ADDRESS_PEPPER) . '$');
    $modified_hash = preg_replace('/[.\/]/', '_', $full_hash);
    $hashes[$ip_address] = utf8_substr($modified_hash, -31);

    return $hashes[$ip_address];
}
