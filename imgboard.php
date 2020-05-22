<?php
define('NELLIEL_VERSION', 'v0.9.20'); // Version
define('NELLIEL_COPYRIGHT', '2010-2020 Nelliel Project'); // Copyright line
define('NELLIEL_PACKAGE', 'Nelliel'); // Package
define('NEL_BASE_PATH', realpath('.') . '/'); // Base path for script

$core_path = '.';

if(file_exists('nelliel_base.php'))
{
    include 'nelliel_base.php';
}

define('NEL_CORE_PATH', realpath($core_path) . '/nelliel_core/'); // Base board files path
unset($core_path);


define('NEL_INCLUDE_PATH', NEL_CORE_PATH . 'include/'); // Base include files path
define('NEL_LIBRARY_PATH', NEL_CORE_PATH . 'libraries/'); // Libraries path

require_once NEL_INCLUDE_PATH . 'autoload.php';
require_once NEL_INCLUDE_PATH . 'derp.php';
require_once NEL_INCLUDE_PATH . 'initializations.php';
require_once NEL_LIBRARY_PATH . 'portable-utf8/portable-utf8.php';
require_once NEL_INCLUDE_PATH . 'accessors.php';

nel_plugins()->loadPlugins();

// Example plugin hooks
nel_plugins()->processHook('nel-plugin-example', array(5));
$out = nel_plugins()->processHook('nel-plugin-example-return', array('string'), 5);
unset($out);

require_once NEL_INCLUDE_PATH . 'crypt.php';

nel_set_password_algorithm(NEL_PASSWORD_PREFERRED_ALGORITHM);

// IT'S GO TIME!
ignore_user_abort(true);

require_once NEL_INCLUDE_PATH . 'dispatch/central_dispatch.php';

nel_central_dispatch();
nel_clean_exit();
