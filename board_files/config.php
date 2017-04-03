<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// This holds the basic configuration needed for the imageboard to function.
// All other settings can be configured from the admin control panels.
//


// Set the language; the corresponding file should be named lang.<language code>.php
// Default Nelliel language files follow the IETF codes.
define('BOARD_LANGUAGE', 'en-us');

// Be certain CONF_BOARD_DIR are unique for each board you set up!
define('CONF_BOARD_DIR', 'board'); // Name of the directory the imageboard is installed. Used for cookies and other things.
define('HOME', '../'); // Site home directory (up one level by default). Can be a web-accessible directory or a URL.


// If no auth file is found and these settings are not blank, it will use DEFAULTADMIN and DEFAULTADMIN_PASS to create a default admin.
// Once able to login you can then set up other staff and even delete the default user if desired.
// Once the default admin account is created, make sure to set both of these back to ''
define('DEFAULTADMIN', ''); // Sets a default admin with all permissions
define('DEFAULTADMIN_PASS', ''); // Password for default admin


// Salt used for secure tripcodes
// Change this setting ONCE when you begin your board. Changing it again will alter the tripcode output
define('TRIPCODE_SALT', 'sodiumz');


//
// The settings below can be changed if you really want but there's not much point.
//


define('SRC_DIR', 'src/'); // Image directory
define('THUMB_DIR', 'thumb/'); // Thumbnail directory
define('PAGE_DIR', 'threads/'); // Response page directory
define('ARCHIVE_DIR', 'archive/'); // Archive directory
define('PHP_SELF', 'imgboard.php'); // Name of main script file
define('PHP_SELF2', 'imgboard'); // Name of main html file
define('PHP_EXT', '.html'); // Extension used for board pages

define('BASE_PATH', realpath('./')); // Base path for script
define('SQLITE_DB_DEFAULT_PATH', BASE_PATH . '/' . BOARD_FILES); // Base SQLite DB location
define('FILES_PATH', BASE_PATH . '/' . BOARD_FILES); // Base cache path
define('INCLUDE_PATH', BASE_PATH . '/' . BOARD_FILES . 'include/'); // Base cache path
define('PLUGINS_PATH', BASE_PATH . '/' . BOARD_FILES . 'plugins/'); // Base cache path
define('TEMPLATE_PATH', BASE_PATH . '/' . BOARD_FILES . 'templates/nelliel/'); // Base template path
define('LANGUAGE_PATH', BASE_PATH . '/' . BOARD_FILES . 'languages/'); // location of the language files
define('CSSDIR', BOARD_FILES . 'css/'); // location of the css files
define('JSDIR', BOARD_FILES . 'js/'); // location of the javascript files
define('CACHE_DIR', 'cache/'); // Cache directory, only used internally by Nelliel
define('CACHE_PATH', FILES_PATH . '/' . CACHE_DIR); // Base cache path
define('SRC_PATH', BASE_PATH . '/' . SRC_DIR); // Base src path
define('THUMB_PATH', BASE_PATH . '/' . THUMB_DIR); // Base thumbnail path
define('PAGE_PATH', BASE_PATH . '/' . PAGE_DIR); // Base page path
define('ARCHIVE_PATH', BASE_PATH . '/' . ARCHIVE_DIR); // Base archive path
define('ARC_SRC_PATH', BASE_PATH . '/' . ARCHIVE_DIR . SRC_DIR); // Archive src path
define('ARC_THUMB_PATH', BASE_PATH . '/' . ARCHIVE_DIR . THUMB_DIR); // Archive thumbnail path
define('ARC_PAGE_PATH', BASE_PATH . '/' . ARCHIVE_DIR . PAGE_DIR); // Archive page path