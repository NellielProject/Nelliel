<?php
defined('NELLIEL_VERSION') or die('NOPE.AVI');

$base_config = array();
$db_config = array();
$crypt_config = array();

require_once NEL_CONFIG_FILES_PATH . 'config.php';

if (file_exists(NEL_CONFIG_FILES_PATH . 'additional_databases.php')) {
    include NEL_CONFIG_FILES_PATH . 'additional_databases.php';
}

define('NEL_DEFAULT_TIME_ZONE', $base_config['default_time_zone'] ?? 'UTC');
define('NEL_INSTALL_KEY', $base_config['install_key'] ?? '');
date_default_timezone_set(NEL_DEFAULT_TIME_ZONE);
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

unset($base_config);
unset($db_config);
unset($crypt_config);

require_once NEL_INCLUDE_PATH . 'crypt.php';
nel_set_password_algorithm(NEL_PASSWORD_PREFERRED_ALGORITHM);

$language = new \Nelliel\Language\Language();
$language->loadLanguage(NEL_DEFAULT_LOCALE, 'nelliel', LC_MESSAGES);
unset($language);
Mustache_Autoloader::register();

require_once NEL_INCLUDE_PATH . 'general_functions.php';
$file_handler = nel_utilities()->fileHandler();
$setup = new \Nelliel\Setup\Setup(nel_database('core'), nel_utilities()->sqlCompatibility(), $file_handler);

if (isset($_GET['install'])) {
    $setup->install();
}

if (!$setup->checkInstallDone()) {
    nel_derp(107, _gettext('Installation has not been done yet or is not complete.'));
}

unset($setup);

$upgrade = new \Nelliel\Setup\Upgrade($file_handler);

if (isset($_GET['upgrade'])) {
    $upgrade->doUpgrades();
    die();
} else {
    if ($upgrade->needsUpgrade()) {
        nel_derp(110,
            _gettext('Versions do not match. An upgrade may be in progress or something is broken. Try again later.'));
    }
}

unset($upgrade);

if (file_exists(NEL_GENERATED_FILES_PATH . 'peppers.php')) {
    $peppers = array();
    include_once NEL_GENERATED_FILES_PATH . 'peppers.php';
    define('NEL_TRIPCODE_PEPPER', $peppers['tripcode_pepper']);
    define('NEL_IP_ADDRESS_PEPPER', $peppers['ip_address_pepper']);
    define('NEL_POSTER_ID_PEPPER', $peppers['poster_id_pepper']);
    define('NEL_POST_PASSWORD_PEPPER', $peppers['post_password_pepper']);
    unset($peppers);
}

unset($file_handler);

define('NEL_SETUP_GOOD', true);

require_once NEL_WAT_FILES_PATH . 'special.php';

nel_plugins()->loadPlugins();
