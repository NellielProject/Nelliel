<?php
declare(strict_types = 1);

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Utility\FileHandler;

$base_config = array();
$db_config = array();
$crypt_config = array();

require_once NEL_CONFIG_FILES_PATH . 'config.php';

if (file_exists(NEL_CONFIG_FILES_PATH . 'additional_databases.php')) {
    include NEL_CONFIG_FILES_PATH . 'additional_databases.php';
}

define('NEL_DEFAULT_TIME_ZONE', $base_config['default_time_zone'] ?? 'UTC');
date_default_timezone_set(NEL_DEFAULT_TIME_ZONE);

define('NEL_INSTALL_KEY', $base_config['install_key'] ?? '');
define('NEL_DIRECTORY_PERM', $base_config['directory_perm'] ?? '0775');
define('NEL_FILES_PERM', $base_config['file_perm'] ?? '0664');
define('NEL_USE_FILE_CACHE', $base_config['use_file_cache'] ?? true);
define('NEL_USE_RENDER_CACHE', $base_config['use_render_cache'] ?? true);
define('NEL_USE_MUSTACHE_CACHE', $base_config['use_mustache_cache'] ?? true);
define('NEL_DEFAULT_LOCALE', $base_config['default_locale'] ?? 'en_US');
define('NEL_ENABLE_PLUGINS', $base_config['enable_plugins'] ?? true);
define('NEL_ENABLE_JSON_API', $base_config['enable_json_api'] ?? true);
define('NEL_SECURE_SESSION_ONLY', $base_config['secure_session_only'] ?? false);
define('NEL_DATABASES', $db_config);
define('NEL_PASSWORD_PREFERRED_ALGORITHM', $crypt_config['password_algorithm'] ?? 'BCRYPT');
define('NEL_PASSWORD_BCRYPT_COST', $crypt_config['password_bcrypt_cost'] ?? 12);
define('NEL_PASSWORD_ARGON2_MEMORY_COST', $crypt_config['password_argon2_memory_cost'] ?? 1024);
define('NEL_PASSWORD_ARGON2_TIME_COST', $crypt_config['password_argon2_time_cost'] ?? 2);
define('NEL_PASSWORD_ARGON2_THREADS', $crypt_config['password_argon2_threads'] ?? 2);

$file_handler = new FileHandler();
$base_temp_directory = empty($base_config['base_temp_directory'] ?? '') ? sys_get_temp_dir() : $base_config['base_temp_directory'];
define('NEL_TEMP_FILES_BASE_DIRECTORY', $base_temp_directory);
define('NEL_TEMP_FILES_BASE_PATH', $file_handler->pathJoin(NEL_TEMP_FILES_BASE_DIRECTORY, DIRECTORY_SEPARATOR));
unset($base_temp_directory);

unset($base_config);
unset($db_config);
unset($crypt_config);
