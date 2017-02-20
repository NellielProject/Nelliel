<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// This holds the basic configuration needed for the imageboard to function.
// All other settings can be configured from the admin control panels.
//


// Database type. Supported types: MYSQL, SQLITE
define('SQLTYPE', 'SQLITE'); // Database type


// Set these if you plan to use MySQL.
define('MYSQL_DB', 'database'); // Name of database used by imageboard
define('MYSQL_HOST', 'localhost'); // SQL server address
define('MYSQL_USER', 'username'); // SQL user
define('MYSQL_PASS', 'password'); // SQL user's password


// Set this if you plan to use SQLite
// The filename can be an absolute path if you want the database file somewhere else on the server.
define('SQLITE_DB_NAME', 'nelliel.sqlite'); // Filename of SQLite database.


// Set the language; the corresponding file should be named lang.<language code>.php
// Default Nelliel language files follow the IETF codes.
define('BOARD_LANGUAGE', 'en-us');

// Be certain TABLEPREFIX and CONF_BOARD_DIR are unique for each board you set up!
define('TABLEPREFIX', 'nelliel'); // Prefix used for tables in the database.
define('CONF_BOARD_DIR', 'board'); // Name of the directory the imageboard is installed. Used for cookies and other things.
define('HOME', '../'); // Site home directory (up one level by default). Can be a web-accessible directory or a URL.


// If no auth file is found and these settings are not blank, it will use DEFAULTADMIN and DEFAULTADMIN_PASS to create a default admin.
// Once able to login you can then set up other staff and even delete the default user if desired.
// Once the default admin account is created, make sure to set both of these back to ''
define('DEFAULTADMIN', ''); // Sets a default admin with all permissions
define('DEFAULTADMIN_PASS', ''); // Password for default admin


// Salt used for secure tripcodes and with the fallback to basic hashed passwords if crypt isn't usable.
// Include numbers, letters and funny symbols for best results.
// Change this setting ONCE when you begin your board. Changing it again will break everything that uses it.
define('HASH_SALT', 'sodiumz');

//
// This section is for the various hashing functions.
// Generally these can be left alone.
//

// If a different hashing method or cost was used on something, then rehash it with the current settings.
define('DO_PASSWORD_REHASH', false);

// Whether to give password_hash PHP's PASSWORD_DEFAULT, which should pick the best algorithm available
// If set false we will try to use bcrypt specifically
define('USE_PASSWORD_DEFAULT', true);

// The cost used by bcrypt (if available)
// Default is 10; minimum is 04; maximum is 31
define('BCRYPT_COST', 10);

// In case bcrypt or a later PHP password option is not available,  Nelliel can fall back to SHA-2 crypts
// This is not as secure and must be enabled manually by setting SHA_FALLBACK true
define('SHA2_FALLBACK', false);

// The cost used when falling back to SHA512 or SHA256
// Default is 5000; minimum is 1000; maximum is 999999999
define('CRYPT_SHA_COST', 5000);

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


//
// Leave everything below this point alone unless you have a good reason to mess with it.
// It's largely internal things used by the code or database.
//


define('POST_TABLE', TABLEPREFIX . '_post'); // Table used for post data
define('THREAD_TABLE', TABLEPREFIX . '_thread'); // Table used for thread data
define('FILE_TABLE', TABLEPREFIX . '_file'); // Table used for file data
define('EXTERNAL_TABLE', TABLEPREFIX . '_external'); // Table used for external content
define('ARCHIVE_POST_TABLE', TABLEPREFIX . '_archive_post'); // Stores archived threads
define('ARCHIVE_THREAD_TABLE', TABLEPREFIX . '_archive_thread'); // Stores archived thread data
define('ARCHIVE_FILE_TABLE', TABLEPREFIX . '_archive_file'); // Stores archived file data
define('ARCHIVE_EXTERNAL_TABLE', TABLEPREFIX . '_archive_external'); // Stores archived external content
define('CONFIG_TABLE', TABLEPREFIX . '_config'); // Table to store board configuration. Best to leave it as-is unless you really need to change it
define('BAN_TABLE', TABLEPREFIX . '_ban'); // Table containing ban info


define('BASE_PATH', realpath('./')); // Base path for script
define('SQLITE_DB_LOCATION', BASE_PATH . '/' . BOARD_FILES); // Base SQLite DB location
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


?>
