<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// This holds some basic configuration needed for Nelliel to function.
// Board-specific config can be found in the management section or board-config.php.
//

// If on a new install or no users are found, it will use DEFAULTADMIN and DEFAULTADMIN_PASS to create an admin account.
// Once created the default admin can be used for the rest of setup, managing staff, etc.
// Once the default admin account is created, make sure to set both of these back to ''
define('DEFAULTADMIN', ''); // Sets a default admin with all permissions
define('DEFAULTADMIN_PASS', ''); // Password for default admin


// Salt used for secure tripcodes
// Change this setting ONCE when you do initial setup. Changing it again will alter the secure tripcode output.
define('TRIPCODE_SALT', 'sodiumz');


// Each time the script is run, this runs through the setup sequence and checks that files, directories and database stuff is in place.
// Once initial setup and testing is finished this isn't really necessary and should be set to false.
define('RUN_SETUP_CHECK', true);


//
// Default file and directory permissions. There shouldn't be any need to change these.
// If they are changed, format must be in the proper octal format.
//

define('DIRECTORY_PERM', '0775'); // Default permissions given to directories
define('FILE_PERM', '0664'); // Default permissions given to files