<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_valid($dataforce, $authorize)
{
    $dat = '';
    
    if (!empty($_SESSION))
    {
        $user_auth = $authorize->get_user_auth($_SESSION['username']);
        nel_render_multiple_in($user_auth['perms']);
        $dat .= nel_render_header($dataforce, 'ADMIN', array());
        $dat .= nel_parse_template('manage_options.tpl', 'management', '', FALSE);
        $dat .= nel_render_basic_footer();
        echo $dat;
    }
    else
    {
        $dat .= nel_render_header($dataforce, 'ADMIN', array());
        $dat .= nel_parse_template('manage_login.tpl', 'management', '', FALSE);
        $dat .= nel_render_basic_footer();
        echo $dat;
    }
}

//
// Update ban info
//
function nel_update_ban($dataforce, $mode, $authorize, $dbh)
{
    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_ban_panel'))
    {
        nel_derp(101, array('origin' => 'ADMIN'));
    }
    
    if ($mode === 'remove')
    {
        $dbh->query('DELETE FROM ' . BANTABLE . ' WHERE id=' . $dataforce['banid'] . '');
    }
    
    if ($mode === 'update')
    {
        $ban_input = array('days' => 0, 'hours' => 0, 'reason' => '', 'response' => '', 'review' => FALSE, 'status' => 0, 'length' => '');
        
        foreach ($_POST as $key => $val)
        {
            if ($key === 'timedays')
            {
                $ban_input['days'] = $val * 86400;
            }
            
            if ($key === 'timehours')
            {
                $ban_input['hours'] = $val * 3600;
            }
            
            if ($key === 'banreason')
            {
                $ban_input['reason'] = $val;
            }
            
            if ($key === 'appealresponse')
            {
                $ban_input['response'] = $val;
            }
            
            if ($key === 'appealreview')
            {
                $ban_input['review'] = TRUE;
            }
            
            if ($key === 'appealstatus')
            {
                $ban_input['status'] = $val;
            }
            
            if ($key === 'original')
            {
                $ban_input['length'] = $val;
            }
        }
        
        $bantotal = (int) $ban_input['days'] + (int) $ban_input['hours'];
        
        if ($ban_input['review'])
        {
            $ban_input['status'] = ((int) $ban_input['length'] !== $bantotal) ? 3 : 2;
        }
        
        $prepared = $dbh->prepare('UPDATE ' . BANTABLE . ' SET reason=:reason, length=:length, appeal_response=:response, appeal_status=:status WHERE id=:banid');
        $prepared->bindParam(':reason', $ban_input['reason'], PDO::PARAM_STR);
        $prepared->bindParam(':length', $bantotal, PDO::PARAM_INT);
        $prepared->bindParam(':response', $ban_input['response'], PDO::PARAM_STR);
        $prepared->bindParam(':status', $ban_input['status'], PDO::PARAM_INT);
        $prepared->bindParam(':banid', $dataforce['banid'], PDO::PARAM_INT);
        $prepared->execute();
        unset($prepared);
    }
}

//
// Staff control panel
// /


// This whole section is messy but works. Will clean up later. Really.
function nel_staff_panel($dataforce, $mode, $plugins, $authorize, $dbh)
{
    $temp_auth = array();
    $dat = '';

    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_staff_panel'))
    {
        nel_derp(102, array('origin' => 'ADMIN'));
    }
    
    require_once INCLUDE_PATH . 'output-generation/staff-panel-generation.php';
    
    if(isset($_POST['staff_name']))
    {
        $staff_name = $_POST['staff_name'];
    }

    if ($mode === 'edit' || $mode === 'add')
    {
        if(isset($_POST['staff_type']))
        {
            $staff_type = $_POST['staff_type'];
        }

        if ($mode === 'add')
        {
            if ($authorize->get_user_auth(nel_render_out('staff_name')))
            {
                nel_derp(154, array('origin' => 'ADMIN'));
            }
            
            nel_gen_new_staff($staff_name, $staff_type, $authorize);
        }
        else if ($mode === 'edit')
        {
            if (!$authorize->get_user_auth($staff_name))
            {
                nel_derp(150, array('origin' => 'ADMIN'));
            }
        }
        
        $temp_auth = $authorize->get_user_auth($staff_name);
        $dat = nel_render_staff_panel_edit($dataforce, $temp_auth);
    }
    else if ($mode === 'update')
    {
        $old_pass = $authorize->get_user_setting($staff_name, 'staff_password');
        $new_pass = '';
        $new_auth = $authorize->get_blank_settings();
        
        foreach ($_POST as $key => $val)
        {
            if ($key === 'staff_password')
            {
                $new_pass = nel_hash($val, $plugins);
            }
            
            if ($key === 'change_pass' && $new_pass != '')
            {
                $new_auth['staff_password'] = $new_pass;
            }
            
            if ($key !== 'adminmode' && $key !== 'mode' && $key !== 'staff_name' && $key !== 'username' && $key !== 'super_sekrit' && $key !== 'staff_password' && $key !== 'change_pass')
            {
                if ($val === '1')
                {
                    $new_auth[$key] = TRUE;
                }
                else
                {
                    $new_auth[$key] = $val;
                }
            }
            
            $authorize->update_user_auth($staff_name, $new_auth, $authorize);
            $temp_auth = $new_auth;
        }
        
        $authorize->write_auth_file();
        $dat = nel_render_staff_panel_add($dataforce, $temp_auth);
    }
    else if ($mode === 'delete')
    {
        $authorize->remove_user_auth($staff_name);
        $authorize->write_auth_file();
    }
    else
    {
        $dat = nel_render_staff_panel_add($dataforce, $temp_auth);
    }

    echo $dat;
}

function nel_gen_new_staff($new_name, $new_type, $authorize)
{
    $new_auth = $authorize->get_blank_settings();
    
    if ($new_type === 'admin')
    {
        $authorize->update_user_setting($new_name, 'perm_config', TRUE);
        $authorize->update_user_setting($new_name, 'perm_staff_panel', TRUE);
        $authorize->update_user_setting($new_name, 'perm_ban_panel', TRUE);
        $authorize->update_user_setting($new_name, 'perm_thread_panel', TRUE);
        $authorize->update_user_setting($new_name, 'perm_mod_mode', TRUE);
        $authorize->update_user_setting($new_name, 'perm_ban', TRUE);
        $authorize->update_user_setting($new_name, 'perm_delete', TRUE);
        $authorize->update_user_setting($new_name, 'perm_post', TRUE);
        $authorize->update_user_setting($new_name, 'perm_post_anon', TRUE);
        $authorize->update_user_setting($new_name, 'perm_sticky', TRUE);
        $authorize->update_user_setting($new_name, 'perm_update_pages', TRUE);
        $authorize->update_user_setting($new_name, 'perm_update_cache', TRUE);
    }
    else if ($new_type === 'moderator')
    {
        $authorize->update_user_setting($new_name, 'perm_config', FALSE);
        $authorize->update_user_setting($new_name, 'perm_staff_panel', FALSE);
        $authorize->update_user_setting($new_name, 'perm_ban_panel', TRUE);
        $authorize->update_user_setting($new_name, 'perm_thread_panel', TRUE);
        $authorize->update_user_setting($new_name, 'perm_mod_mode', TRUE);
        $authorize->update_user_setting($new_name, 'perm_ban', TRUE);
        $authorize->update_user_setting($new_name, 'perm_delete', TRUE);
        $authorize->update_user_setting($new_name, 'perm_post', TRUE);
        $authorize->update_user_setting($new_name, 'perm_post_anon', TRUE);
        $authorize->update_user_setting($new_name, 'perm_sticky', TRUE);
        $authorize->update_user_setting($new_name, 'perm_update_pages', FALSE);
        $authorize->update_user_setting($new_name, 'perm_update_cache', FALSE);
    }
    else if ($new_type === 'janitor')
    {
        $authorize->update_user_setting($new_name, 'perm_config', FALSE);
        $authorize->update_user_setting($new_name, 'perm_staff_panel', FALSE);
        $authorize->update_user_setting($new_name, 'perm_ban_panel', FALSE);
        $authorize->update_user_setting($new_name, 'perm_thread_panel', FALSE);
        $authorize->update_user_setting($new_name, 'perm_mod_mode', TRUE);
        $authorize->update_user_setting($new_name, 'perm_ban', FALSE);
        $authorize->update_user_setting($new_name, 'perm_delete', TRUE);
        $authorize->update_user_setting($new_name, 'perm_post', FALSE);
        $authorize->update_user_setting($new_name, 'perm_post_anon', FALSE);
        $authorize->update_user_setting($new_name, 'perm_sticky', FALSE);
        $authorize->update_user_setting($new_name, 'perm_update_pages', FALSE);
        $authorize->update_user_setting($new_name, 'perm_update_cache', FALSE);
    }
    else
    {
        nel_derp(151, array('origin' => 'ADMIN'));
    }
    
    $authorize->write_auth_file();
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
function nel_admin_control($dataforce, $mode, $authorize, $dbh)
{
    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_config'))
    {
        nel_derp(102, array('origin' => 'ADMIN'));
    }

    require_once INCLUDE_PATH . 'output-generation/admin-panel-generation.php';
    $update = FALSE;

    if ($mode === 'set')
    {
        // Apply settings from admin panel
        $dbh->query('UPDATE ' . CONFIGTABLE . ' SET setting=""');
        
        while ($item = each($_POST))
        {
            if ($item[0] !== 'adminmode' && $item[0] !== 'username' && $item[0] !== 'super_sekrit')
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

    $dat = nel_render_admin_panel($dataforce, $dbh);
    echo $dat;
}

//
// Ban control panel
//
function nel_ban_control($dataforce, $mode, $authorize, $dbh)
{
    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_ban_panel'))
    {
        nel_derp(101, array('origin' => 'ADMIN'));
    }

    require_once INCLUDE_PATH . 'output-generation/ban-panel-generation.php';

    if ($mode === 'list')
    {
        $dat = nel_render_ban_panel_list($dataforce, $dbh);
    }
    else if ($mode === 'modify')
    {
        $dat = nel_render_ban_panel_modify($dataforce, $dbh);
    }
    else if ($mode === 'new')
    {
        $dat = nel_render_ban_panel_add($dataforce, $dbh);
    }

    echo $dat;
}

//
// Thread management panel
//
function nel_thread_panel($dataforce, $mode, $authorize, $dbh)
{
    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_thread_panel'))
    {
        nel_derp(103, array('origin' => 'ADMIN'));
    }
    
    require_once INCLUDE_PATH . 'output-generation/thread-panel-generation.php';

    if ($mode === 'update')
    {
        $updates = nel_thread_updates($dataforce, $dbh);
        nel_regen($dataforce, $updates, 'thread', FALSE, $dbh);
        nel_regen($dataforce, NULL, 'main', FALSE, $dbh);
    }

    $dat = nel_render_thread_panel($dataforce, $expand, $dbh);
    echo $dat;
}

//
// Apply b&hammer
//
function nel_ban_hammer($dataforce, $dbh)
{
    $ban_input = array();

    if ($dataforce['admin_mode'] === 'add_ban')
    {
        $prepared = $dbh->prepare('INSERT INTO ' . BANTABLE . ' (board,type,host,name,reason,length,ban_time) 
								VALUES ("' . POSTTABLE . '",NULL,NULL,NULL,:reason,:length,' . time() . ')');
        $prepared->bindParam(':host', @inet_pton($dataforce['banip']), PDO::PARAM_STR);
        $prepared->bindParam(':reason', $dataforce['banreason'], PDO::PARAM_STR);
        $prepared->bindParam(':length', (($dataforce['timedays'] * 86400) + ($dataforce['timehours'] * 3600)), PDO::PARAM_INT);
        $prepared->execute();
        unset($prepared);
        return;
    }
    
    reset($_POST);
    
    $manual = FALSE;
    $manual_host = '';
    $i = 0;
    $current_num = '';
    
    while ($item = each($_POST))
    {
        if ($item[0] === 'adminmode' && $item[1] === 'addban')
        {
            $manual = TRUE;
            if ($i !== 0)
            {
                ++ $i;
            }
        }
        
        if ($item[0] === 'postban' . $item[1])
        {
            if ($i !== 0)
            {
                ++ $i;
            }
            
            $current_num = $item[1];
            $ban_input[$i] = array('num' => $item[1], 'days' => 0, 'hours' => 0, 'message' => '', 'reason' => '', 'name' => '', 'host' => '');
        }
        
        if ($item[0] === 'timedays' . $current_num)
        {
            $ban_input[$i]['days'] = $item[1] * 86400;
        }
        
        if ($item[0] === 'timehours' . $current_num)
        {
            $ban_input[$i]['hours'] = $item[1] * 3600;
        }
        
        if ($item[0] === 'banmessage' . $current_num)
        {
            $ban_input[$i]['message'] = $item[1];
        }
        
        if ($item[0] === 'banreason' . $current_num)
        {
            $ban_input[$i]['reason'] = $item[1];
        }
        
        if ($item[0] === 'banname' . $current_num)
        {
            $ban_input[$i]['name'] = $item[1];
        }
        
        if ($item[0] === 'banhost' . $current_num)
        {
            $ban_input[$i]['host'] = $item[1];
        }
    }
    
    if ($manual)
    {
        $count_posts = count($ban_input);
        $i = 0;
        while ($i < $count_posts)
        {
            $banlength = $ban_input[$i]['days'] + $ban_input[$i]['hours'];
            $prepared = $dbh->prepare('INSERT INTO ' . BANTABLE . ' (board,type,host,name,reason,length,ban_time) 
									VALUES ("' . POSTTABLE . '",NULL,:host,NULL,:reason,:length,:time)');
            $prepared->bindParam(':host', @inet_pton($ban_input[$i]['host']), PDO::PARAM_STR);
            $prepared->bindParam(':reason', $ban_input[$i]['reason'], PDO::PARAM_STR);
            $prepared->bindParam(':length', $banlength, PDO::PARAM_INT);
            $prepared->bindParam(':time', time(), PDO::PARAM_INT);
            $prepared->execute();
            unset($prepared);
            ++ $i;
        }
    }
    else
    {
        $count_posts = count($ban_input);
        $i = 0;
        
        while ($i < $count_posts)
        {
            $prepared = $dbh->prepare('SELECT host,mod_comment FROM ' . POSTTABLE . ' WHERE post_number=:bannum');
            $prepared->bindParam(':bannum', $ban_input[$i]['num'], PDO::PARAM_INT);
            $prepared->execute();
            $baninfo1 = $prepared->fetch(PDO::FETCH_ASSOC);
            unset($prepared);
            
            if (!empty($baninfo1))
            {
                $prepared = $dbh->prepare('SELECT * FROM ' . BANTABLE . ' WHERE host=:host');
                $prepared->bindParam(':host', @inet_ntop($ban_input[$i]['host']), PDO::PARAM_STR);
                $result = $prepared->execute();
                
                if ($result != FALSE)
                {
                    $baninfo2 = $prepared->fetch(PDO::FETCH_ASSOC);
                    
                    if ($baninfo2['id'] && $baninfo2['board'] === TABLEPREFIX)
                    {
                        $dbh->query('DELETE FROM ' . BANTABLE . ' WHERE id=' . $baninfo2['id'] . '');
                    }
                }
                
                unset($prepared);
            }
            
            if ($ban_input[$i]['message'] !== '')
            {
                $mod_comment = $baninfo1['mod_comment'] . '<br>(' . $ban_input[$i]['message'] . ')';
                $prepared = $dbh->prepare('UPDATE ' . POSTTABLE . ' SET mod_comment=:mcomment WHERE post_number=:bannum');
                $prepared->bindParam(':mcomment', $mod_comment, PDO::PARAM_STR);
                $prepared->bindParam(':bannum', $ban_input[$i]['num'], PDO::PARAM_INT);
                $prepared->execute();
                unset($prepared);
            }
            
            $banlength = $ban_input[$i]['days'] + $ban_input[$i]['hours'];
            $prepared = $dbh->prepare('INSERT INTO ' . BANTABLE . ' (type,host,name,reason,length,ban_time) 
									VALUES (NULL,:host,:name,:reason,:length,:time)');
            $prepared->bindParam(':host', $baninfo1['host'], PDO::PARAM_STR);
            $prepared->bindParam(':name', $ban_input[$i]['name'], PDO::PARAM_STR);
            $prepared->bindParam(':reason', $ban_input[$i]['reason'], PDO::PARAM_STR);
            $prepared->bindParam(':length', $banlength, PDO::PARAM_INT);
            $prepared->bindParam(':time', time(), PDO::PARAM_INT);
            $prepared->execute();
            unset($prepared);
            ++ $i;
        }
    }
}
?>