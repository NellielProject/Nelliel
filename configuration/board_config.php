<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// This holds the basic configuration needed for a specific board.
//


// Set the language; the corresponding file should be named lang.<language code>.php
// Default Nelliel language files follow the IETF codes.
define('BOARD_LANGUAGE', 'en-us');


// Subdirectory the board will be in. Can be absolute location or relative to imgboard.php.
// If desired, can be left empty for single-board installations.
define('BOARD_DIRECTORY', '');


// ID of board. Does not have to match name or directory of board.
// This is mostly used for internal identification and is permanent.
// Must consist of only letters, numbers and underscores.
define('BOARD_ID', '');