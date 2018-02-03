<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// This holds some basic configuration needed for Nelliel to function.
// Board-specific config can be found in the management section or board-config.php.
//

// The home location/portal
// This can be a full URL or a relative URL targeting a web-accessible directory.
// Defaults to the parent directory of the imageboard.
define('HOME', '../');


// If on a new install or no users are found, it will use DEFAULTADMIN and DEFAULTADMIN_PASS to create a default admin.
// Once able to login you can then set up other staff and even delete the default user if desired.
// Once the default admin account is created, make sure to set both of these back to ''
define('DEFAULTADMIN', ''); // Sets a default admin with all permissions
define('DEFAULTADMIN_PASS', ''); // Password for default admin


// Salt used for secure tripcodes
// Change this setting ONCE when you begin your board. Changing it again will alter the tripcode output
define('TRIPCODE_SALT', 'sodiumz');


// Should Nelliel go through the setup check
// Basically this runs through the setup sequence and checks that files, directories and database stuff is in place.
// Once initial setup and testing is finished this isn't really necessary and should be set to false.
// Note: Upgrades will run relevant setup checks regardless of this setting.
define('RUN_SETUP_CHECK', true);


//
// Default file and directory permissions. There shouldn't be any need to change these.
// If they are changed, format must be in the proper octal format.
//

define('DIRECTORY_PERM', '0775'); // Default permissions given to directories
define('FILE_PERM', '0664'); // Default permissions given to files