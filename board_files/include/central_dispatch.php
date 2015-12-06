<?php

//
// This handles the GET requests
//
function nel_process_get($dataforce, $authorized)
{
    if (isset($dataforce['mode2']))
    {
        switch ($dataforce['mode2']) // Moar modes
        {
            case 'display':
                if (!empty($_SESSION)) // For expanding a thread
                {
                    if (is_null($dataforce['response_id']))
                    {
                        regen($dataforce, $authorized, NULL, 'main', TRUE);
                    }
                    else
                    {
                        regen($dataforce, $authorized, $dataforce['response_id'], 'thread', TRUE);
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
}

//
// This handles the POST requests
//
function nel_process_post($dataforce, $authorized)
{
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
                            ban_hammer($dataforce, $authorized);
                        }
                        else
                        {
                            derp(104, LANG_ERROR_104, array('MAIN'));
                        }
                    }
                    
                    $updates = thread_updates($dataforce, $authorized);
                    regen($dataforce, $authorized, $updates, 'thread', FALSE);
                    regen($dataforce, $authorized, NULL, 'main', FALSE);
                    
                    echo '<meta http-equiv="refresh" content="0;URL=' . PHP_SELF . '?mode=display&page=0">';
                    die();
                }
                
                $updates = thread_updates($dataforce, $authorized);
                regen($dataforce, $authorized, $updates, 'thread', FALSE);
                regen($dataforce, $authorized, NULL, 'main', FALSE);
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
                            admin_control($dataforce, $authorized, 'null');
                            break;
                        
                        case 'bancontrol':
                            ban_control($dataforce, $authorized, 'list');
                            break;
                        
                        case 'modcontrol':
                            thread_panel($dataforce, $authorized, 'list');
                            break;
                        
                        case 'staff':
                            staff_panel($dataforce, $authorized, 'staff');
                            break;
                        
                        case 'modmode':
                            echo '<meta http-equiv="refresh" content="0;URL=' . PHP_SELF . '?mode=display&page=0">';
                            die();
                        
                        case 'fullupdate':
                            regen($dataforce, $authorized, NULL, 'full', FALSE);
                            valid($dataforce);
                            break;
                        
                        case 'updatecache':
                            regen($dataforce, $authorized, NULL, 'update_all_cache', FALSE);
                            valid($dataforce);
                            break;
                        
                        // Settings panel
                        case 'changesettings':
                            admin_control($dataforce, $authorized, 'set');
                            break;
                        
                        // Bans panel (done)
                        case 'newban':
                            ban_control($dataforce, $authorized, 'new');
                            break;
                        
                        case 'addban':
                            if ($authorized[$_SESSION['username']]['perm_ban'])
                            {
                                ban_hammer($dataforce, $authorized);
                                ban_control($dataforce, $authorized, 'list');
                            }
                            else
                            {
                                derp(104, LANG_ERROR_104, array('MAIN'));
                            }
                            break;
                        
                        case 'modifyban':
                            ban_control($dataforce, $authorized, 'modify');
                            ban_control($dataforce, $authorized, 'list');
                            break;
                        
                        case 'removeban':
                            update_ban($dataforce, $authorized, 'remove');
                            ban_control($dataforce, $authorized, 'list');
                            break;
                        
                        case 'changeban':
                            update_ban($dataforce, $authorized, 'update');
                            ban_control($dataforce, $authorized, 'list');
                            break;
                        
                        // Staff panel (done)
                        case 'updatestaff':
                            staff_panel($dataforce, $authorized, 'update');
                            break;
                        
                        case 'deletestaff':
                            staff_panel($dataforce, $authorized, 'delete');
                            break;
                        
                        case 'addstaff':
                            staff_panel($dataforce, $authorized, 'add');
                            break;
                        
                        case 'editstaff':
                            staff_panel($dataforce, $authorized, 'edit');
                            break;
                        
                        // Thread panel (done)
                        case 'updatethread':
                            if (isset($dataforce['expand_thread']))
                            {
                                thread_panel($dataforce, $authorized, 'expand');
                            }
                            else
                            {
                                thread_panel($dataforce, $authorized, 'update');
                            }
                            break;
                        
                        case 'returnthreadlist':
                            thread_panel($dataforce, $authorized, 'return');
                            break;
                        
                        default:
                            derp(153, LANG_ERROR_153, 'ADMIN', array(), '');
                    }
                }
        }
    }
}

?>