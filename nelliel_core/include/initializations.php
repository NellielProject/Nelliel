<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

define('NEL_OVER_9000', 9001);

if (ini_get('date.timezone') === '')
{
    date_default_timezone_set('UTC');
}

define('NEL_ASSETS_DIR', 'assets');
define('NEL_STYLES_DIR', 'styles');
define('NEL_ICON_SETS_DIR', 'icons');
define('NEL_IMAGES_DIR', 'imagez');
define('NEL_TEMPLATES_DIR', 'templates');
define('NEL_SCRIPTS_DIR', 'scripts');
define('NEL_FONTS_DIR', 'fonts');
define('NEL_CORE_DIR', 'core');
define('NEL_CUSTOM_DIR', 'custom');
define('NEL_GENERAL_DIR', '.nelliel');
define('NEL_CAPTCHA_DIR', 'captchas');

if ($_SERVER['SERVER_PORT'] != 80 && empty($_SERVER['HTTPS']))
{
    define('NEL_BASE_DOMAIN', $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT']);
}
else
{
    define('NEL_BASE_DOMAIN', $_SERVER['SERVER_NAME']);
}

define('NEL_MAIN_SCRIPT', 'imgboard.php');
define('NEL_MAIN_INDEX', 'index');
define('NEL_PAGE_EXT', '.html');
define('NEL_JSON_EXT', '.json');

define('NEL_ASSETS_TABLE', 'nelliel_assets');
define('NEL_BANS_TABLE', 'nelliel_bans');
define('NEL_BOARD_DATA_TABLE', 'nelliel_board_data');
define('NEL_BOARD_DEFAULTS_TABLE', 'nelliel_board_defaults');
define('NEL_CAPTCHA_TABLE', 'nelliel_captcha');
define('NEL_CITES_TABLE', 'nelliel_cites');
define('NEL_FILES_FILTERS_TABLE', 'nelliel_file_filters');
define('NEL_FILETYPES_TABLE', 'nelliel_filetypes');
define('NEL_LOGS_TABLE', 'nelliel_logs');
define('NEL_NEWS_TABLE', 'nelliel_news');
define('NEL_PERMISSIONS_TABLE', 'nelliel_permissions');
define('NEL_OVERBOARD_TABLE', 'nelliel_overboard');
define('NEL_RATE_LIMIT_TABLE', 'nelliel_rate_limit');
define('NEL_REPORTS_TABLE', 'nelliel_reports');
define('NEL_ROLE_PERMISSIONS_TABLE', 'nelliel_role_permissions');
define('NEL_ROLES_TABLE', 'nelliel_roles');
define('NEL_SETTINGS_TABLE', 'nelliel_settings');
define('NEL_SITE_CONFIG_TABLE', 'nelliel_site_config');
define('NEL_TEMPLATES_TABLE', 'nelliel_templates');
define('NEL_USER_ROLES_TABLE', 'nelliel_user_roles');
define('NEL_USERS_TABLE', 'nelliel_users');
define('NEL_VERSIONS_TABLE', 'nelliel_version');

define('NEL_ASSETS_CORE_FILES_PATH', NEL_BASE_PATH . NEL_ASSETS_DIR . '/' . NEL_CORE_DIR . '/');
define('NEL_ASSETS_CUSTOM_FILES_PATH', NEL_BASE_PATH . NEL_ASSETS_DIR . '/' . NEL_CUSTOM_DIR . '/');
define('NEL_ASSETS_FILES_PATH', NEL_BASE_PATH . NEL_ASSETS_DIR . '/');
define('NEL_CONFIG_FILES_PATH', NEL_CORE_PATH . 'configuration/');
define('NEL_CACHE_FILES_PATH', NEL_CORE_PATH . 'cache/');
define('NEL_CORE_TEMPLATES_FILES_PATH', NEL_BASE_PATH . NEL_TEMPLATES_DIR . '/' . NEL_CORE_DIR . '/');
define('NEL_CUSTOM_TEMPLATES_FILES_PATH', NEL_BASE_PATH . NEL_TEMPLATES_DIR . '/' . NEL_CUSTOM_DIR . '/');
define('NEL_CORE_FONTS_FILES_PATH', NEL_ASSETS_CORE_FILES_PATH . NEL_FONTS_DIR . '/');
define('NEL_CUSTOM_FONTS_FILES_PATH', NEL_ASSETS_CUSTOM_FILES_PATH . NEL_FONTS_DIR . '/');
define('NEL_GENERATED_FILES_PATH', NEL_CORE_PATH . 'generated/');
define('NEL_PLUGINS_FILES_PATH', NEL_CORE_PATH . 'plugins/');
define('NEL_LANGUAGES_FILES_PATH', NEL_CORE_PATH . 'languages/');
define('NEL_LOCALE_FILES_PATH', NEL_LANGUAGES_FILES_PATH . 'locale/');
define('NEL_CORE_STYLES_FILES_PATH', NEL_ASSETS_CORE_FILES_PATH . NEL_STYLES_DIR . '/');
define('NEL_CUSTOM_STYLES_FILES_PATH', NEL_ASSETS_CUSTOM_FILES_PATH . NEL_STYLES_DIR . '/');
define('NEL_CORE_ICON_SETS_FILES_PATH', NEL_ASSETS_CORE_FILES_PATH . NEL_ICON_SETS_DIR . '/');
define('NEL_CUSTOM_ICON_SETS_FILES_PATH', NEL_ASSETS_CUSTOM_FILES_PATH . NEL_ICON_SETS_DIR . '/');
define('NEL_WAT_FILES_PATH', NEL_INCLUDE_PATH . 'wat/');
define('NEL_GENERAL_FILES_PATH', NEL_BASE_PATH . NEL_GENERAL_DIR . '/');
define('NEL_CAPTCHA_FILES_PATH', NEL_GENERAL_FILES_PATH . NEL_CAPTCHA_DIR . '/');

define('NEL_ASSETS_CORE_WEB_PATH', NEL_ASSETS_DIR . '/' . NEL_CORE_DIR . '/');
define('NEL_ASSETS_CUSTOM_WEB_PATH', NEL_ASSETS_DIR . '/' . NEL_CUSTOM_DIR . '/');
define('NEL_CORE_SCRIPTS_WEB_PATH', NEL_ASSETS_CORE_WEB_PATH . NEL_SCRIPTS_DIR . '/');
define('NEL_CUSTOM_SCRIPTS_WEB_PATH', NEL_ASSETS_CUSTOM_WEB_PATH . NEL_SCRIPTS_DIR . '/');
define('NEL_CORE_IMAGES_WEB_PATH', NEL_ASSETS_CORE_WEB_PATH . NEL_IMAGES_DIR . '/');
define('NEL_CUSTOM_IMAGES_WEB_PATH', NEL_ASSETS_CUSTOM_WEB_PATH . NEL_IMAGES_DIR . '/');
define('NEL_CORE_STYLES_WEB_PATH', NEL_ASSETS_CORE_WEB_PATH . NEL_STYLES_DIR . '/');
define('NEL_CUSTOM_STYLES_WEB_PATH', NEL_ASSETS_CUSTOM_WEB_PATH . NEL_STYLES_DIR . '/');
define('NEL_CORE_ICON_SETS_WEB_PATH', NEL_ASSETS_CORE_WEB_PATH . NEL_ICON_SETS_DIR . '/');
define('NEL_CUSTOM_ICON_SETS_WEB_PATH', NEL_ASSETS_CUSTOM_WEB_PATH . NEL_ICON_SETS_DIR . '/');
define('NEL_GENERAL_WEB_PATH', NEL_GENERAL_DIR . '/');
define('NEL_CAPTCHA_WEB_PATH', NEL_GENERAL_WEB_PATH . NEL_CAPTCHA_DIR . '/');
define('NEL_BASE_WEB_PATH', pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME) . '/');
define('NEL_SQLITE_DB_DEFAULT_PATH', '../');

define('NEL_BASE_HONEYPOT_FIELD1', 'display_signature'); // Honeypot field name
define('NEL_BASE_HONEYPOT_FIELD2', 'signature'); // Honeypot field name
define('NEL_BASE_HONEYPOT_FIELD3', 'website'); // Honeypot field name

define('NEL_DEFAULT_TEXTDOMAIN_BIND', NEL_LANGUAGES_FILES_PATH . 'locale');

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

require_once NEL_CONFIG_FILES_PATH . 'config.php';

define('NEL_DIRECTORY_PERM', $base_config['directory_perm']);
define('NEL_FILES_PERM', $base_config['file_perm']);
define('NEL_USE_INTERNAL_CACHE', $base_config['use_internal_cache']);
define('NEL_USE_MUSTACHE_CACHE', $base_config['use_mustache_cache']);
define('NEL_DEFAULT_LOCALE', $base_config['default_locale']);
define('NEL_ENABLE_PLUGINS', $base_config['enable_plugins']);
define('NEL_SECURE_SESSION_ONLY', $base_config['secure_session_only']);
define('NEL_SQLTYPE', $db_config['sqltype']);
define('NEL_MYSQL_DB', $db_config['mysql_db']);
define('NEL_MYSQL_HOST', $db_config['mysql_host']);
define('NEL_MYSQL_PORT', $db_config['mysql_port']);
define('NEL_MYSQL_USER', $db_config['mysql_user']);
define('NEL_MYSQL_PASS', $db_config['mysql_pass']);
define('NEL_MYSQL_ENCODING', $db_config['mysql_encoding']);
define('NEL_MARIADB_DB', $db_config['mariadb_db']);
define('NEL_MARIADB_HOST', $db_config['mariadb_host']);
define('NEL_MARIADB_PORT', $db_config['mariadb_port']);
define('NEL_MARIADB_USER', $db_config['mariadb_user']);
define('NEL_MARIADB_PASS', $db_config['mariadb_pass']);
define('NEL_MARIADB_ENCODING', $db_config['mariadb_encoding']);
define('NEL_POSTGRESQL_DB', $db_config['postgresql_db']);
define('NEL_POSTGRESQL_HOST', $db_config['postgresql_host']);
define('NEL_POSTGRESQL_PORT', $db_config['postgresql_port']);
define('NEL_POSTGRESQL_USER', $db_config['postgresql_user']);
define('NEL_POSTGRESQL_PASS', $db_config['postgresql_password']);
define('NEL_POSTGRESQL_SCHEMA', $db_config['postgresql_schema']);
define('NEL_POSTGRESQL_ENCODING', $db_config['postgresql_encoding']);
define('NEL_SQLITE_DB_NAME', $db_config['sqlite_db_name']);
define('NEL_SQLITE_DB_PATH', $db_config['sqlite_db_path']);
define('NEL_SQLITE_ENCODING', $db_config['sqlite_encoding']);
define('NEL_PASSWORD_PREFERRED_ALGORITHM', $crypt_config['password_algorithm']);
define('NEL_PASSWORD_BCRYPT_COST', $crypt_config['password_bcrypt_cost']);
define('NEL_PASSWORD_ARGON2_MEMORY_COST', $crypt_config['password_argon2_memory_cost']);
define('NEL_PASSWORD_ARGON2_TIME_COST', $crypt_config['password_argon2_time_cost']);
define('NEL_PASSWORD_ARGON2_THREADS', $crypt_config['password_argon2_threads']);

$language = new \Nelliel\Language\Language();
$language->loadLanguage(NEL_DEFAULT_LOCALE, 'nelliel', LC_MESSAGES);
unset($language);
Mustache_Autoloader::register();

require_once NEL_INCLUDE_PATH . 'database.php';
require_once NEL_INCLUDE_PATH . 'general_functions.php';
$setup = new \Nelliel\Setup\Setup();

if (isset($_GET['install']))
{
    $setup->install();
}

if (!$setup->checkInstallDone())
{
    nel_derp(107, _gettext('Installation has not been done yet or is not complete.'));
}

if (file_exists(NEL_GENERATED_FILES_PATH . 'peppers.php'))
{
    $peppers = array();
    include_once NEL_GENERATED_FILES_PATH . 'peppers.php';
    define('NEL_TRIPCODE_PEPPER', $peppers['tripcode_pepper']);
    define('NEL_IP_PEPPER', $peppers['ip_pepper']);
    define('NEL_POSTER_ID_PEPPER', $peppers['poster_id_pepper']);
    define('NEL_POST_PASSWORD_PEPPER', $peppers['post_password_pepper']);
    unset($peppers);
}

unset($setup);
unset($base_config);
unset($db_config);
unset($crypt_config);

define('NEL_SETUP_GOOD', true);
