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
define('ICON_SETS_DIR', 'icon_sets');
define('IMAGES_DIR', 'imagez');
define('TEMPLATES_DIR', 'templates');
define('SCRIPTS_DIR', 'scripts');
define('FONTS_DIR', 'fonts');
define('CORE_DIR', 'core');
define('CUSTOM_DIR', 'custom');

if($_SERVER['SERVER_PORT'] != 80 && empty($_SERVER['HTTPS']))
{
    define('BASE_DOMAIN', $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT']);
}
else
{
    define('BASE_DOMAIN', $_SERVER['SERVER_NAME']);
}

define('MAIN_SCRIPT', 'imgboard.php');
define('MAIN_INDEX', 'index');
define('PAGE_EXT', '.html');
define('JSON_EXT', '.json');

define('ASSETS_TABLE', 'nelliel_assets');
define('BANS_TABLE', 'nelliel_bans');
define('BOARD_DATA_TABLE', 'nelliel_board_data');
define('BOARD_DEFAULTS_TABLE', 'nelliel_board_defaults');
define('CAPTCHA_TABLE', 'nelliel_captcha');
define('CITES_TABLE', 'nelliel_cites');
define('FILE_FILTERS_TABLE', 'nelliel_file_filters');
define('FILETYPES_TABLE', 'nelliel_filetypes');
define('LOGIN_ATTEMPTS_TABLE', 'nelliel_login_attempts');
define('NEWS_TABLE', 'nelliel_news');
define('PERMISSIONS_TABLE', 'nelliel_permissions');
define('REPORTS_TABLE', 'nelliel_reports');
define('ROLE_PERMISSIONS_TABLE', 'nelliel_role_permissions');
define('ROLES_TABLE', 'nelliel_roles');
define('SITE_CONFIG_TABLE', 'nelliel_site_config');
define('STAFF_LOGS_TABLE', 'nelliel_staff_logs');
define('SYSTEM_LOGS_TABLE', 'nelliel_system_logs');
define('TEMPLATES_TABLE', 'nelliel_templates');
define('USER_ROLES_TABLE', 'nelliel_user_roles');
define('USERS_TABLE', 'nelliel_users');
define('VERSIONS_TABLE', 'nelliel_version');

define('ASSETS_CORE_FILE_PATH', BASE_PATH . ASSETS_DIR . '/' . CORE_DIR . '/');
define('ASSETS_CUSTOM_FILE_PATH', BASE_PATH . ASSETS_DIR . '/' . CUSTOM_DIR . '/');
define('ASSETS_FILE_PATH', BASE_PATH . ASSETS_DIR . '/');
define('CONFIG_FILE_PATH', NELLIEL_CORE_PATH . 'configuration/');
define('CACHE_FILE_PATH', NELLIEL_CORE_PATH . 'cache/');
define('CORE_TEMPLATES_FILE_PATH', BASE_PATH . TEMPLATES_DIR . '/' . CORE_DIR . '/');
define('CUSTOM_TEMPLATES_FILE_PATH', BASE_PATH . TEMPLATES_DIR . '/' . CUSTOM_DIR . '/');
define('CORE_FONTS_FILE_PATH', ASSETS_CORE_FILE_PATH . FONTS_DIR . '/');
define('CUSTOM_FONTS_FILE_PATH', ASSETS_CUSTOM_FILE_PATH . FONTS_DIR . '/');
define('GENERATED_FILE_PATH', NELLIEL_CORE_PATH . 'generated/');
define('PLUGINS_FILE_PATH', NELLIEL_CORE_PATH . 'plugins/');
define('LANGUAGES_FILE_PATH', NELLIEL_CORE_PATH . 'languages/');
define('LOCALE_FILE_PATH', LANGUAGES_FILE_PATH . 'locale/');
define('CORE_STYLES_FILE_PATH', ASSETS_CORE_FILE_PATH . STYLES_DIR . '/');
define('CUSTOM_STYLES_FILE_PATH', ASSETS_CUSTOM_FILE_PATH .STYLES_DIR . '/');
define('CORE_ICON_SETS_FILE_PATH', ASSETS_CORE_FILE_PATH . ICON_SETS_DIR . '/');
define('CUSTOM_ICON_SETS_FILE_PATH', ASSETS_CUSTOM_FILE_PATH . ICON_SETS_DIR . '/');
define('WAT_FILE_PATH', INCLUDE_PATH . 'wat/');

define('ASSETS_CORE_WEB_PATH', ASSETS_DIR . '/' . CORE_DIR . '/');
define('ASSETS_CUSTOM_WEB_PATH', ASSETS_DIR . '/' . CUSTOM_DIR . '/');
define('CORE_SCRIPTS_WEB_PATH', ASSETS_CORE_WEB_PATH . SCRIPTS_DIR . '/');
define('CUSTOM_SCRIPTS_WEB_PATH', ASSETS_CUSTOM_WEB_PATH . SCRIPTS_DIR . '/');
define('CORE_IMAGES_WEB_PATH', ASSETS_CORE_WEB_PATH . IMAGES_DIR . '/');
define('CUSTOM_IMAGES_WEB_PATH', ASSETS_CUSTOM_WEB_PATH . IMAGES_DIR . '/');
define('CORE_STYLES_WEB_PATH', ASSETS_CORE_WEB_PATH . STYLES_DIR . '/');
define('CUSTOM_STYLES_WEB_PATH', ASSETS_CUSTOM_WEB_PATH . STYLES_DIR . '/');
define('CORE_ICON_SETS_WEB_PATH', ASSETS_CORE_WEB_PATH . ICON_SETS_DIR . '/');
define('CUSTOM_ICON_SETS_WEB_PATH', ASSETS_CUSTOM_WEB_PATH . ICON_SETS_DIR . '/');
define('BASE_WEB_PATH', pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME) . '/');
define('SQLITE_DB_DEFAULT_PATH', NELLIEL_CORE_PATH);

define('BASE_HONEYPOT_FIELD1', 'display_signature'); // Honeypot field name
define('BASE_HONEYPOT_FIELD2', 'signature'); // Honeypot field name
define('BASE_HONEYPOT_FIELD3', 'website'); // Honeypot field name

define('DEFAULT_TEXTDOMAIN_BIND', LANGUAGES_FILE_PATH . 'locale');

define('OVER_9000', 9001);

// Set default values here in case the config is missing something
$base_config['defaultadmin'] = '';
$base_config['defaultadmin_pass'] = '';
$base_config['tripcode_pepper'] = 'sodiumz';
$base_config['directory_perm'] = '0775';
$base_config['file_perm'] = '0664';
$base_config['use_internal_cache'] = true;
$base_config['default_locale'] = 'en_US';
$base_config['enable_plugins'] = true;
$base_config['secure_session_only'] = false;
$db_config = array();
$crypt_config['password_algorithm'] = 'BCRYPT';
$crypt_config['password_bcrypt_cost'] = 12;
$crypt_config['argon2_memory_cost'] = 1024;
$crypt_config['argon2_time_cost'] = 2;
$crypt_config['argon2_threads'] = 2;

require_once CONFIG_FILE_PATH . 'config.php';

define('SUPER_ADMIN', $base_config['super_admin']);
define('SUPER_ADMIN_PASS', $base_config['super_admin_pass']);
define('DIRECTORY_PERM', $base_config['directory_perm']);
define('FILE_PERM', $base_config['file_perm']);
define('USE_INTERNAL_CACHE', $base_config['use_internal_cache']);
define('USE_MUSTACHE_CACHE', $base_config['use_mustache_cache']);
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

if(file_exists(GENERATED_FILE_PATH . 'generated.php'))
{
    $generated = array();
    include_once GENERATED_FILE_PATH . 'generated.php';
    define('TRIPCODE_PEPPER', $generated['tripcode_pepper']);
    define('IP_PEPPER', $generated['ip_pepper']);
    define('POSTER_ID_PEPPER', $generated['poster_id_pepper']);
    define('POST_PASSWORD_PEPPER', $generated['post_password_pepper']);
    unset($generated);
}

unset($base_config);
unset($db_config);
unset($crypt_config);