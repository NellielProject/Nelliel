<?php

//
// This handles the GET requests
//
function nel_process_get($dataforce, $authorized, $lang, $dbh)
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
                        regen($dataforce, $authorized, $lang, NULL, 'main', TRUE, $dbh);
                    }
                    else
                    {
                        regen($dataforce, $authorized, $lang, $dataforce['response_id'], 'thread', TRUE, $dbh);
                    }
                }
                
                die();
            
            case 'admin':
                valid($dataforce, $authorized, $lang);
                die();
            
            case 'about':
                about_screen($lang);
                die();
        }
    }

}

//
// This handles the POST requests
//
function nel_process_post($dataforce, $authorized, $lang, $dbh)
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
                            ban_hammer($dataforce, $authorized, $lang, $dbh);
                        }
                        else
                        {
                            derp($lang, 104, $lang['ERROR_104'], array('MAIN'));
                        }
                    }
                    
                    $updates = thread_updates($dataforce, $authorized);
                    regen($dataforce, $authorized, $lang, $updates, 'thread', FALSE, $dbh);
                    regen($dataforce, $authorized, $lang, NULL, 'main', FALSE, $dbh);
                    
                    echo '<meta http-equiv="refresh" content="0;URL=' . PHP_SELF . '?mode=display&page=0">';
                    clean_exit($dataforce, TRUE);
                }
                
                $updates = thread_updates($dataforce, $authorized);
                regen($dataforce, $authorized, $lang, $updates, 'thread', FALSE, $dbh);
                regen($dataforce, $authorized, $lang, NULL, 'main', FALSE, $dbh);
                clean_exit($dataforce, FALSE);
            
            case 'new_post':
                new_post($dataforce, $authorized, $lang, $dbh);
                
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
                            admin_control($dataforce, $authorized, $lang, 'null', $dbh);
                            die();
                        
                        case 'bancontrol':
                            ban_control($dataforce, $authorized, $lang, 'list', $dbh);
                            die();
                        
                        case 'modcontrol':
                            thread_panel($dataforce, $authorized, $lang, 'list', $dbh);
                            die();
                        
                        case 'staff':
                            staff_panel($dataforce, $authorized, $lang, 'staff', $dbh);
                            die();
                        
                        case 'modmode':
                            echo '<meta http-equiv="refresh" content="0;URL=' . PHP_SELF . '?mode=display&page=0">';
                            die();
                        
                        case 'fullupdate':
                            regen($dataforce, $authorized, $lang, NULL, 'full', FALSE, $dbh);
                            valid($dataforce, $authorized, $lang);
                            clean_exit($dataforce, TRUE);
                        
                        case 'updatecache':
                            regen($dataforce, $authorized, $lang, NULL, 'update_all_cache', FALSE, $dbh);
                            valid($dataforce, $authorized, $lang);
                            clean_exit($dataforce, TRUE);
                        
                        // Settings panel
                        case 'changesettings':
                            admin_control($dataforce, $authorized, $lang, 'set', $dbh);
                            die();
                        
                        // Bans panel (done)
                        case 'newban':
                            ban_control($dataforce, $authorized, $lang, 'new', $dbh);
                            die();
                        
                        case 'addban':
                            if ($authorized[$_SESSION['username']]['perm_ban'])
                            {
                                ban_hammer($dataforce, $authorized, $lang, $dbh);
                                ban_control($dataforce, $authorized, $lang, 'list', $dbh);
                            }
                            else
                            {
                                derp($lang, 104, $lang['ERROR_104'], array('MAIN'));
                            }
                            
                            die();
                        
                        case 'modifyban':
                            ban_control($dataforce, $authorized, $lang, 'modify', $dbh);
                            ban_control($dataforce, $authorized, $lang, 'list', $dbh);
                            die();
                        
                        case 'removeban':
                            update_ban($dataforce, $authorized, $lang, 'remove', $dbh);
                            ban_control($dataforce, $authorized, $lang, 'list', $dbh);
                            die();
                        
                        case 'changeban':
                            update_ban($dataforce, $authorized, $lang, 'update', $dbh);
                            ban_control($dataforce, $authorized, $lang, 'list', $dbh);
                            die();
                        
                        // Staff panel (done)
                        case 'updatestaff':
                            staff_panel($dataforce, $authorized, $lang, 'update', $dbh);
                            die();
                        
                        case 'deletestaff':
                            staff_panel($dataforce, $authorized, $lang, 'delete', $dbh);
                            die();
                        
                        case 'addstaff':
                            staff_panel($dataforce, $authorized, $lang, 'add', $dbh);
                            die();
                        
                        case 'editstaff':
                            staff_panel($dataforce, $authorized, $lang, 'edit', $dbh);
                            die();
                        
                        // Thread panel (done)
                        case 'updatethread':
                            if (isset($dataforce['expand_thread']))
                            {
                                thread_panel($dataforce, $authorized, $lang, 'expand', $dbh);
                            }
                            else
                            {
                                thread_panel($dataforce, $authorized, $lang, 'update', $dbh);
                            }
                            
                            die();
                        
                        case 'returnthreadlist':
                            thread_panel($dataforce, $authorized, $lang, 'return', $dbh);
                            die();
                        
                        default:
                            derp($lang, 153, $lang['ERROR_153'], 'ADMIN', array(), '');
                    }
                }
        }
    }

}

?>