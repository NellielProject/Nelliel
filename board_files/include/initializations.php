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

$config_array = parse_ini_file(CONFIG_PATH. 'config.php', true, INI_SCANNER_RAW);

define('DEFAULTADMIN', $config_array['General']['defaultadmin']);
define('DEFAULTADMIN_PASS', $config_array['General']['defaultadmin_pass']);
define('TRIPCODE_SALT', $config_array['General']['tripcode_salt']);
define('RUN_SETUP_CHECK', (bool)$config_array['General']['run_setup_check']);
define('DIRECTORY_PERM', $config_array['General']['directory_perm']);
define('FILE_PERM', $config_array['General']['file_perm']);
define('PASSWORD_BCRYPT_COST', $config_array['Crypt']['password_bcrypt_cost']);
define('PASSWORD_SHA2_COST', $config_array['Crypt']['password_sha2_cost']);