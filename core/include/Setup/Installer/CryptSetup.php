<?php
declare(strict_types = 1);

namespace Nelliel\Setup\Installer;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Language\Translator;
use Nelliel\Render\RenderCoreSimple;
use Nelliel\Utility\FileHandler;

class CryptSetup
{
    private $file_handler;
    private $translator;
    private $render_core;

    function __construct(FileHandler $file_handler, Translator $translator)
    {
        $this->file_handler = $file_handler;
        $this->translator = $translator;
        $this->render_core = new RenderCoreSimple(NEL_INCLUDE_PATH . 'Setup/Installer/templates/');
    }

    public function setup(string $step)
    {
        if ($step === 'crypt-check') {
            if (file_exists(NEL_CONFIG_FILES_PATH . 'crypt.php')) {
                $this->output('crypt/crypt_found', ['page_title' => __('Hashing config already exists')]);
            } else {
                $this->output('crypt/crypt_ask', ['page_title' => __('Hashing config')]);
            }
        }

        if ($step === 'crypt-config') {
            if (isset($_POST['new_crypt_config'])) {
                $this->output('crypt/crypt_ask', ['page_title' => __('Hashing config')]);
            }

            if (isset($_POST['customize_crypt_config'])) {
                $this->output('crypt/crypt_config', ['page_title' => __('Hashing config')]);
            }

            if (!isset($_POST['keep_crypt_config'])) {
                $config = $this->cryptConfig();
                $this->writeCryptConfig($config);
            }
        }

        $crypt_config = array();
        require_once NEL_CONFIG_FILES_PATH . 'crypt.php';
        define('NEL_PASSWORD_PREFERRED_ALGORITHM', $crypt_config['password_algorithm'] ?? 'BCRYPT');
        define('NEL_PASSWORD_BCRYPT_COST', $crypt_config['password_bcrypt_cost'] ?? '12');
        define('NEL_PASSWORD_ARGON2_MEMORY_COST', $crypt_config['password_argon2_memory_cost'] ?? 1024);
        define('NEL_PASSWORD_ARGON2_TIME_COST', $crypt_config['password_argon2_time_cost'] ?? 2);
        define('NEL_PASSWORD_ARGON2_THREADS', $crypt_config['password_argon2_threads'] ?? 2);
        define('NEL_IP_HASH_ALGORITHM', $crypt_config['ip_hash_algorithm'] ?? 'BCRYPT');
        define('NEL_IP_HASH_BCRYPT_COST', $crypt_config['ip_hash_bcrypt_cost'] ?? '08');

        if ($step === 'crypt-config') {
            $this->output('crypt/crypt_config_complete', ['page_title' => __('Hashing config complete')]);
        }
    }

    private function cryptConfig(): array
    {
        $config = array();
        $config['password_algorithm'] = $_POST['password_algorithm'] ?? 'BCRYPT';
        $config['password_bcrypt_cost'] = $this->stringifyBcryptCost(intval($_POST['password_bcrypt_cost'] ?? 12));
        $config['password_argon2_memory_cost'] = intval($_POST['password_argon2_memory_cost'] ?? 1024);
        $config['password_argon2_time_cost'] = intval($_POST['password_argon2_time_cost'] ?? 2);
        $config['password_argon2_threads'] = intval($_POST['password_argon2_threads'] ?? 2);
        $config['ip_hash_algorithm'] = $_POST['ip_hash_algorithm'] ?? 'BCRYPT';
        $config['ip_hash_bcrypt_cost'] = $this->stringifyBcryptCost(intval($_POST['ip_hash_bcrypt_cost'] ?? 8));
        return $config;
    }

    private function stringifyBcryptCost(int $cost): string
    {
        $string_cost = '';

        if ($cost < 10) {
            $string_cost .= '0';
        }

        return $string_cost . $cost;
    }

    private function writeCryptConfig(array $config, bool $overwrite = true): bool
    {
        if (!$overwrite && file_exists(NEL_CONFIG_FILES_PATH . 'crypt.php')) {
            return false;
        }

        $prepend = "\n" . '// Hashing config generated by Nelliel installer';
        $this->file_handler->writeInternalFile(NEL_CONFIG_FILES_PATH . 'crypt.php',
            $prepend . "\n" . nel_config_var_export($config, '$crypt_config'), true);
        return true;
    }

    private function output(string $template_file, array $render_data = array()): void
    {
        $render_data['base_stylesheet'] = NEL_STYLES_WEB_PATH . 'core/base_style.css';
        $html = $this->render_core->renderFromTemplateFile($template_file, $render_data);
        echo $this->translator->translateHTML($html);
        die();
    }
}