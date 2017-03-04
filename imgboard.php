<?php
define('NELLIEL_VERSION', 'v0.9.3'); // Version
define('BOARD_FILES', 'board_files/'); // Name of directory where the support and internal files go
require_once BOARD_FILES . 'config.php';
require_once INCLUDE_PATH . 'crypt.php';
require_once BOARD_FILES . 'libraries/password_compat/password.php';

$best_hashing = nel_best_available_hashing();

if($best_hashing === 0)
{
    die("No acceptable password hashing available. Something is broken or this host just sucks.");
}
else
{
    define('NELLIEL_PASS_ALGORITHM', $best_hashing);
}

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

require_once INCLUDE_PATH . 'database.php';
require_once INCLUDE_PATH . 'general-functions.php';
require_once INCLUDE_PATH . 'file-handling.php';
require_once INCLUDE_PATH . 'initializations.php';
require_once INCLUDE_PATH . 'archive.php';
require_once INCLUDE_PATH . 'derp.php';
require_once INCLUDE_PATH . 'regen.php';
require_once INCLUDE_PATH . 'thread-functions.php';
require_once INCLUDE_PATH . 'output/html-generation.php';
require_once INCLUDE_PATH . 'banhammer.php';
require_once INCLUDE_PATH . 'snacks.php';

// IT'S GO TIME!
nel_ban_spambots($dataforce);
require_once INCLUDE_PATH . 'sessions.php';
nel_initialize_session($dataforce, $authorize);
require_once INCLUDE_PATH . 'central-dispatch.php';
nel_process_get($dataforce, $authorize);
nel_process_post($dataforce, $authorize);
nel_regen($dataforce, NULL, 'main', FALSE);
nel_clean_exit($dataforce, FALSE);
?>
