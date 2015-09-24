<?php
// Initialization routines
// These all need to happen in roughly this order
// Otherwise it asplode
define('NELLIEL_VERSION', 'v0.9b'); // Version
define('BOARD_FILES', 'board_files/'); // Name of directory where the support and internal files go

require_once BOARD_FILES . 'config.php';

if (SQLTYPE === 'MYSQL')
{
    $dbh = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_DB, MYSQL_USER, MYSQL_PASS);
    $dbh->exec("SET CHARACTER SET utf8");
}
else if (SQLTYPE === 'SQLITE')
{
    $dbh = new PDO('sqlite:' . SQLITE_DB_LOCATION . SQLITE_DB_NAME);
}
else
{
    die('No database type specified in config. Can\'t do shit cap\'n!');
}

if (ini_get('date.timezone') === '')
{
    date_default_timezone_set('UTC');
}

// Mmm...sodium...
function asdfg($input)
{
    $half_salt = substr(HASH_SALT, 0, (strlen(HASH_SALT) / 2));
    $trip = md5($half_salt . md5(HASH_SALT . $input));
    return $trip;
}

define('LANG_TEXT_SPAMBOT_FIELD1', 'name'); // First anti-spambot hidden field
define('LANG_TEXT_SPAMBOT_FIELD2', 'url'); // Second anti-spambot hidden field

ignore_user_abort(TRUE);
require_once INCLUDE_PATH . 'setup.php';
setup_check();
generate_auth_file();

require_once FILES_PATH . '/auth_data.nel.php';
require_once INCLUDE_PATH . 'file-handling.php';
require_once INCLUDE_PATH . 'initializations.php';
require_once INCLUDE_PATH . 'universal-functions.php';
require_once INCLUDE_PATH . 'thread-functions.php';
require_once INCLUDE_PATH . 'admin-functions.php';
require_once INCLUDE_PATH . 'thread-generation.php';
require_once INCLUDE_PATH . 'main-generation.php';
require_once INCLUDE_PATH . 'html-generation.php';
require_once INCLUDE_PATH . 'snacks.php';

// Initialization done. GO TIME!

/* -----------Main------------- */

session_start();

if (BS1_USE_SPAMBOT_TRAP && (!is_null($dataforce['sp_field1']) || !is_null($dataforce['sp_field2'])))
{
    $dataforce['banreason'] = "Spambot. Nobody wants any. GTFO";
    $dataforce['bandays'] = 9001;
    $dataforce['banip'] = $_SERVER["REMOTE_ADDR"];
    ban_hammer($dataforce);
}

applyBan($dataforce, $authorized);

if (!empty($_SESSION))
{
    if (isset($dataforce['mode2']))
    {
        if ($dataforce['mode2'] === 'log_out')
        {
            terminate_session();
            echo '<meta http-equiv="refresh" content="0;URL=' . PHP_SELF2 . PHP_EXT . '">';
            die();
        }
        else if ($dataforce['mode2'] === 'admin')
        {
            regen_session();
            valid($dataforce);
        }
    }
    else if (isset($dataforce['admin_mode']))
    {
        regen_session();
    }
    else
    {
        $_SESSION['ignore_login'] = TRUE;
    }
}
else if (isset($dataforce['admin_mode']) && $dataforce['admin_mode'] === 'login') // No existing session but this may be a login attempt
{
    if ($dataforce['username'] !== '' && asdfg($dataforce['admin_pass']) === $authorized[$dataforce['username']]['staff_password'])
    {
        // We set up the session here
        $_SESSION['ignore_login'] = FALSE;
        $_SESSION['username'] = $dataforce['username'];
        $_SESSION['password'] = $dataforce['admin_pass'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
    }
    else
    {
        terminate_session();
        derp(107, LANG_ERROR_107, array('LOGIN'));
    }
    
    valid($dataforce);
}
else
{
    terminate_session();
}

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

if (!isset($dataforce['mode']))
{
    // Just regen page?
}
else
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
