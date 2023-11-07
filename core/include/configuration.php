<?php
declare(strict_types = 1);

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Utility\FileHandler;

$technical_config = array();

if ((isset($_GET['install']) || isset($_GET['upgrade'])) && !file_exists(NEL_CONFIG_FILES_PATH . 'technical.php')) {
    copy(NEL_CONFIG_FILES_PATH . 'technical.php.example', NEL_CONFIG_FILES_PATH . 'technical.php');
}

require_once NEL_CONFIG_FILES_PATH . 'technical.php';

define('NEL_SECURE_SESSION_ONLY', boolval($technical_config['secure_session_only'] ?? false));
define('NEL_DIRECTORY_PERM', strval($technical_config['directory_perm'] ?? '0775'));
define('NEL_FILES_PERM', strval($technical_config['file_perm'] ?? '0664'));
define('NEL_USE_FILE_CACHE', boolval($technical_config['use_file_cache'] ?? true));
define('NEL_USE_RENDER_CACHE', boolval($technical_config['use_render_cache'] ?? true));
define('NEL_USE_MUSTACHE_CACHE', boolval($technical_config['use_mustache_cache'] ?? true));
define('NEL_ENABLE_PLUGINS', boolval($technical_config['enable_plugins'] ?? true));
define('NEL_ENABLE_JSON_API', boolval($technical_config['enable_json_api'] ?? true));

$file_handler = new FileHandler();
$base_temp_directory = empty($technical_config['base_temp_directory'] ?? '') ? sys_get_temp_dir() : $technical_config['base_temp_directory'];
define('NEL_TEMP_FILES_BASE_DIRECTORY', $base_temp_directory);
define('NEL_TEMP_FILES_BASE_PATH', $file_handler->pathJoin(NEL_TEMP_FILES_BASE_DIRECTORY, DIRECTORY_SEPARATOR));
unset($base_temp_directory);

unset($technical_config);
