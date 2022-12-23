<?php
declare(strict_types = 1);

define('NELLIEL_VERSION', 'v0.9.29'); // Version
define('NELLIEL_COPYRIGHT', '2010-2022 Nelliel Project'); // Copyright line
define('NELLIEL_PACKAGE', 'Nelliel'); // Package
define('NELLIEL_PHP_MINIMUM', '7.2.0'); // Minimum PHP version

define('NEL_PUBLIC_PATH', realpath('.') . '/'); // Base path where imgboard.php resides

$core_path = '../'; // Path to core directory

// For custom file paths from imgboard.php to core and other critial areas
if (file_exists('nelliel_base.php')) {
    include 'nelliel_base.php';
}

define('NEL_BASE_PATH', realpath($core_path) . '/'); // Base path where project resides
define('NEL_CORE_DIRECTORY', 'core'); // Core directory name
define('NEL_CORE_PATH', NEL_BASE_PATH . NEL_CORE_DIRECTORY . '/'); // Path to the core directory
unset($core_path);

define('NEL_INCLUDE_PATH', NEL_CORE_PATH . 'include/'); // Base include files path

$dirname = pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME); // Get path info for the location of imgboard.php
$dirname = ($dirname === '/') ? '' : $dirname; // When running at web root the base path would end up being // which has special meaning and then all the URLs are fucked
define('NEL_BASE_WEB_PATH', $dirname . '/'); // Base path
unset($dirname);

require_once NEL_INCLUDE_PATH . 'definitions.php'; // Hard-coded constants are defined here
require_once NEL_LIBRARY_PATH . 'portable-utf8/portable-utf8.php'; // UTF-8 support
require_once NEL_INCLUDE_PATH . 'autoload.php'; // Autoloaders
require_once NEL_INCLUDE_PATH . 'configuration.php'; // Initialize core configurations
require_once NEL_INCLUDE_PATH . 'accessors.php'; // Utility functions for accessing various things

// Initialize language handlers
$language = new Nelliel\Language\Language();
$language->loadLanguage(NEL_DEFAULT_LOCALE, 'nelliel', LC_MESSAGES);
unset($language);

require_once NEL_INCLUDE_PATH . 'derp.php'; // Error handler
require_once NEL_INCLUDE_PATH . 'initializations.php'; // Any remaining initialization and checks happens in here

// IT'S GO TIME!
ignore_user_abort(true); // From this point on we want to handle any exits cleanly

// Hand off control to the dispatch functions
$dispatch_preparation = new \Nelliel\Dispatch\Start();
$dispatch_preparation->startDispatch();

nel_clean_exit(); // All done!
