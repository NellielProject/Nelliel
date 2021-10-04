<?php
define('NELLIEL_VERSION', 'v0.9.24'); // Version
define('NELLIEL_COPYRIGHT', '2010-2021 Nelliel Project'); // Copyright line
define('NELLIEL_PACKAGE', 'Nelliel'); // Package
define('NELLIEL_PHP_MINIMUM', '7.2.0'); // Minimum PHP version

define('NEL_BASE_PATH', realpath('.') . '/'); // Base path where imgboard.php resides

$core_path = '.';

if(file_exists('nelliel_base.php'))
{
    include 'nelliel_base.php'; // Provides a custom path to the core directory
}

define('NEL_CORE_DIRECTORY', 'nelliel_core'); // Core directory name
define('NEL_CORE_PATH', realpath($core_path) . '/' . NEL_CORE_DIRECTORY . '/'); // Path to the core directory
unset($core_path);

define('NEL_INCLUDE_PATH', NEL_CORE_PATH . 'include/'); // Base include files path

$dirname = pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);

// When running at web root $dirname would result in // which has special meaning and all the URLs are fucked
$dirname = ($dirname === '/') ? '' : $dirname;
define('NEL_BASE_WEB_PATH', $dirname . '/');
unset($dirname);

require_once NEL_INCLUDE_PATH . 'definitions.php'; // Hard-coded constants are defined here
require_once NEL_INCLUDE_PATH . 'autoload.php'; // Autoloader
require_once NEL_INCLUDE_PATH . 'accessors.php'; //  Utility functions for accessing various things
require_once NEL_LIBRARY_PATH . 'portable-utf8/portable-utf8.php'; // UTF-8 support
require_once NEL_INCLUDE_PATH . 'derp.php'; // Error handler
require_once NEL_INCLUDE_PATH . 'initializations.php'; // Most config and other initialization happens in here

// IT'S GO TIME!
ignore_user_abort(true);

$dispatch_preparation = new \Nelliel\Dispatch\Preparation();
$dispatch_preparation->prepare();
nel_clean_exit();
