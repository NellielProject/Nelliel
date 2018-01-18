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

//
// Database tables
//

define('POST_TABLE', INPUT_BOARD_ID . '_posts'); // Table used for post data
define('THREAD_TABLE', INPUT_BOARD_ID . '_threads'); // Table used for thread data
define('FILE_TABLE', INPUT_BOARD_ID . '_files'); // Table used for file data
define('EXTERNAL_TABLE', INPUT_BOARD_ID . '_external'); // Table used for external content
define('ARCHIVE_POST_TABLE', INPUT_BOARD_ID . '_archive_posts'); // Stores archived threads
define('ARCHIVE_THREAD_TABLE', INPUT_BOARD_ID . '_archive_threads'); // Stores archived thread data
define('ARCHIVE_FILE_TABLE', INPUT_BOARD_ID . '_archive_files'); // Stores archived file data
define('ARCHIVE_EXTERNAL_TABLE', INPUT_BOARD_ID . '_archive_external'); // Stores archived external content
define('CONFIG_TABLE', INPUT_BOARD_ID . '_config'); // Table to store board configuration. Best to leave it as-is unless you really need to change it
define('BAN_TABLE', 'nelliel_bans'); // Table containing ban info
define('USER_TABLE', 'nelliel_users'); // Table used for post data
define('ROLES_TABLE', 'nelliel_roles'); // Table used for post data
define('USER_ROLE_TABLE', 'nelliel_user_role'); // Table used for post data
define('PERMISSIONS_TABLE', 'nelliel_permissions'); // Table used for post data
define('LOGINS_TABLE', 'nelliel_login_attempts'); // Table used for post data
define('BOARD_DATA_TABLE', 'nelliel_board_data'); // Table used for post data
