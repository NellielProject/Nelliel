<?php
define('NELLIEL_VERSION', 'v0.9.4.6'); // Version
define('BASE_PATH', realpath('./')); // Base path for script
define('BOARD_FILES', 'board_files/'); // Name of directory where the support and internal files go
define('INCLUDE_PATH', BASE_PATH . '/' . BOARD_FILES . 'include/'); // Base cache path
require_once BOARD_FILES . 'config.php';
require_once BOARD_FILES . 'crypt-config.php';
require_once BOARD_FILES . 'database-config.php';
require_once INCLUDE_PATH . 'defines.php';
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

require_once LIBRARY_PATH . 'phpDOMExtend/autoload.php';
require_once LIBRARY_PATH . 'NellielTemplates/autoload.php';
require_once INCLUDE_PATH . 'autoload.php';
require_once INCLUDE_PATH . 'database.php';
require_once INCLUDE_PATH . 'accessors.php';
require_once INCLUDE_PATH . 'general-functions.php';
require_once INCLUDE_PATH . 'initializations.php';
require_once INCLUDE_PATH . 'snacks.php';

// IT'S GO TIME!
nel_apply_ban($dataforce);
nel_ban_spambots($dataforce);

require_once INCLUDE_PATH . 'regen.php';
require_once INCLUDE_PATH . 'thread-functions.php';
require_once INCLUDE_PATH . 'admin/login.php';
require_once INCLUDE_PATH . 'sessions.php';
require_once INCLUDE_PATH . 'post/post.php';
require_once INCLUDE_PATH . 'dispatch/central_dispatch.php';

nel_process_get($dataforce);
nel_process_post($dataforce);
nel_clean_exit($dataforce, false);

