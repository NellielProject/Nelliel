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

define('SQLITE_DB_DEFAULT_PATH', FILES_PATH); // Base SQLite DB location