<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

if (ini_get('date.timezone') === '')
{
    date_default_timezone_set('UTC');
}

define('WEB_FILES', 'web/'); // Directory for CSS, Javascript and other web-related support files
define('IMAGES_DIR', WEB_FILES . 'imagez'); // Images used by Nelliel go here
define('CSS_DIR', WEB_FILES . 'css'); // CSS files here
define('JS_DIR', WEB_FILES . 'js'); // Javascript files here

define('PHP_SELF', 'imgboard.php'); // Name of main script file
define('PHP_SELF2', 'index'); // Name of board index
define('PHP_EXT', '.html'); // Extension used for board pages
define('JSON_EXT', '.json'); // Extension used for board pages

define('BAN_TABLE', 'nelliel_bans'); // Contains ban info
define('USER_TABLE', 'nelliel_users'); // Table of users
define('ROLES_TABLE', 'nelliel_roles'); // Table of roles
define('USER_ROLE_TABLE', 'nelliel_user_role'); // Which role is assigned to a user
define('ROLE_PERMISSIONS_TABLE', 'nelliel_role_permissions'); // Permissions
define('PERMISSIONS_TABLE', 'nelliel_permissions'); // Permissions
define('LOGINS_TABLE', 'nelliel_login_attempts'); // Record of failed login attempts
define('BOARD_DATA_TABLE', 'nelliel_board_data'); // Basic data on each board
define('SITE_CONFIG_TABLE', 'nelliel_site_config'); // Site-wide config settings
define('FILETYPE_TABLE', 'nelliel_filetypes'); // Site-wide filetypes
define('FILE_FILTER_TABLE', 'nelliel_file_filters'); // Site-wide file filters
define('BOARD_DEFAULTS_TABLE', 'nelliel_board_defaults'); // Default config for new boards
define('REPORTS_TABLE', 'nelliel_reports'); // Content reports
define('STYLES_TABLE', 'nelliel_styles'); // Styles
define('TEMPLATE_TABLE', 'nelliel_templates'); // Templates
define('ICON_SET_TABLE', 'nelliel_icon_sets'); // Icon Sets
define('CAPTCHA_TABLE', 'nelliel_captcha'); // CAPTCHA data
define('VERSION_TABLE', 'nelliel_version'); // Version data

define('CONFIG_PATH', BASE_PATH . 'configuration/'); // Base config path
define('LIBRARY_PATH', FILES_PATH . 'libraries/'); // Libraries path
define('CACHE_PATH', FILES_PATH . 'cache/'); // Base cache path
define('WEB_PATH', BASE_PATH . WEB_FILES); // Base web path
define('TEMPLATE_PATH', FILES_PATH . 'templates/'); // Base template path
define('ICON_SET_PATH', WEB_PATH . 'icon_sets/'); // Base icon set path
define('FILETYPE_ICON_PATH', ICON_SET_PATH . 'filetype/'); // Base filetype icon set path
define('PLUGINS_PATH', FILES_PATH . 'plugins/'); // Base plugins path
define('LANGUAGE_PATH', FILES_PATH . 'languages/'); // Language path
define('LOCALE_PATH', LANGUAGE_PATH . 'locale/'); // Locale files path
define('SQLITE_DB_DEFAULT_PATH', FILES_PATH); // Base SQLite DB location

define('BASE_HONEYPOT_FIELD1', 'display_signature'); // Honeypot field name
define('BASE_HONEYPOT_FIELD2', 'signature'); // Honeypot field name
define('BASE_HONEYPOT_FIELD3', 'website'); // Honeypot field name

// Set default values here in case the config is missing something
$base_config['defaultadmin'] = '';
$base_config['defaultadmin_pass'] = '';
$base_config['tripcode_salt'] = 'sodiumz';
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

require_once CONFIG_PATH . 'config.php';

define('DEFAULTADMIN', $base_config['defaultadmin']);
define('DEFAULTADMIN_PASS', $base_config['defaultadmin_pass']);
define('TRIPCODE_SALT', $base_config['tripcode_salt']);
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