<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

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

define('BAN_TABLE', 'nelliel_bans'); // Table containing ban info
define('USER_TABLE', 'nelliel_users'); // Table used for post data
define('ROLES_TABLE', 'nelliel_roles'); // Table used for post data
define('USER_ROLE_TABLE', 'nelliel_user_role'); // Table used for post data
define('PERMISSIONS_TABLE', 'nelliel_permissions'); // Table used for post data
define('LOGINS_TABLE', 'nelliel_login_attempts'); // Table used for post data
define('BOARD_DATA_TABLE', 'nelliel_board_data'); // Table used for post data
define('SITE_CONFIG_TABLE', 'nelliel_site_config'); // Table containing site-wide config

$dataforce = array();
$dataforce['login_valid'] = false;
$dataforce['mode'] = (isset($_POST['mode'])) ? $_POST['mode'] : NULL;
$dataforce['get_mode'] = (isset($_GET['mode'])) ? $_GET['mode'] : NULL;
$dataforce['current_page'] = (isset($_GET['page'])) ? $_GET['page'] : NULL;
$dataforce['expand'] = (isset($_GET['expand'])) ? TRUE : FALSE;
$dataforce['collapse'] = (isset($_GET['collapse'])) ? TRUE : FALSE;
$dataforce['response_id'] = (isset($_GET['post']) && is_numeric($_GET['post'])) ? (int) $_GET['post'] : NULL;
