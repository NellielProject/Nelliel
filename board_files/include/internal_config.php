<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// This file is used internally by Nelliel for configuration.
// Settings here should not be changed without very good reason and doing so is not supported.
// Changes may be overwritten by updates as well.
//

if(isset($_POST['board_id']))
{
    define('INPUT_BOARD_ID', $_POST['board_id']);

    $dbh = nel_database();
    $prepared = $dbh->prepare('SELECT * FROM "nelliel_board_data" WHERE "board_id" = ?');
    $board_data = $dbh->executePreparedFetch($prepared, array(INPUT_BOARD_ID), PDO::FETCH_ASSOC);

    define('BOARD_DIR', $board_data['board_directory']);
    define('BOARD_PREFIX', $board_data['db_prefix']);
    define('BOARD_PATH', BASE_PATH . BOARD_DIR . '/'); // Base board path
}
else
{
    define('INPUT_BOARD_ID', '');
    define('BOARD_DIR', '');
    define('BOARD_PREFIX', '');
    define('BOARD_PATH', BASE_PATH . BOARD_DIR . '/'); // Base board path
}

define('WEB_FILES', 'web/'); // Name of directory where the support and internal files go
define('IMAGES_DIR', WEB_FILES . 'imagez/'); // Web location of the javascript files
define('CSS_DIR', WEB_FILES . 'css/'); // Web location of the css files
define('JS_DIR', WEB_FILES . 'js/'); // Web location of the javascript files

define('PHP_SELF', 'imgboard.php'); // Name of main script file
define('PHP_SELF2', 'imgboard'); // Name of main html file
define('PHP_EXT', '.html'); // Extension used for board pages
define('SRC_DIR', 'src/'); // Image directory
define('THUMB_DIR', 'thumb/'); // Thumbnail directory
define('PAGE_DIR', 'threads/'); // Response page directory
define('ARCHIVE_DIR', 'archive/'); // Archive directory

define('CONFIG_TABLE', BOARD_PREFIX . '_config'); // Table to store board configuration. Best to leave it as-is unless you really need to change it
define('BAN_TABLE', 'nelliel_bans'); // Table containing ban info
define('USER_TABLE', 'nelliel_users'); // Table used for post data
define('ROLES_TABLE', 'nelliel_roles'); // Table used for post data
define('USER_ROLE_TABLE', 'nelliel_user_role'); // Table used for post data
define('PERMISSIONS_TABLE', 'nelliel_permissions'); // Table used for post data
define('LOGINS_TABLE', 'nelliel_login_attempts'); // Table used for post data
define('BOARD_DATA_TABLE', 'nelliel_board_data'); // Table used for post data
