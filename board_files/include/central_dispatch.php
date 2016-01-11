<?php

//
// This handles the GET requests
//
function nel_process_get($dataforce, $authorize, $dbh)
{
    if (!isset($dataforce['mode2']))
    {
        return;
    }

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
    	    nel_valid($dataforce, $authorize);
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
    
    	        if($dataforce['delpost'])
    	        {
    	            if ($authorize->get_user_perm($_SESSION['username'], 'perm_delete'))
    	            {
    	                $updates = nel_thread_updates($dataforce, $plugins, $dbh);
    	            }
    	            else
    	            {
    	                nel_derp(108, array('origin' => 'DISPATCH'));
    	            }
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
    	    nel_process_new_post($dataforce, $plugins, $dbh);
    
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
    	        	    nel_admin_control($dataforce, 'null', $authorize, $dbh);
    	        	    break;;
    
    	        	case 'bancontrol':
    	        	    nel_ban_control($dataforce, 'list', $authorize, $dbh);
    	        	    break;
    
    	        	case 'modcontrol':
    	        	    nel_thread_panel($dataforce, 'list', $authorize, $dbh);
    	        	    break;
    
    	        	case 'staff':
    	        	    nel_staff_panel($dataforce, 'staff', $plugins, $authorize, $dbh);
    	        	    break;
    
    	        	case 'modmode':
    	        	    echo '<meta http-equiv="refresh" content="0;URL=' . PHP_SELF . '?mode=display&page=0">';
    	        	    break;
    
    	        	case 'fullupdate':
    	        	    nel_regen($dataforce, NULL, 'full', FALSE, $dbh);
    	        	    nel_valid($dataforce, $authorize);
    	        	    break;
    
    	        	case 'updatecache':
    	        	    nel_regen($dataforce, NULL, 'update_all_cache', FALSE, $dbh);
    	        	    nel_valid($dataforce, $authorize);
    	        	    break;
    
    	        	    // Settings panel
    	        	case 'changesettings':
    	        	    nel_admin_control($dataforce, 'set', $authorize, $dbh);
    	        	    break;
    
    	        	    // Bans panel (done)
    	        	case 'newban':
    	        	    nel_ban_control($dataforce, 'new', $dbh);
    	        	    break;
    
    	        	case 'addban':
    	        	    nel_ban_hammer($dataforce, $dbh);
    	        	    nel_ban_control($dataforce, 'list', $authorize, $dbh);
    	        	    break;
    
    	        	case 'modifyban':
    	        	    nel_ban_control($dataforce, 'modify', $authorize, $dbh);
    	        	    break;
    
    	        	case 'removeban':
    	        	    nel_update_ban($dataforce, 'remove', $authorize, $dbh);
    	        	    break;
    
    	        	case 'changeban':
    	        	    nel_update_ban($dataforce, 'update', $authorize, $dbh);
    	        	    break;
    
    	        	    // Staff panel (done)
    	        	case 'updatestaff':
    	        	    nel_staff_panel($dataforce, 'update', $plugins, $authorize, $dbh);
    	        	    break;
    
    	        	case 'deletestaff':
    	        	    nel_staff_panel($dataforce, 'delete', $plugins, $authorize, $dbh);
    	        	    break;
    
    	        	case 'addstaff':
    	        	    nel_staff_panel($dataforce, 'add', $plugins, $authorize, $dbh);
    	        	    break;
    
    	        	case 'editstaff':
    	        	    nel_staff_panel($dataforce, 'edit', $plugins, $authorize, $dbh);
    	        	    break;
    
    	        	    // Thread panel (done)
    	        	case 'updatethread':
    	        	    if (isset($dataforce['expand_thread']))
    	        	    {
    	        	        nel_thread_panel($dataforce, 'expand', $authorize, $dbh);
    	        	    }
    	        	    else
    	        	    {
    	        	        nel_thread_panel($dataforce, 'update', $authorize, $dbh);
    	        	    }
    
    	        	    break;
    
    	        	case 'returnthreadlist':
    	        	    nel_thread_panel($dataforce, 'return', $authorize, $dbh);
    	        	    break;
    
    	        	default:
    	        	    nel_derp(153, array('origin' => 'DISPATCH'));
    	        }
    	        
    	        nel_clean_exit($dataforce, TRUE);
    	    }
    }
}

?>