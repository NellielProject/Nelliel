<?php
define('NELLIEL_VERSION', 'v0.9.18'); // Version
define('NELLIEL_COPYRIGHT', '2010-2019 Nelliel Project'); // Copyright line
define('NELLIEL_PACKAGE', 'Nelliel'); // Package
define('BASE_PATH', realpath('./') . '/'); // Base path for script
define('FILES_PATH', BASE_PATH . 'board_files/'); // Base board files path
define('INCLUDE_PATH', FILES_PATH . 'include/'); // Base include files path
define('LIBRARY_PATH', FILES_PATH . 'libraries/'); // Libraries path

require_once INCLUDE_PATH . 'autoload.php';
require_once INCLUDE_PATH . 'initializations.php';
require_once LIBRARY_PATH . 'portable-utf8/portable-utf8.php';
require_once LIBRARY_PATH . 'Mustache/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();
$language = new \Nelliel\Language\Language();
$language->loadLanguage(DEFAULT_LOCALE, 'nelliel', LC_MESSAGES);
unset($language);

require_once INCLUDE_PATH . 'derp.php';
require_once INCLUDE_PATH . 'database.php';
require_once INCLUDE_PATH . 'accessors.php';

$plugins_api = new \Nelliel\API\Plugin\PluginAPI();
$plugins_api->loadPlugins();
unset($plugins_api);

// Example plugin hooks
nel_plugins()->processHook('nel-plugin-example', array(5));
$out = nel_plugins()->processHook('nel-plugin-example-return', array('string'), 5);
unset($out);

require_once INCLUDE_PATH . 'general_functions.php';

// Check if we're just returning a CAPTCHA image
if(isset($_GET['get-captcha']))
{
    nel_get_captcha();
}

require_once INCLUDE_PATH . 'crypt.php';
nel_set_password_algorithm(NEL_PASSWORD_PREFERRED_ALGORITHM);

$setup = new \Nelliel\Setup\Setup();

if(isset($_GET['install']))
{
    $setup->install();
}

if(!$setup->checkInstallDone())
{
    nel_derp(107, _gettext('Installation has not been done yet or is not complete.'));
}

unset ($setup);

// IT'S GO TIME!
define('SETUP_GOOD', true);
ignore_user_abort(true);

require_once CONFIG_FILE_PATH . 'generated.php';
require_once INCLUDE_PATH . 'dispatch/central_dispatch.php';

nel_central_dispatch();
nel_clean_exit();
