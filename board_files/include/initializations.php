<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

if (ini_get('date.timezone') === '')
{
    date_default_timezone_set('UTC');
}

define('ASSETS_DIR', 'assets');
define('STYLES_DIR', 'styles');
define('ICON_SET_DIR', 'icon_sets');
define('IMAGES_DIR', 'imagez');
define('SCRIPT_DIR', 'script');

define('PHP_SELF', 'imgboard.php'); // Name of main script file
define('PHP_SELF2', 'index'); // Name of board index
define('PHP_EXT', '.html'); // Extension used for board pages
define('JSON_EXT', '.json'); // Extension used for board pages

define('ASSETS_TABLE', 'nelliel_assets');
define('BANS_TABLE', 'nelliel_bans');
define('BOARD_DATA_TABLE', 'nelliel_board_data');
define('BOARD_DEFAULTS_TABLE', 'nelliel_board_defaults');
define('CAPTCHA_TABLE', 'nelliel_captcha');
define('FILE_FILTERS_TABLE', 'nelliel_file_filters');
define('FILETYPES_TABLE', 'nelliel_filetypes');
define('LOGIN_ATTEMPTS_TABLE', 'nelliel_login_attempts');
define('PERMISSIONS_TABLE', 'nelliel_permissions');
define('REPORTS_TABLE', 'nelliel_reports');
define('ROLE_PERMISSIONS_TABLE', 'nelliel_role_permissions');
define('ROLES_TABLE', 'nelliel_roles');
define('SITE_CONFIG_TABLE', 'nelliel_site_config');
define('TEMPLATES_TABLE', 'nelliel_templates');
define('USER_ROLES_TABLE', 'nelliel_user_roles');
define('USERS_TABLE', 'nelliel_users');
define('VERSIONS_TABLE', 'nelliel_version');

define('CONFIG_FILE_PATH', BASE_PATH . 'configuration/');
define('CACHE_FILE_PATH', FILES_PATH . 'cache/');
define('TEMPLATE_FILE_PATH', FILES_PATH . 'templates/');
define('PLUGINS_FILE_PATH', FILES_PATH . 'plugins/');
define('LANGUAGE_FILE_PATH', FILES_PATH . 'languages/');
define('LOCALE_FILE_PATH', LANGUAGE_FILE_PATH . 'locale/');
define('STYLES_FILE_PATH', BASE_PATH . ASSETS_DIR . '/' . STYLES_DIR . '/');
define('ICON_SETS_FILE_PATH', BASE_PATH . ASSETS_DIR . '/' . ICON_SET_DIR . '/');

define('SCRIPT_WEB_PATH', ASSETS_DIR . '/' . SCRIPT_DIR . '/');
define('IMAGES_WEB_PATH', ASSETS_DIR . '/' . IMAGES_DIR . '/');
define('STYLES_WEB_PATH', ASSETS_DIR . '/' . STYLES_DIR . '/');
define('ICON_SETS_WEB_PATH', ASSETS_DIR . '/' . ICON_SET_DIR . '/');

define('SQLITE_DB_DEFAULT_PATH', FILES_PATH); // Base SQLite DB location

define('BASE_HONEYPOT_FIELD1', 'display_signature'); // Honeypot field name
define('BASE_HONEYPOT_FIELD2', 'signature'); // Honeypot field name
define('BASE_HONEYPOT_FIELD3', 'website'); // Honeypot field name

// Set default values here in case the config is missing something
$base_config['defaultadmin'] = '';
$base_config['defaultadmin_pass'] = '';
$base_config['tripcode_pepper'] = 'sodiumz';
$base_config['run_setup_check'] = true;
$base_config['directory_perm'] = '0775';
$base_config['file_perm'] = '0664';
$base_config['use_internal_cache'] = true;
$base_config['default_locale'] = 'en_US';
$base_config['enable_plugins'] = true;
$base_config['secure_session_only'] = false;
$crypt_config['password_algorithm'] = 'BCRYPT';
$crypt_config['password_bcrypt_cost'] = 12;
$crypt_config['argon2_memory_cost'] = 1024;
$crypt_config['argon2_time_cost'] = 2;
$crypt_config['argon2_threads'] = 2;

require_once CONFIG_FILE_PATH . 'config.php';

define('DEFAULTADMIN', $base_config['defaultadmin']);
define('DEFAULTADMIN_PASS', $base_config['defaultadmin_pass']);
define('RUN_SETUP_CHECK', (bool)$base_config['run_setup_check']);
define('DIRECTORY_PERM', $base_config['directory_perm']);
define('FILE_PERM', $base_config['file_perm']);
define('USE_INTERNAL_CACHE', $base_config['use_internal_cache']);
define('DEFAULT_LOCALE', $base_config['default_locale']);
define('ENABLE_PLUGINS', $base_config['enable_plugins']);
define('SECURE_SESSION_ONLY', $base_config['secure_session_only']);
define('SQLTYPE', $db_config['sqltype']);
define('MYSQL_DB', $db_config['mysql_db']);
define('MYSQL_HOST', $db_config['mysql_host']);
define('MYSQL_PORT', $db_config['mysql_port']);
define('MYSQL_USER', $db_config['mysql_user']);
define('MYSQL_PASS', $db_config['mysql_pass']);
define('MYSQL_ENCODING', $db_config['mysql_encoding']);
define('MARIADB_DB', $db_config['mariadb_db']);
define('MARIADB_HOST', $db_config['mariadb_host']);
define('MARIADB_PORT', $db_config['mariadb_port']);
define('MARIADB_USER', $db_config['mariadb_user']);
define('MARIADB_PASS', $db_config['mariadb_pass']);
define('MARIADB_ENCODING', $db_config['mariadb_encoding']);
define('POSTGRESQL_DB', $db_config['postgresql_db']);
define('POSTGRESQL_HOST', $db_config['postgresql_host']);
define('POSTGRESQL_PORT', $db_config['postgresql_port']);
define('POSTGRESQL_USER', $db_config['postgresql_user']);
define('POSTGRESQL_PASS', $db_config['postgresql_password']);
define('POSTGRESQL_SCHEMA', $db_config['postgresql_schema']);
define('POSTGRESQL_ENCODING', $db_config['postgresql_encoding']);
define('SQLITE_DB_NAME', $db_config['sqlite_db_name']);
define('SQLITE_DB_PATH', $db_config['sqlite_db_path']);
define('SQLITE_ENCODING', $db_config['sqlite_encoding']);
define('NEL_PASSWORD_PREFERRED_ALGORITHM', $crypt_config['password_algorithm']);
define('NEL_PASSWORD_BCRYPT_COST', $crypt_config['password_bcrypt_cost']);
define('NEL_PASSWORD_ARGON2_MEMORY_COST', $crypt_config['password_argon2_memory_cost']);
define('NEL_PASSWORD_ARGON2_TIME_COST', $crypt_config['password_argon2_time_cost']);
define('NEL_PASSWORD_ARGON2_THREADS', $crypt_config['password_argon2_threads']);

$setup = new \Nelliel\Setup\Setup();
$setup->checkGenerated();

require_once CONFIG_FILE_PATH . 'generated.php';

define('TRIPCODE_PEPPER', $generated['tripcode_pepper']);

unset($generated);
unset($base_config);
unset($db_config);
unset($crypt_config);