<?php

//
// This handles the GET requests
//
function nel_process_get($dataforce, $authorize, $dbh)
{
    if (!isset($dataforce['get_mode']))
    {
        return;
    }
    
    switch ($dataforce['get_mode']) // Moar modes
    {
        case 'display':
            if (!empty($_SESSION)) // For expanding a thread
            {
                if (is_null($dataforce['response_id']))
                {
                    nel_regen($dataforce, NULL, 'main', TRUE, $dbh);
                }
                else
                {
                    nel_regen($dataforce, $dataforce['response_id'], 'thread', TRUE, $dbh);
                }
            }
            
            die();
        
        case 'admin':
            nel_login($dataforce, $authorize);
            die();
        
        case 'about':
            include INCLUDE_PATH . 'about.php';
            about_screen();
            die();
    }
}

//
// This handles the POST requests
//
function nel_process_post($dataforce, $plugins, $authorize, $dbh)
{
    global $fgsfds;
    
    if (!isset($dataforce['mode']))
    {
        return;
    }
    
    switch ($dataforce['mode']) // Even moar modes
    {
        case 'update':
            $updates = 0;
            
            if (!empty($_SESSION) && isset($dataforce['admin_mode']) && $dataforce['admin_mode'] === 'modmode')
            {
                if ($dataforce['banpost'])
                {
                    nel_ban_hammer($dataforce, $dbh);
                }
                
                if ($dataforce['delpost'])
                {
                    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_delete'))
                    {
                        nel_derp(108, array('origin' => 'DISPATCH'));
                    }

                    $updates = nel_thread_updates($dataforce, $plugins, $dbh);
                }
                
                nel_regen($dataforce, $updates, 'thread', FALSE, $dbh);
                nel_regen($dataforce, NULL, 'main', FALSE, $dbh);
                
                echo '<meta http-equiv="refresh" content="0;URL=' . PHP_SELF . '?mode=display&page=0">';
                nel_clean_exit($dataforce, TRUE);
            }
            
            $updates = nel_thread_updates($dataforce, $plugins, $dbh);
            nel_regen($dataforce, $updates, 'thread', FALSE, $dbh);
            nel_regen($dataforce, NULL, 'main', FALSE, $dbh);
            nel_clean_exit($dataforce, FALSE);
        
        case 'new_post':
            require_once INCLUDE_PATH . 'post.php';
            nel_process_new_post($dataforce, $plugins, $dbh);
            
            if ($fgsfds['noko'])
            {
                if (isset($dataforce['get_mode']) || $dataforce['mode_extra'] === 'modmode')
                {
                    echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF . '?mode=display&post=' . $fgsfds['noko_topic'] . '">';
                }
                else
                {
                    echo '<meta http-equiv="refresh" content="1;URL=' . PAGE_DIR . $fgsfds['noko_topic'] . '/' . $fgsfds['noko_topic'] . '.html">';
                }
            }
            else
            {
                if (!empty($_SESSION) && $dataforce['mode_extra'] === 'modmode')
                {
                    echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF . '?mode=display&page=0">';
                }
                else
                {
                    echo '<meta http-equiv="refresh" content="1e;URL=' . PHP_SELF2 . PHP_EXT . '">';
                }
            }
            
            nel_clean_exit($dataforce, TRUE);
        
        case 'admin':
            if (empty($_SESSION) || is_null($dataforce['sub_mode']))
            {
                break; // Should set up an error here probably
            }
            else
            {
                admin_dispatch($dataforce, $plugins, $authorize, $dbh);
            }
    }
}

function admin_dispatch($dataforce, $plugins, $authorize, $dbh)
{
    switch ($dataforce['sub_mode'])
    {
        case 'staff':
            require_once INCLUDE_PATH . 'admin/staff-panel.php';
            nel_staff_panel($dataforce, $authorize, $dbh);
            break;
        
        case 'ban':
            require_once INCLUDE_PATH . 'admin/bans-panel.php';
            nel_ban_control($dataforce, $authorize, $dbh);
            break;
        
        case 'modmode':
            echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF . '?mode=display&page=0">';
            break;
        
        case 'settings':
            require_once INCLUDE_PATH . 'admin/settings-panel.php';
            nel_settings_control($dataforce, $authorize, $dbh);
            break;
        
        case 'regen':
            nel_regen($dataforce, NULL, $dataforce['mode_action'], FALSE, $dbh);
            nel_login($dataforce, $authorize);
            break;
        
        case 'thread':
            require_once INCLUDE_PATH . 'admin/threads-panel.php';
            nel_thread_panel($dataforce, $authorize, $plugins, $dbh);
            break;
        
        default:
            nel_derp(153, array('origin' => 'DISPATCH'));
    }
    
    nel_clean_exit($dataforce, TRUE);
}

?>