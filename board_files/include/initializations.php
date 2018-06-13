<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

if (ini_get('date.timezone') === '')
{
    date_default_timezone_set('UTC');
}

define('INPUT_BOARD_ID', isset($_POST['board_id']) ? $_POST['board_id'] : ''); // Default board id setting
define('WEB_FILES', 'web/'); // Directory for CSS, Javascript and other web-related support files
define('IMAGES_DIR', WEB_FILES . 'imagez/'); // Images used by Nelliel go here
define('CSS_DIR', WEB_FILES . 'css/'); // CSS files here
define('JS_DIR', WEB_FILES . 'js/'); // Javascript files here

define('PHP_SELF', 'imgboard.php'); // Name of main script file
define('PHP_SELF2', 'index'); // Name of board index
define('PHP_EXT', '.html'); // Extension used for board pages

define('BAN_TABLE', 'nelliel_bans'); // Contains ban info
define('USER_TABLE', 'nelliel_users'); // Table of users
define('ROLES_TABLE', 'nelliel_roles'); // Table of roles
define('USER_ROLE_TABLE', 'nelliel_user_role'); // Which role is assigned to a user
define('PERMISSIONS_TABLE', 'nelliel_permissions'); // Permissions
define('LOGINS_TABLE', 'nelliel_login_attempts'); // Record of failed login attempts
define('BOARD_DATA_TABLE', 'nelliel_board_data'); // Basic data on each board
define('SITE_CONFIG_TABLE', 'nelliel_site_config'); // Site-wide config settings
define('FILETYPE_TABLE', 'nelliel_filetypes'); // Site-wide filetypes
define('FILE_FILTER_TABLE', 'nelliel_file_filters'); // Site-wide file filters

// Set default values here in case the config is missing something
$base_config['defaultadmin'] = '';
$base_config['defaultadmin_pass'] = '';
$base_config['tripcode_salt'] = 'sodiumz';
$base_config['run_setup_check'] = true;
$base_config['directory_perm'] = '0775';
$base_config['file_perm'] = '0664';
$base_config['use_internal_cache'] = true;
$crypt_config['password_bcrypt_cost'] = 12;
$crypt_config['password_sha2_cost'] = 200000;

require_once CONFIG_PATH . 'config.php';

define('DEFAULTADMIN', $base_config['defaultadmin']);
define('DEFAULTADMIN_PASS', $base_config['defaultadmin_pass']);
define('TRIPCODE_SALT', $base_config['tripcode_salt']);
define('RUN_SETUP_CHECK', (bool)$base_config['run_setup_check']);
define('DIRECTORY_PERM', $base_config['directory_perm']);
define('FILE_PERM', $base_config['file_perm']);
define('USE_INTERNAL_CACHE', $base_config['use_internal_cache']);
define('SQLTYPE', $db_config['sqltype']);
define('MYSQL_DB', $db_config['mysql_db']);
define('MYSQL_HOST', $db_config['mysql_host']);
define('MYSQL_PORT', $db_config['mysql_port']);
define('MYSQL_USER', $db_config['mysql_user']);
define('MYSQL_PASS', $db_config['mysql_pass']);
define('MYSQL_ENCODING', $db_config['mysql_encoding']);
define('SQLITE_DB_NAME', $db_config['sqlite_db_name']);
define('SQLITE_DB_PATH', $db_config['sqlite_db_path']);
define('SQLITE_ENCODING', $db_config['sqlite_encoding']);
define('POSTGRES_DB', $db_config['postgres_db']);
define('POSTGRES_HOST', $db_config['postgres_host']);
define('POSTGRES_PORT', $db_config['postgres_port']);
define('POSTGRES_USER', $db_config['postgres_user']);
define('POSTGRES_PASS', $db_config['postgres_password']);
define('POSTGRES_SCHEMA', $db_config['postgres_schema']);
define('POSTGRES_ENCODING', $db_config['postgres_encoding']);
define('PASSWORD_BCRYPT_COST', $crypt_config['password_bcrypt_cost']);
define('PASSWORD_SHA2_COST', $crypt_config['password_sha2_cost']);