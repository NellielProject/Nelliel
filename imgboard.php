<?php
define('NELLIEL_VERSION', 'v0.9.19'); // Version
define('NELLIEL_COPYRIGHT', '2010-2020 Nelliel Project'); // Copyright line
define('NELLIEL_PACKAGE', 'Nelliel'); // Package
define('BASE_PATH', realpath('./') . '/'); // Base path for script
define('NELLIEL_CORE_PATH', BASE_PATH . 'nelliel_core/'); // Base board files path
define('INCLUDE_PATH', NELLIEL_CORE_PATH . 'include/'); // Base include files path
define('LIBRARY_PATH', NELLIEL_CORE_PATH . 'libraries/'); // Libraries path

require_once INCLUDE_PATH . 'autoload.php';
require_once INCLUDE_PATH . 'initializations.php';
require_once LIBRARY_PATH . 'portable-utf8/portable-utf8.php';
require_once INCLUDE_PATH . 'derp.php';
require_once INCLUDE_PATH . 'accessors.php';

nel_plugins()->loadPlugins();

// Example plugin hooks
nel_plugins()->processHook('nel-plugin-example', array(5));
$out = nel_plugins()->processHook('nel-plugin-example-return', array('string'), 5);
unset($out);

require_once INCLUDE_PATH . 'crypt.php';

nel_set_password_algorithm(NEL_PASSWORD_PREFERRED_ALGORITHM);

// IT'S GO TIME!
ignore_user_abort(true);

require_once INCLUDE_PATH . 'dispatch/central_dispatch.php';

nel_central_dispatch();
nel_clean_exit();
