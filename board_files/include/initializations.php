<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

define('INPUT_BOARD_ID', isset($_POST['board_id']) ? $_POST['board_id'] : '');
define('WEB_FILES', 'web/'); // Name of directory where the support and internal files go
define('IMAGES_DIR', WEB_FILES . 'imagez/'); // Web location of the javascript files
define('CSS_DIR', WEB_FILES . 'css/'); // Web location of the css files
define('JS_DIR', WEB_FILES . 'js/'); // Web location of the javascript files

define('PHP_SELF', 'imgboard.php'); // Name of main script file
define('PHP_SELF2', 'index'); // Name of board index
define('PHP_EXT', '.html'); // Extension used for board pages

define('BAN_TABLE', 'nelliel_bans'); // Table containing ban info
define('USER_TABLE', 'nelliel_users'); // Table used for post data
define('ROLES_TABLE', 'nelliel_roles'); // Table used for post data
define('USER_ROLE_TABLE', 'nelliel_user_role'); // Table used for post data
define('PERMISSIONS_TABLE', 'nelliel_permissions'); // Table used for post data
define('LOGINS_TABLE', 'nelliel_login_attempts'); // Table used for post data
define('BOARD_DATA_TABLE', 'nelliel_board_data'); // Table used for post data
define('SITE_CONFIG_TABLE', 'nelliel_site_config'); // Table containing site-wide config

require_once CONFIG_PATH . 'config.php';

define('DEFAULTADMIN', $base_config['defaultadmin']);
define('DEFAULTADMIN_PASS', $base_config['defaultadmin_pass']);
define('TRIPCODE_SALT', $base_config['tripcode_salt']);
define('RUN_SETUP_CHECK', (bool)$base_config['run_setup_check']);
define('DIRECTORY_PERM', $base_config['directory_perm']);
define('FILE_PERM', $base_config['file_perm']);
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