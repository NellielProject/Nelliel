<?php
define('NELLIEL_VERSION', 'v0.9.1'); // Version
define('BOARD_FILES', 'board_files/'); // Name of directory where the support and internal files go


require_once BOARD_FILES . 'config.php';
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

define('SHA256_AVAILABLE', in_array('sha256', hash_algos()));

// This hashing is probably fine for most imageboards
// If you need something stronger, it can be replaced by a plugin method
function nel_hash($input, $plugins)
{
    $methods = array('sha256', 'md5', FALSE, FALSE);
    $methods = $plugins->plugin_hook('hash-algorithms', TRUE, array($methods));
    
    // If set to TRUE by a plugin, default method will not be used
    if (!$methods[3])
    {
        $half_salt1 = utf8_substr(HASH_SALT, 0, (utf8_strlen(HASH_SALT) / 2));
        $half_salt2 = utf8_substr(HASH_SALT, (utf8_strlen(HASH_SALT) / 2), utf8_strlen(HASH_SALT));
        
        // In case there is a need for something older
        if ($methods[2] || !SHA256_AVAILABLE)
        {
            $hash = hash($methods[1], $half_salt1 . $input . $half_salt2);
        }
        else
        {
            $hash = hash($methods[0], $half_salt1 . $input . $half_salt2);
        }
    }
    
    return $hash;
}

require_once INCLUDE_PATH . 'initializations.php';
require_once INCLUDE_PATH . 'archive.php';
require_once INCLUDE_PATH . 'derp.php';
require_once INCLUDE_PATH . 'universal-functions.php'; // Someday this shall be no more
require_once INCLUDE_PATH . 'thread-functions.php';
require_once INCLUDE_PATH . 'admin-functions.php';
require_once INCLUDE_PATH . 'output/thread-generation.php';
require_once INCLUDE_PATH . 'output/main-generation.php';
require_once INCLUDE_PATH . 'output/html-generation.php';
require_once INCLUDE_PATH . 'post.php';
require_once INCLUDE_PATH . 'snacks.php';

// Initialization done. IT'S GO TIME!


nel_ban_spambots($dataforce, $dbh);
require_once INCLUDE_PATH . 'sessions.php';
nel_initialize_session($dataforce, $plugins, $authorize);

require_once INCLUDE_PATH . 'central_dispatch.php';
nel_process_get($dataforce, $authorize, $dbh);
nel_process_post($dataforce, $plugins, $authorize, $dbh);
nel_regen($dataforce, NULL, 'main', FALSE, $dbh);
nel_clean_exit($dataforce, FALSE);

function nel_clean_exit($dataforce, $die)
{
    $dataforce['post_links'] = nel_cache_links($dataforce['post_links']);
    nel_write_multi_cache($dataforce);
    
    if ($die)
    {
        die();
    }
    
    if (STUFF_DONE)
    {
        echo '<meta http-equiv="refresh" content="10;URL=' . PHP_SELF2 . PHP_EXT . '">';
    }
    else
    {
        echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF2 . PHP_EXT . '">';
    }
    
    die();
}
?>
