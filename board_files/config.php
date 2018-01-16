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
define('BOARD_DIRECTORY', ''); // Directory board will be in. Can be absolute location or relative to imgboard.php.
define('BOARD_ID', ''); // ID of board. Does not have to match name or directory of board.
define('CONF_BOARD_DIR', 'board'); // Name of the directory the imageboard is installed. Used for cookies and other things.
define('HOME', '../'); // Site home directory (up one level by default). Can be a web-accessible directory or a URL.


// If on a new install or no users are found, it will use DEFAULTADMIN and DEFAULTADMIN_PASS to create a default admin.
// Once able to login you can then set up other staff and even delete the default user if desired.
// Once the default admin account is created, make sure to set both of these back to ''
define('DEFAULTADMIN', ''); // Sets a default admin with all permissions
define('DEFAULTADMIN_PASS', ''); // Password for default admin


// Salt used for secure tripcodes
// Change this setting ONCE when you begin your board. Changing it again will alter the tripcode output
define('TRIPCODE_SALT', 'sodiumz');

// Should Nelliel  go through the setup check
// Basically this runs through the setup sequence and checks that files, directories and database stuff is in place.
// Highly recommend setting this to false once your initial setup and testing is done! It can generate unnecessary load.
define('RUN_SETUP_CHECK', true);

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

define('DIRECTORY_PERM', '0775'); // Default permissions given to directories
define('FILE_PERM', '0664'); // Default permissions given to files