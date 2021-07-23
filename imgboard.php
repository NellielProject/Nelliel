<?php
define('NELLIEL_VERSION', 'v0.9.23'); // Version
define('NELLIEL_COPYRIGHT', '2010-2021 Nelliel Project'); // Copyright line
define('NELLIEL_PACKAGE', 'Nelliel'); // Package
define('NELLIEL_PHP_MINIMUM', '7.2.0'); // Minimum PHP version

define('NEL_BASE_PATH', realpath('.') . '/'); // Base path for script

$core_path = '.';

if(file_exists('nelliel_base.php'))
{
    include 'nelliel_base.php';
}

define('NEL_CORE_PATH', realpath($core_path) . '/nelliel_core/'); // Base board files path
unset($core_path);

define('NEL_INCLUDE_PATH', NEL_CORE_PATH . 'include/'); // Base include files path

require_once NEL_INCLUDE_PATH . 'definitions.php';
require_once NEL_INCLUDE_PATH . 'autoload.php';
require_once NEL_INCLUDE_PATH . 'accessors.php';
require_once NEL_INCLUDE_PATH . 'derp.php';
require_once NEL_INCLUDE_PATH . 'initializations.php';
require_once NEL_LIBRARY_PATH . 'portable-utf8/portable-utf8.php';

nel_plugins()->loadPlugins();

require_once NEL_INCLUDE_PATH . 'crypt.php';

nel_set_password_algorithm(NEL_PASSWORD_PREFERRED_ALGORITHM);

// IT'S GO TIME!
ignore_user_abort(true);

$dispatch_preparation = new \Nelliel\Dispatch\Preparation();
$dispatch_preparation->prepare();
nel_clean_exit();
