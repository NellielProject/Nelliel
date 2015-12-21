<?php

//
// This handles the GET requests
//
function nel_process_get($dataforce, $dbh)
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
                        nel_regen($dataforce, NULL, 'main', TRUE, $dbh);
                    }
                    else
                    {
                        nel_regen($dataforce, $dataforce['response_id'], 'thread', TRUE, $dbh);
                    }
                }
                
                die();
            
            case 'admin':
                nel_valid($dataforce);
                die();
            
            case 'about':
                include INCLUDE_PATH . 'about.php';
                about_screen();
                die();
        }
    }
}

//
// This handles the POST requests
//
function nel_process_post($dataforce, $dbh)
{
    global $fgsfds;
    
    if (isset($dataforce['mode']))
    {
        switch ($dataforce['mode']) // Even moar modes
        {
            case 'update':
                
                if (!empty($_SESSION) && isset($dataforce['admin_mode']) && $dataforce['admin_mode'] === 'modmode')
                {
                    if ($dataforce['banpost'])
                    {
                        if (nel_is_authorized($_SESSION['username'], 'perm_ban'))
                        {
                            nel_ban_hammer($dataforce, $dbh);
                        }
                        else
                        {
                            nel_derp(104, array('origin' => 'DISPATCH'));
                        }
                    }
                    
                    $updates = nel_thread_updates($dataforce, $dbh);
                    nel_regen($dataforce, $updates, 'thread', FALSE, $dbh);
                    nel_regen($dataforce, NULL, 'main', FALSE, $dbh);
                    
                    echo '<meta http-equiv="refresh" content="0;URL=' . PHP_SELF . '?mode=display&page=0">';
                    nel_clean_exit($dataforce, TRUE);
                }
                
                $updates = nel_thread_updates($dataforce, $dbh);
                nel_regen($dataforce, $updates, 'thread', FALSE, $dbh);
                nel_regen($dataforce, NULL, 'main', FALSE, $dbh);
                nel_clean_exit($dataforce, FALSE);
            
            case 'new_post':
                nel_process_new_post($dataforce, $dbh);
                
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
                }
                
                nel_clean_exit($dataforce, TRUE);
            
            case 'admin':
                if (!empty($_SESSION) && isset($dataforce['admin_mode']))
                {
                    switch ($dataforce['admin_mode'])
                    {
                        // Options list (done)
                        case 'admincontrol':
                            nel_admin_control($dataforce, 'null', $dbh);
                            die();
                        
                        case 'bancontrol':
                            nel_ban_control($dataforce, 'list', $dbh);
                            die();
                        
                        case 'modcontrol':
                            nel_thread_panel($dataforce, 'list', $dbh);
                            die();
                        
                        case 'staff':
                            nel_staff_panel($dataforce, 'staff', $dbh);
                            die();
                        
                        case 'modmode':
                            echo '<meta http-equiv="refresh" content="0;URL=' . PHP_SELF . '?mode=display&page=0">';
                            die();
                        
                        case 'fullupdate':
                            nel_regen($dataforce, NULL, 'full', FALSE, $dbh);
                            nel_valid($dataforce);
                            nel_clean_exit($dataforce, TRUE);
                        
                        case 'updatecache':
                            nel_regen($dataforce, NULL, 'update_all_cache', FALSE, $dbh);
                            nel_valid($dataforce);
                            nel_clean_exit($dataforce, TRUE);
                        
                        // Settings panel
                        case 'changesettings':
                            nel_admin_control($dataforce, 'set', $dbh);
                            die();
                        
                        // Bans panel (done)
                        case 'newban':
                            nel_ban_control($dataforce, 'new', $dbh);
                            die();
                        
                        case 'addban':
                            if (nel_is_authorized($_SESSION['username'], 'perm_ban'))
                            {
                                nel_ban_hammer($dataforce, $dbh);
                                nel_ban_control($dataforce, 'list', $dbh);
                            }
                            else
                            {
                                nel_derp(104, array('origin' => 'DISPATCH'));
                            }
                            
                            die();
                        
                        case 'modifyban':
                            nel_ban_control($dataforce, 'modify', $dbh);
                            nel_ban_control($dataforce, 'list', $dbh);
                            die();
                        
                        case 'removeban':
                            nel_update_ban($dataforce, 'remove', $dbh);
                            nel_ban_control($dataforce, 'list', $dbh);
                            die();
                        
                        case 'changeban':
                            nel_update_ban($dataforce, 'update', $dbh);
                            nel_ban_control($dataforce, 'list', $dbh);
                            die();
                        
                        // Staff panel (done)
                        case 'updatestaff':
                            nel_staff_panel($dataforce, 'update', $dbh);
                            die();
                        
                        case 'deletestaff':
                            nel_staff_panel($dataforce, 'delete', $dbh);
                            die();
                        
                        case 'addstaff':
                            nel_staff_panel($dataforce, 'add', $dbh);
                            die();
                        
                        case 'editstaff':
                            nel_staff_panel($dataforce, 'edit', $dbh);
                            die();
                        
                        // Thread panel (done)
                        case 'updatethread':
                            if (isset($dataforce['expand_thread']))
                            {
                                nel_thread_panel($dataforce, 'expand', $dbh);
                            }
                            else
                            {
                                nel_thread_panel($dataforce, 'update', $dbh);
                            }
                            
                            die();
                        
                        case 'returnthreadlist':
                            nel_thread_panel($dataforce, 'return', $dbh);
                            die();
                        
                        default:
                            nel_derp(153, array('origin' => 'DISPATCH'));
                    }
                }
        }
    }
}

?>