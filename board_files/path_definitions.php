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
}
else
{
    define('INPUT_BOARD_ID', '');
}


//
// Path definitions
//

define('CONFIG_PATH', BASE_PATH . 'configuration/'); // Base cache path
define('INCLUDE_PATH', FILES_PATH . 'include/'); // Base cache path
define('LIBRARY_PATH', FILES_PATH . 'libraries/'); // Libraries path
define('PLUGINS_PATH', FILES_PATH . 'plugins/'); // Base plugins path
define('TEMPLATE_PATH', FILES_PATH . 'templates/nelliel/'); // Base template path
define('LANGUAGE_PATH', FILES_PATH . 'languages/'); // Language files path
define('CACHE_PATH', FILES_PATH . 'cache/'); // Base cache path
define('WEB_PATH', FILES_PATH . 'web/'); // Base cache path
define('BOARD_PATH', BASE_PATH . INPUT_BOARD_ID. '/'); // Base board path

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

define('SRC_PATH', BOARD_PATH . SRC_DIR); // Base src path
define('THUMB_PATH', BOARD_PATH . THUMB_DIR); // Base thumbnail path
define('PAGE_PATH', BOARD_PATH . PAGE_DIR); // Base page path
define('ARCHIVE_PATH', BOARD_PATH . ARCHIVE_DIR); // Base archive path
define('ARC_SRC_PATH', BOARD_PATH . ARCHIVE_DIR . SRC_DIR); // Archive src path
define('ARC_THUMB_PATH', BOARD_PATH . ARCHIVE_DIR . THUMB_DIR); // Archive thumbnail path
define('ARC_PAGE_PATH', BOARD_PATH . ARCHIVE_DIR . PAGE_DIR); // Archive page path

define('SQLITE_DB_DEFAULT_PATH', FILES_PATH); // Base SQLite DB location