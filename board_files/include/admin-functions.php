<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_valid($dataforce, $authorize)
{
    $render = new nel_render();
    $render->add_data('dotdot', '');
    nel_render_header($dataforce, $render, array());
    
    if (!empty($_SESSION))
    {
        $user_auth = $authorize->get_user_auth($_SESSION['username']);
        $render->add_multiple_data($user_auth['perms']);
        $render->parse('manage_options.tpl', 'management');
    }
    else
    {
        $render->parse('manage_login.tpl', 'management');
    }
    
    nel_render_basic_footer($render);
    echo $render->output();
}

function nel_change_true_false(&$item1, $key)
{
    if (is_bool($item1))
    {
        $item1 = FALSE;
    }
}

//
// Board settings
//
function nel_admin_control($dataforce, $authorize, $dbh)
{
    $mode = $dataforce['mode_action'];
    
    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_config'))
    {
        nel_derp(102, array('origin' => 'ADMIN'));
    }
    
    require_once INCLUDE_PATH . 'output/admin-panel-generation.php';
    $update = FALSE;
    
    if ($mode === 'update')
    {
        // Apply settings from admin panel
        $dbh->query('UPDATE ' . CONFIGTABLE . ' SET setting=""');
        
        while ($item = each($_POST))
        {
            if ($item[0] !== 'mode' && $item[0] !== 'username' && $item[0] !== 'super_sekrit')
            {
                if ($item[0] === 'jpeg_quality' && $item[1] > 100)
                {
                    $item[0] = 100;
                }
                
                if ($item[0] === 'page_limit')
                {
                    $dataforce['max_pages'] = (int) $item[1];
                }
                
                $dbh->query('UPDATE ' . CONFIGTABLE . ' SET setting="' . $item[1] . '" WHERE config_name="' . $item[0] . '"');
            }
        }
        
        nel_cache_rules($dbh);
        nel_cache_settings($dbh);
        nel_regen($dataforce, NULL, 'full', FALSE, $dbh);
    }
    
    nel_render_admin_panel($dataforce, $dbh);
}

//
// Thread management panel
//
function nel_thread_panel($dataforce, $mode, $authorize, $dbh)
{
    $mode = $dataforce['mode_action'];
    
    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_thread_panel'))
    {
        nel_derp(103, array('origin' => 'ADMIN'));
    }
    
    require_once INCLUDE_PATH . 'output/thread-panel-generation.php';
    
    if ($mode === 'update')
    {
        if (isset($dataforce['expand_thread']))
        {
            $expand = TRUE;
        }
        
        $updates = nel_thread_updates($dataforce, $dbh);
        nel_regen($dataforce, $updates, 'thread', FALSE, $dbh);
        nel_regen($dataforce, NULL, 'main', FALSE, $dbh);
    }
    
    nel_render_thread_panel($dataforce, $expand, $dbh);
}

?>