<?php
define('NELLIEL_VERSION', 'v0.9.4.12'); // Version
define('BASE_PATH', realpath('./') . '/'); // Base path for script
define('FILES_PATH', BASE_PATH . '/' . 'board_files/'); // Base board files path
define('INCLUDE_PATH', FILES_PATH . 'include/'); // Base include files path
define('CONFIG_PATH', BASE_PATH . 'configuration/'); // Base cache path
define('LIBRARY_PATH', FILES_PATH . 'libraries/'); // Libraries path
define('PLUGINS_PATH', FILES_PATH . 'plugins/'); // Base plugins path
define('TEMPLATE_PATH', FILES_PATH . 'templates/nelliel/'); // Base template path
define('LANGUAGE_PATH', FILES_PATH . 'languages/'); // Language files path
define('CACHE_PATH', FILES_PATH . 'cache/'); // Base cache path
define('WEB_PATH', BASE_PATH . '/web/'); // Base cache path
define('SQLITE_DB_DEFAULT_PATH', FILES_PATH); // Base SQLite DB location

require_once INCLUDE_PATH . 'autoload.php';
require_once LIBRARY_PATH . 'phpDOMExtend/autoload.php';
require_once LIBRARY_PATH . 'NellielTemplates/autoload.php';
require_once INCLUDE_PATH . 'initializations.php';
require_once INCLUDE_PATH . 'general_functions.php';
require_once INCLUDE_PATH . 'database.php';
require_once INCLUDE_PATH . 'cache_functions.php';
require_once INCLUDE_PATH . 'accessors.php';
require_once INCLUDE_PATH . 'setup/setup.php';

if(RUN_SETUP_CHECK)
{
    setup_check(INPUT_BOARD_ID);
}

require_once INCLUDE_PATH . 'language/language.php';
require_once INCLUDE_PATH . 'derp.php';
require_once INCLUDE_PATH . 'crypt.php';
nel_verfiy_hash_algorithm();

require_once INCLUDE_PATH . 'plugins.php';
$plugin_files = glob(PLUGINS_PATH . '*.nel.php');
$plugins = new nel_plugin_handler();

foreach ($plugin_files as $file)
{
    require_once $file;
}

$plugins->activate();

// A demo point. Does nothing, really
$example_result = $plugins->plugin_hook('plugin-example', TRUE, array(5));

require_once LIBRARY_PATH . 'portable-utf8/portable-utf8.php';
require_once INCLUDE_PATH . 'sessions.php';
require_once INCLUDE_PATH . 'snacks.php';

ignore_user_abort(true);

// IT'S GO TIME!
nel_ban_spambots();
nel_apply_ban(INPUT_BOARD_ID);

require_once INCLUDE_PATH . 'regen.php';
require_once INCLUDE_PATH . 'thread_functions.php';
require_once INCLUDE_PATH . 'admin/login.php';
require_once INCLUDE_PATH . 'dispatch/central_dispatch.php';

nel_central_dispatch();
nel_clean_exit();

