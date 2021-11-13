<?php
defined('NELLIEL_VERSION') or die('NOPE.AVI');

//
// Most of these functions are basically wrappers to extend or simplify PHP password and crypt functions
//
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

function nel_set_password_algorithm(string $algorithm)
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

function nel_password_hash(string $password, int $algorithm, array $options = array())
{
    switch ($algorithm) {
        case PASSWORD_BCRYPT:
            $options['cost'] = $options['cost'] ?? NEL_PASSWORD_BCRYPT_COST;
            return password_hash($password, $algorithm, $options);

        case PASSWORD_ARGON2I:
        case PASSWORD_ARGON2ID:
            $options['memory_cost'] = $options['memory_cost'] ?? NEL_PASSWORD_ARGON2_MEMORY_COST;
            $options['time_cost'] = $options['time_cost'] ?? NEL_PASSWORD_ARGON2_TIME_COST;
            $options['threads'] = $options['threads'] ?? NEL_PASSWORD_ARGON2_THREADS;
            return password_hash($password, $algorithm, $options);

        default:
            return false;
    }
}

function nel_password_verify(string $password, string $hash)
{
    return password_verify($password, $hash);
}

function nel_password_needs_rehash(string $password, int $algorithm, array $options = array())
{
    $site_domain = new \Nelliel\Domains\DomainSite(nel_database('core'));

    if (!$site_domain->setting('do_password_rehash')) {
        return false;
    }

    return password_needs_rehash($password, $algorithm);
}

function nel_ip_hash(string $ip_address)
{
    $hashed_ip = hash_hmac('sha256', $ip_address, NEL_IP_ADDRESS_PEPPER);
    return substr($hashed_ip, 0, 32);
}

function nel_post_password_hash(string $post_password)
{
    $hashed_password = hash_hmac('sha256', $post_password, NEL_POST_PASSWORD_PEPPER);
    return $hashed_password;
}
