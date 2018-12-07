<?php
define('NELLIEL_VERSION', 'v0.9.12'); // Version
define('NELLIEL_COPYRIGHT', '2010-2018 Nelliel Project'); // Copyright line
define('NELLIEL_PACKAGE', 'Nelliel'); // Package
define('BASE_PATH', realpath('./') . '/'); // Base path for script
define('FILES_PATH', BASE_PATH . 'board_files/'); // Base board files path
define('INCLUDE_PATH', FILES_PATH . 'include/'); // Base include files path
define('CONFIG_PATH', BASE_PATH . 'configuration/'); // Base config path
define('LIBRARY_PATH', FILES_PATH . 'libraries/'); // Libraries path
define('PLUGINS_PATH', FILES_PATH . 'plugins/'); // Base plugins path
define('TEMPLATE_PATH', FILES_PATH . 'templates/nelliel/'); // Base template path
define('LANGUAGE_PATH', FILES_PATH . 'languages/'); // Language path
define('LOCALE_PATH', LANGUAGE_PATH . 'locale/'); // Locale files path
define('CACHE_PATH', FILES_PATH . 'cache/'); // Base cache path
define('WEB_PATH', BASE_PATH . 'web/'); // Base web path
define('SQLITE_DB_DEFAULT_PATH', FILES_PATH); // Base SQLite DB location

require_once INCLUDE_PATH . 'autoload.php';
require_once LIBRARY_PATH . 'portable-utf8/portable-utf8.php';
require_once LIBRARY_PATH . 'random_compat/lib/random.php';
require_once INCLUDE_PATH . 'initializations.php';
require_once INCLUDE_PATH . 'output/header.php';
require_once INCLUDE_PATH . 'output/footer.php';
require_once INCLUDE_PATH . 'derp.php';
require_once INCLUDE_PATH . 'accessors.php';
require_once INCLUDE_PATH . 'database.php';

$authorization = new \Nelliel\Auth\Authorization(nel_database());
$language = new \Nelliel\Language\Language($authorization);
$language->loadLanguage(LOCALE_PATH . DEFAULT_LOCALE . '/LC_MESSAGES/nelliel.po');

require_once INCLUDE_PATH . 'general_functions.php';
require_once INCLUDE_PATH . 'crypt.php';
nel_set_password_algorithm(NEL_PASSWORD_PREFERRED_ALGORITHM);

if (RUN_SETUP_CHECK)
{
    $setup = new \Nelliel\Setup\Setup();
    $board_id = (isset($_GET['board_id'])) ? $_GET['board_id'] : '';
    $setup->checkAll($board_id);
}

if (nel_setup_stuff_done())
{
    if (USE_INTERNAL_CACHE)
    {
        $regen = new \Nelliel\Regen();
        $regen->siteCache();
    }
}

nel_plugins()->loadPlugins();

// A demo point. Does nothing.
nel_plugins()->processHook('nel-plugin-example', array(5));
$out = nel_plugins()->processHook('nel-plugin-example-return', array('string'), 5);

// IT'S GO TIME!
ignore_user_abort(true);
$snacks = new \Nelliel\Snacks(nel_database(), new \Nelliel\BanHammer(nel_database()));
$snacks->banSpambots();

require_once INCLUDE_PATH . 'dispatch/central_dispatch.php';

nel_central_dispatch();
nel_clean_exit();

