<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// This holds the basic configuration needed for the imageboard to function
// All other settings can be configured from the admin control panels
//

// Database type. Supported types: MYSQL, SQLITE
define('SQLTYPE', 'SQLITE');

// Set these if you plan to use MySQL
define('MYSQL_DB', 'database'); // Name of database used by imageboard
define('MYSQL_HOST', 'localhost'); // SQL server address
define('MYSQL_USER', 'username'); // SQL user
define('MYSQL_PASS', 'password'); // SQL user's password
                           
// Set this if you plan to use SQLite
define('SQLITE_DB_NAME', 'nelliel.sqlite'); // Filename of SQLite database. Can be an absolute path if you want the database file somewhere else on the server
                                             
// Be certain TABLEPREFIX and CONF_BOARD_DIR are unique for each board you set up!
define('TABLEPREFIX', 'nelliel'); // Prefix used for tables in the database
define('CONF_BOARD_DIR', 'board'); // Name of the directory the imageboard is installed. Used for cookies and other things.
define('HOME', '../'); // Site home directory (up one level by default). Can be a web-accessible directory or a URL
                        
// If no auth file is found and these settings are not blank, it will use DEFAULTADMIN and DEFAULTADMIN_PASS to create a default admin.
// Once able to login you can then set up other staff and even delete the default user if desired.
// It is strongly recommended to set these two variables back to '' once you have an admin set up.
define('DEFAULTADMIN', ''); // Sets a default admin with all permissions
define('DEFAULTADMIN_PASS', ''); // Password for default admin
                                  
// Salt used for passwords and secure tripcodes. Include numbers, letters and funny symbols for best results.
// Change this setting ONCE when you begin your board. Changing it again will break all the post passwords and alter secure tripcodes.
define('HASH_SALT', 'sodiumz');

/* * * * * * * * * * */
// The settings below this point can be changed if you really want but it's probably just as well to leave them alone.
/* * * * * * * * * * */

define('SRC_DIR', 'src/'); // Image directory
define('THUMB_DIR', 'thumb/'); // Thumbnail directory
define('PAGE_DIR', 'threads/'); // Response page directory
define('ARCHIVE_DIR', 'archive/'); // Archive directory
define('PHP_SELF', 'imgboard.php'); // Name of main script file
define('PHP_SELF2', 'imgboard'); // Name of main html file
define('PHP_EXT', '.html'); // Extension used for board pages

define('POSTTABLE', TABLEPREFIX . '_post'); // Table used for file data
define('FILETABLE', TABLEPREFIX . '_file'); // Table used for file data
define('CONFIGTABLE', TABLEPREFIX . '_config'); // Table to store board configuration. Best to leave it as-is unless you really need to change it
define('ARCHIVETABLE', TABLEPREFIX . '_archive'); // Stores archived threads
define('ARCHIVEFILETABLE', TABLEPREFIX . '_archive_file'); // Stores archived file data
define('BANTABLE', TABLEPREFIX . '_ban'); // Table containing ban info

/* * * * * * * * * * */
// Leave everything below this point alone unless you have a good reason to mess with it.
// It's just internal stuff used in the code.
/* * * * * * * * * * */

define('BASE_PATH', realpath('./')); // Base path for script
define('SQLITE_DB_LOCATION', BASE_PATH . '/' . BOARD_FILES); // Base SQLite DB location
define('FILES_PATH', BASE_PATH . '/' . BOARD_FILES); // Base cache path
define('INCLUDE_PATH', BASE_PATH . '/' . BOARD_FILES . 'include/'); // Base cache path
define('TEMPLATE_PATH', BASE_PATH . '/' . BOARD_FILES . 'templates/nelliel/'); // Base template path
define('CSSDIR', BOARD_FILES . 'css/'); // location of the css files
define('CACHE_DIR', 'cache/'); // Cache directory, only used internally by Nelliel
define('CACHE_PATH', FILES_PATH . '/' . CACHE_DIR); // Base cache path
define('SRC_PATH', BASE_PATH . '/' . SRC_DIR); // Base src path
define('THUMB_PATH', BASE_PATH . '/' . THUMB_DIR); // Base thumbnail path
define('PAGE_PATH', BASE_PATH . '/' . PAGE_DIR); // Base page path
define('ARCHIVE_PATH', BASE_PATH . '/' . ARCHIVE_DIR); // Base archive path
define('ARC_SRC_PATH', BASE_PATH . '/' . ARCHIVE_DIR . SRC_DIR); // Archive src path
define('ARC_THUMB_PATH', BASE_PATH . '/' . ARCHIVE_DIR . THUMB_DIR); // Archive thumbnail path
define('ARC_PAGE_PATH', BASE_PATH . '/' . ARCHIVE_DIR . PAGE_DIR); // Archive page path

?>
