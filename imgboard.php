<?php
define('NELLIEL_VERSION', 'v0.9.7.1'); // Version
define('NELLIEL_COPYRIGHT', '2010-2018 Nelliel Project'); // Copyright line
define('NELLIEL_PACKAGE', 'Nelliel'); // Package
define('BASE_PATH', realpath('./') . '/'); // Base path for script
define('FILES_PATH', BASE_PATH . 'board_files/'); // Base board files path
define('INCLUDE_PATH', FILES_PATH . 'include/'); // Base include files path
define('CONFIG_PATH', BASE_PATH . 'configuration/'); // Base cache path
define('LIBRARY_PATH', FILES_PATH . 'libraries/'); // Libraries path
define('PLUGINS_PATH', FILES_PATH . 'plugins/'); // Base plugins path
define('TEMPLATE_PATH', FILES_PATH . 'templates/nelliel/'); // Base template path
define('LANGUAGE_PATH', FILES_PATH . 'languages/'); // Language path
define('LOCALE_PATH', LANGUAGE_PATH . 'locale/'); // Locale files path
define('CACHE_PATH', FILES_PATH . 'cache/'); // Base cache path
define('WEB_PATH', BASE_PATH . 'web/'); // Base cache path
define('SQLITE_DB_DEFAULT_PATH', FILES_PATH); // Base SQLite DB location

require_once INCLUDE_PATH . 'autoload.php';
require_once LIBRARY_PATH . 'portable-utf8/portable-utf8.php';
require_once LIBRARY_PATH . 'password_compat/lib/password.php';
require_once LIBRARY_PATH . 'random_compat/lib/random.php';
require_once INCLUDE_PATH . 'initializations.php';
require_once INCLUDE_PATH . 'accessors.php';

nel_language()->loadLanguage(LOCALE_PATH . DEFAULT_LOCALE . '/LC_MESSAGES/nelliel.po');

require_once INCLUDE_PATH . 'database.php';
require_once INCLUDE_PATH . 'general_functions.php';
require_once INCLUDE_PATH . 'derp.php';
require_once INCLUDE_PATH . 'crypt.php';

nel_set_password_algorithm(NEL_PASSWORD_PREFERRED_ALGORITHM);

if (RUN_SETUP_CHECK)
{
    $setup = new \Nelliel\setup\Setup();
    $board_id = (isset($_GET['board_id'])) ? $_GET['board_id'] : '';
    $setup->checkAll($board_id);
}

require_once INCLUDE_PATH . 'output/header.php';
require_once INCLUDE_PATH . 'output/footer.php';

if (nel_setup_stuff_done())
{
    if (USE_INTERNAL_CACHE)
    {
        $regen = new \Nelliel\Regen();
        $regen->siteCache();
    }
}

nel_plugins()->initializePlugins();

// A demo point. Does nothing.
nel_plugins()->processHook('plugin-example', array(5));
$out = nel_plugins()->processHook('plugin-example-return', array('string'), 5);

require_once INCLUDE_PATH . 'snacks.php';

// IT'S GO TIME!
ignore_user_abort(true);
nel_ban_spambots();

require_once INCLUDE_PATH . 'dispatch/central_dispatch.php';

nel_central_dispatch();
nel_clean_exit();

