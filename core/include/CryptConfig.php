<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class CryptConfig
{
    private $config;
    private $account_password_algorithm;
    private $account_password_options = array();
    private $post_password_algorithm;
    private $post_password_options = array();
    private $ip_hash_algorithm;
    private $ip_hash_options = array();

    function __construct()
    {
        $crypt_config = array();
        include NEL_CONFIG_FILES_PATH . 'crypt.php';
        $this->config = $crypt_config;

        if ($crypt_config['account_password_algorithm'] === 'BCRYPT') {
            if (defined('PASSWORD_BCRYPT')) {
                $this->account_password_algorithm = PASSWORD_BCRYPT;
            } else {
                $this->account_password_algorithm = PASSWORD_DEFAULT;
            }

            $this->account_password_options['cost'] = intval($crypt_config['account_password_bcrypt_cost'] ?? 12);
        }

        if ($crypt_config['account_password_algorithm'] === 'ARGON2') {
            if (defined('PASSWORD_ARGON2ID')) {
                $this->account_password_algorithm = PASSWORD_ARGON2ID;
            } else if (defined('PASSWORD_ARGON2I')) {
                $this->account_password_algorithm = PASSWORD_ARGON2I;
            } else {
                $this->account_password_algorithm = PASSWORD_DEFAULT;
            }

            $this->account_password_options['memory_cost'] = $crypt_config['account_password_argon2_memory_cost'] ?? 1024;
            $this->account_password_options['time_cost'] = $crypt_config['account_password_argon2_time_cost'] ?? 2;
            $this->account_password_options['threads'] = $crypt_config['account_password_argon2_threads'] ?? 2;
        }

        if ($crypt_config['post_password_algorithm'] === 'BCRYPT') {
            if (defined('PASSWORD_BCRYPT')) {
                $this->post_password_algorithm = PASSWORD_BCRYPT;
            } else {
                $this->post_password_algorithm = PASSWORD_DEFAULT;
            }

            $this->post_password_options['cost'] = intval($crypt_config['post_password_bcrypt_cost'] ?? 6);
            $this->post_password_options['pepper'] = NEL_POST_PASSWORD_PEPPER;
        }

        if ($crypt_config['ip_hash_algorithm'] === 'BCRYPT') {
            if (defined('PASSWORD_BCRYPT')) {
                $this->ip_hash_algorithm = PASSWORD_BCRYPT;
            } else {
                $this->ip_hash_algorithm = PASSWORD_DEFAULT;
            }

            $this->ip_hash_options['cost'] = $this->stringifyBcryptCost($crypt_config['ip_hash_bcrypt_cost'] ?? 6);
            $this->ip_hash_options['pepper'] = NEL_IP_ADDRESS_PEPPER;
        }
    }

    private function stringifyBcryptCost($cost): string
    {
        $cost = intval($cost);
        $string_cost = '';

        if ($cost < 10) {
            $string_cost .= '0';
        }

        return $string_cost . $cost;
    }

    public function configValue(string $key)
    {
        return $this->config[$key] ?? null;
    }

    public function accountPasswordAlgorithm()
    {
        return $this->account_password_algorithm;
    }

    public function accountPasswordOptions(): array
    {
        return $this->account_password_options;
    }

    public function postPasswordAlgorithm()
    {
        return $this->post_password_algorithm;
    }

    public function postPasswordOptions(): array
    {
        return $this->post_password_options;
    }

    public function IPHashAlgorithm()
    {
        return $this->ip_hash_algorithm;
    }

    public function IPHashOptions(): array
    {
        return $this->ip_hash_options;
    }
}