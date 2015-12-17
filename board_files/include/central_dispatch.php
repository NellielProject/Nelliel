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
                        regen($dataforce, NULL, 'main', TRUE, $dbh);
                    }
                    else
                    {
                        regen($dataforce, $dataforce['response_id'], 'thread', TRUE, $dbh);
                    }
                }
                
                die();
            
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
                        if (is_authorized($_SESSION['username'], 'perm_ban'))
                        {
                            ban_hammer($dataforce, $dbh);
                        }
                        else
                        {
                            derp(104, array('origin' => 'DISPATCH'));
                        }
                    }
                    
                    $updates = thread_updates($dataforce, $dbh);
                    regen($dataforce, $updates, 'thread', FALSE, $dbh);
                    regen($dataforce, NULL, 'main', FALSE, $dbh);
                    
                    echo '<meta http-equiv="refresh" content="0;URL=' . PHP_SELF . '?mode=display&page=0">';
                    clean_exit($dataforce, TRUE);
                }
                
                $updates = thread_updates($dataforce, $dbh);
                regen($dataforce, $updates, 'thread', FALSE, $dbh);
                regen($dataforce, NULL, 'main', FALSE, $dbh);
                clean_exit($dataforce, FALSE);
            
            case 'new_post':
                new_post($dataforce, $dbh);
                
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
                
                clean_exit($dataforce, TRUE);
            
            case 'admin':
                if (!empty($_SESSION) && isset($dataforce['admin_mode']))
                {
                    switch ($dataforce['admin_mode'])
                    {
                        // Options list (done)
                        case 'admincontrol':
                            admin_control($dataforce, 'null', $dbh);
                            die();
                        
                        case 'bancontrol':
                            ban_control($dataforce, 'list', $dbh);
                            die();
                        
                        case 'modcontrol':
                            thread_panel($dataforce, 'list', $dbh);
                            die();
                        
                        case 'staff':
                            staff_panel($dataforce, 'staff', $dbh);
                            die();
                        
                        case 'modmode':
                            echo '<meta http-equiv="refresh" content="0;URL=' . PHP_SELF . '?mode=display&page=0">';
                            die();
                        
                        case 'fullupdate':
                            regen($dataforce, NULL, 'full', FALSE, $dbh);
                            valid($dataforce);
                            clean_exit($dataforce, TRUE);
                        
                        case 'updatecache':
                            regen($dataforce, NULL, 'update_all_cache', FALSE, $dbh);
                            valid($dataforce);
                            clean_exit($dataforce, TRUE);
                        
                        // Settings panel
                        case 'changesettings':
                            admin_control($dataforce, 'set', $dbh);
                            die();
                        
                        // Bans panel (done)
                        case 'newban':
                            ban_control($dataforce, 'new', $dbh);
                            die();
                        
                        case 'addban':
                            if (is_authorized($_SESSION['username'], 'perm_ban'))
                            {
                                ban_hammer($dataforce, $dbh);
                                ban_control($dataforce, 'list', $dbh);
                            }
                            else
                            {
                                derp(104, array('origin' => 'DISPATCH'));
                            }
                            
                            die();
                        
                        case 'modifyban':
                            ban_control($dataforce, 'modify', $dbh);
                            ban_control($dataforce, 'list', $dbh);
                            die();
                        
                        case 'removeban':
                            update_ban($dataforce, 'remove', $dbh);
                            ban_control($dataforce, 'list', $dbh);
                            die();
                        
                        case 'changeban':
                            update_ban($dataforce, 'update', $dbh);
                            ban_control($dataforce, 'list', $dbh);
                            die();
                        
                        // Staff panel (done)
                        case 'updatestaff':
                            staff_panel($dataforce, 'update', $dbh);
                            die();
                        
                        case 'deletestaff':
                            staff_panel($dataforce, 'delete', $dbh);
                            die();
                        
                        case 'addstaff':
                            staff_panel($dataforce, 'add', $dbh);
                            die();
                        
                        case 'editstaff':
                            staff_panel($dataforce, 'edit', $dbh);
                            die();
                        
                        // Thread panel (done)
                        case 'updatethread':
                            if (isset($dataforce['expand_thread']))
                            {
                                thread_panel($dataforce, 'expand', $dbh);
                            }
                            else
                            {
                                thread_panel($dataforce, 'update', $dbh);
                            }
                            
                            die();
                        
                        case 'returnthreadlist':
                            thread_panel($dataforce, 'return', $dbh);
                            die();
                        
                        default:
                            derp(153, array('origin' => 'DISPATCH'));
                    }
                }
        }
    }
}

?>