<?php
define('NELLIEL_VERSION', 'v0.9.1'); // Version
define('BOARD_FILES', 'board_files/'); // Name of directory where the support and internal files go

require_once BOARD_FILES . 'config.php';
require_once INCLUDE_PATH . 'plugins.php';
define('SHA256_AVAILABLE', in_array('sha256', hash_algos()));

// Mmm...sodium...
function salt_hash($input)
{
    global $plugins;
    $methods = array('sha256', 'md5', FALSE);
    $methods = $plugins->plugin_hook('change-salted-hash-algorithms', TRUE, array($methods));
    $half_salt = substr(HASH_SALT, 0, (strlen(HASH_SALT) / 2));
    
    // In case there is a need for something older
    if($methods[2] || !SHA256_AVAILABLE)
    {
        $hash = hash($methods[1], $half_salt . md5(HASH_SALT . $input));

    }
    else
    {
        $hash = hash($methods[0], $half_salt . md5(HASH_SALT . $input));
    }

    return $hash;
}

require_once INCLUDE_PATH . 'initializations.php';
require_once INCLUDE_PATH . 'archive.php';
require_once INCLUDE_PATH . 'universal-functions.php';
require_once INCLUDE_PATH . 'thread-functions.php';
require_once INCLUDE_PATH . 'admin-functions.php';
require_once INCLUDE_PATH . 'thread-generation.php';
require_once INCLUDE_PATH . 'main-generation.php';
require_once INCLUDE_PATH . 'html-generation.php';
require_once INCLUDE_PATH . 'snacks.php';
require_once INCLUDE_PATH . 'sessions.php';


// Initialization done. IT'S GO TIME!

ban_spambots($dataforce);
applyBan($dataforce, $authorized);

session_start();
initialize_session($dataforce, $authorized);

// This handles the GET requests
if (isset($dataforce['mode2']))
{
    switch ($dataforce['mode2']) // Moar modes
    {
        case 'display':
            if (!empty($_SESSION)) // For expanding a thread
            {
                if (is_null($dataforce['response_id']))
                {
                    regen($dataforce, NULL, 'main', TRUE);
                }
                else
                {
                    regen($dataforce, $dataforce['response_id'], 'thread', TRUE);
                }
            }
        
        case 'admin':
            valid($dataforce);
            die();
        
        case 'about':
            about_screen();
            die();
    }
}

// This is for the POST requests
if (isset($dataforce['mode']))
{
    switch ($dataforce['mode']) // Even moar modes
    {
        case 'update':
            
            if (!empty($_SESSION) && isset($dataforce['admin_mode']) && $dataforce['admin_mode'] === 'modmode')
            {
                if ($dataforce['banpost'])
                {
                    if ($authorized[$_SESSION['username']]['perm_ban'])
                    {
                        ban_hammer($dataforce);
                    }
                    else
                    {
                        derp(104, LANG_ERROR_104, array('MAIN'));
                    }
                }
                
                $updates = thread_updates($dataforce);
                regen($dataforce, $updates, 'thread', FALSE);
                regen($dataforce, NULL, 'main', FALSE);
                
                echo '<meta http-equiv="refresh" content="0;URL=' . PHP_SELF . '?mode=display&page=0">';
                die();
            }
            
            $updates = thread_updates($dataforce);
            regen($dataforce, $updates, 'thread', FALSE);
            regen($dataforce, NULL, 'main', FALSE);
            break;
        
        case 'new_post':
            require_once INCLUDE_PATH . 'post.php';
            new_post($dataforce, $authorized);
            
            if ($fgsfds['noko'])
            {
                if (isset($dataforce['mode2']) || $dataforce['mode_extra'] === 'modmode')
                {
                    echo '<meta http-equiv="refresh" content="0;URL=' . PHP_SELF . '?mode=display&post=' . $fgsfds['noko_topic'] . '">';
                }
                else
                {
                    echo '<meta http-equiv="refresh" content="0;URL=' . PAGE_DIR . $fgsfds['noko_topic'] . '/' . $fgsfds['noko_topic'] . '.html">';
                }
                
                die();
            }
            else
            {
                if (!empty($_SESSION) && $dataforce['mode_extra'] === 'modmode')
                {
                    echo '<meta http-equiv="refresh" content="0;URL=' . PHP_SELF . '?mode=display&page=0">';
                }
                else
                {
                    echo '<meta http-equiv="refresh" content="0;URL=' . PHP_SELF2 . PHP_EXT . '">';
                }
                
                die();
            }
        
        case 'admin':
            if (!empty($_SESSION) && isset($dataforce['admin_mode']))
            {
                switch ($dataforce['admin_mode'])
                {
                    // Options list (done)
                    case 'admincontrol':
                        admin_control($dataforce, 'null');
                        break;
                    
                    case 'bancontrol':
                        ban_control($dataforce, 'list');
                        break;
                    
                    case 'modcontrol':
                        thread_panel($dataforce, 'list');
                        break;
                    
                    case 'staff':
                        staff_panel($dataforce, 'staff');
                        break;
                    
                    case 'modmode':
                        echo '<meta http-equiv="refresh" content="0;URL=' . PHP_SELF . '?mode=display&page=0">';
                        die();
                    
                    case 'fullupdate':
                        regen($dataforce, NULL, 'full', FALSE);
                        valid($dataforce);
                        break;
                    
                    case 'updatecache':
                        regen($dataforce, NULL, 'update_all_cache', FALSE);
                        valid($dataforce);
                        break;
                    
                    // Settings panel
                    case 'changesettings':
                        admin_control($dataforce, 'set');
                        break;
                    
                    // Bans panel (done)
                    case 'newban':
                        ban_control($dataforce, 'new');
                        break;
                    
                    case 'addban':
                        if ($authorized[$_SESSION['username']]['perm_ban'])
                        {
                            ban_hammer($dataforce);
                            ban_control($dataforce, 'list');
                        }
                        else
                        {
                            derp(104, LANG_ERROR_104, array('MAIN'));
                        }
                        break;
                    
                    case 'modifyban':
                        ban_control($dataforce, 'modify');
                        ban_control($dataforce, 'list');
                        break;
                    
                    case 'removeban':
                        update_ban($dataforce, 'remove');
                        ban_control($dataforce, 'list');
                        break;
                    
                    case 'changeban':
                        update_ban($dataforce, 'update');
                        ban_control($dataforce, 'list');
                        break;
                    
                    // Staff panel (done)
                    case 'updatestaff':
                        staff_panel($dataforce, 'update');
                        break;
                    
                    case 'deletestaff':
                        staff_panel($dataforce, 'delete');
                        break;
                    
                    case 'addstaff':
                        staff_panel($dataforce, 'add');
                        break;
                    
                    case 'editstaff':
                        staff_panel($dataforce, 'edit');
                        break;
                    
                    // Thread panel (done)
                    case 'updatethread':
                        if (isset($dataforce['expand_thread']))
                        {
                            thread_panel($dataforce, 'expand');
                        }
                        else
                        {
                            thread_panel($dataforce, 'update');
                        }
                        break;
                    
                    case 'returnthreadlist':
                        thread_panel($dataforce, 'return');
                        break;
                    
                    default:
                        derp(153, LANG_ERROR_153, 'ADMIN', array(), '');
                }
            }
    }
}

regen($dataforce, NULL, 'main', FALSE);

if (STUFF_DONE)
{
    echo '<meta http-equiv="refresh" content="10;URL=' . PHP_SELF2 . PHP_EXT . '">';
}
else
{
    echo '<meta http-equiv="refresh" content="0;URL=' . PHP_SELF2 . PHP_EXT . '">';
}

$dbh = NULL;
?>
