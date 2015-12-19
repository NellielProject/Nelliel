<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function valid($dataforce)
{
    global $rendervar;
    
    $dat = '';
    $rendervar['dotdot'] = '';

    if (!empty($_SESSION))
    {
        $dat .= generate_header($dataforce, 'ADMIN', array());
        $rendervar = array_merge($rendervar, get_user_auth($_SESSION['username']));
        $dat .= parse_template('manage_options.tpl', FALSE);
        $dat .= footer(FALSE, FALSE, FALSE, FALSE);
        echo $dat;
    }
    else
    {
        $dat .= generate_header($dataforce, 'ADMIN', array());
        $dat .= parse_template('manage_login.tpl', FALSE);
        $dat .= footer(FALSE, FALSE, FALSE, FALSE);
        echo $dat;
    }
}

//
// Update ban info
//
function update_ban($dataforce, $mode, $dbh)
{
    if (!is_authorized($_SESSION['username'], 'perm_ban_panel'))
    {
        derp(101, array('origin' => 'ADMIN'));
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
function staff_panel($dataforce, $mode, $dbh)
{
    global $rendervar;
    
    $rendervar['dotdot'] = '';
    $rendervar['enter_staff'] = TRUE;
    $rendervar['edit_staff'] = FALSE;
    
    if (!is_authorized($_SESSION['username'], 'perm_staff_panel'))
    {
        derp(102, array('origin' => 'ADMIN'));
    }
    
    if ($mode === 'edit' || $mode === 'add')
    {
        
        if ($mode === 'add')
        {
            while ($item = each($_POST))
            {
                if ($item[0] === 'staff_name')
                {
                    $rendervar['staff_name'] = $item[1];
                }
                else if ($item[0] === 'staff_type')
                {
                    $rendervar['staff_type'] = $item[1];
                }
            }
            
            if (!get_user_auth($rendervar['staff_name']))
            {
                gen_new_staff($rendervar['staff_name'], $rendervar['staff_type']);
            }
        }
        else if ($mode === 'edit')
        {
            while ($item = each($_POST))
            {
                if ($item[0] === 'staff_name')
                {
                    $rendervar['staff_name'] = $item[1];
                }
            }
            
            if (!get_user_auth($rendervar['staff_name']))
            {
                derp(150, array('origin' => 'ADMIN'));
            }
        }
        
        $temp_auth = get_user_auth($rendervar['staff_name']);
        array_walk($temp_auth, create_function('&$item1', '$item1 = is_bool($item1) ? $item1 === TRUE ? "checked" : "" : $item1;'));
        $rendervar = array_merge($rendervar, $temp_auth);
        $rendervar['edit_staff'] = TRUE;
        $rendervar['enter_staff'] = FALSE;
    }
    else if ($mode === 'update')
    {
        $rendervar['enter_staff'] = TRUE;
        $rendervar['edit_staff'] = FALSE;
        $staff_name = $_POST['staff_name'];
        $old_pass = get_user_setting($staff_name, 'staff_password');
        $new_pass = '';
        //array_walk($authorized[$staff_name], 'change_true_false');
        //array_walk($authorized[$staff_name], 'clear_auth_settings');
        //$authorized[$staff_name]['staff_password'] = $old_pass;
        //$change_pass = FALSE;
        $new_auth = get_blank_settings();
        
        foreach ($_POST as $key => $val)
        {
            if ($key === 'staff_password')
            {
                $new_pass = nel_hash($val);
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
            
            update_user_auth($staff_name, $new_auth);
        }
        
        write_auth_file();
    }
    else if ($mode === 'delete')
    {
        $rendervar['enter_staff'] = TRUE;
        $rendervar['edit_staff'] = FALSE;
        remove_user_auth($_POST['staff_name']);
        write_auth_file();
    }
    
    $dat = generate_header($dataforce, 'ADMIN', array());
    $dat .= parse_template('manage_staff_panel.tpl', FALSE);
    $dat .= footer(FALSE, FALSE, FALSE, FALSE);
    echo $dat;
}

function gen_new_staff($new_name, $new_type)
{
    $new_auth = get_blank_settings();
    
    if ($new_type === 'admin')
    {
        update_user_setting($new_name, 'perm_config', TRUE);
        update_user_setting($new_name, 'perm_staff_panel', TRUE);
        update_user_setting($new_name, 'perm_ban_panel', TRUE);
        update_user_setting($new_name, 'perm_thread_panel', TRUE);
        update_user_setting($new_name, 'perm_mod_mode', TRUE);
        update_user_setting($new_name, 'perm_ban', TRUE);
        update_user_setting($new_name, 'perm_delete', TRUE);
        update_user_setting($new_name, 'perm_post', TRUE);
        update_user_setting($new_name, 'perm_post_anon', TRUE);
        update_user_setting($new_name, 'perm_sticky', TRUE);
        update_user_setting($new_name, 'perm_update_pages', TRUE);
        update_user_setting($new_name, 'perm_update_cache', TRUE);
    }
    else if ($new_type === 'moderator')
    {
        update_user_setting($new_name, 'perm_config', FALSE);
        update_user_setting($new_name, 'perm_staff_panel', FALSE);
        update_user_setting($new_name, 'perm_ban_panel', TRUE);
        update_user_setting($new_name, 'perm_thread_panel', TRUE);
        update_user_setting($new_name, 'perm_mod_mode', TRUE);
        update_user_setting($new_name, 'perm_ban', TRUE);
        update_user_setting($new_name, 'perm_delete', TRUE);
        update_user_setting($new_name, 'perm_post', TRUE);
        update_user_setting($new_name, 'perm_post_anon', TRUE);
        update_user_setting($new_name, 'perm_sticky', TRUE);
        update_user_setting($new_name, 'perm_update_pages', FALSE);
        update_user_setting($new_name, 'perm_update_cache', FALSE);
    }
    else if ($new_type === 'janitor')
    {
        update_user_setting($new_name, 'perm_config', FALSE);
        update_user_setting($new_name, 'perm_staff_panel', FALSE);
        update_user_setting($new_name, 'perm_ban_panel', FALSE);
        update_user_setting($new_name, 'perm_thread_panel', FALSE);
        update_user_setting($new_name, 'perm_mod_mode', TRUE);
        update_user_setting($new_name, 'perm_ban', FALSE);
        update_user_setting($new_name, 'perm_delete', TRUE);
        update_user_setting($new_name, 'perm_post', FALSE);
        update_user_setting($new_name, 'perm_post_anon', FALSE);
        update_user_setting($new_name, 'perm_sticky', FALSE);
        update_user_setting($new_name, 'perm_update_pages', FALSE);
        update_user_setting($new_name, 'perm_update_cache', FALSE);
    }
    else
    {
        derp(151, array('origin' => 'ADMIN'));
    }

    write_auth_file();
}

function change_true_false(&$item1, $key)
{
    if (is_bool($item1))
    {
        $item1 = FALSE;
    }
}

function clear_auth_settings(&$item1, $key)
{
    if (is_string($item1))
    {
        $item1 = '';
    }
}

//
// Board settings
//
function admin_control($dataforce, $mode, $dbh)
{
    global $rendervar;
    
    $rendervar['dotdot'] = '';
    $update = FALSE;
    
    if (!is_authorized($_SESSION['username'], 'perm_config'))
    {
        derp(102, array('origin' => 'ADMIN'));
    }
    
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
        
        cache_rules($dbh);
        cache_settings($dbh);
        regen($dataforce, NULL, 'full', FALSE, $dbh);
    }
    
    $nolink = FALSE;
    
    // Get Filetype settings
    $result = $dbh->query('SELECT * FROM ' . CONFIGTABLE . '');
    
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    unset($result);
    $board_settings = array('iso' => '', 'com' => '', 'us' => '', 'archive' => '', 'prune' => '', 'nothing' => '');
    
    foreach ($rows as $config_line)
    {
        if ($config_line['config_type'] !== 'board_setting')
        {
            if ($config_line['setting'] === '1')
            {
                $board_settings[$config_line['config_name']] = 'checked';
            }
            else
            {
                $board_settings[$config_line['config_name']] = '';
            }
        }
        else if ($config_line['config_type'] === 'board_setting')
        {
            switch ($config_line['setting'])
            {
                case 'ISO':
                    $board_settings['iso'] = 'checked';
                    break;
                
                case 'COM':
                    $board_settings['com'] = 'checked';
                    break;
                
                case 'US':
                    $board_settings['us'] = 'checked';
                    break;
                
                case 'ARCHIVE':
                    $board_settings['archive'] = 'checked';
                    break;
                
                case 'PRUNE':
                    $board_settings['prune'] = 'checked';
                    break;
                
                case 'NOTHING':
                    $board_settings['nothing'] = 'checked';
                    break;
                
                default:
                    $board_settings[$config_line['config_name']] = $config_line['setting'];
            }
        }
    }
    
    $rendervar = array_merge($rendervar, (array) $board_settings);
    
    $dat = generate_header($dataforce, 'ADMIN', array());
    $dat .= parse_template('admin_panel.tpl', FALSE);
    $dat .= footer(FALSE, FALSE, FALSE, FALSE);
    echo $dat;
}

//
// Ban control panel
//
function ban_control($dataforce, $mode, $dbh)
{
    global $rendervar;
    
    $rendervar['dotdot'] = '';
    
    if (!is_authorized($_SESSION['username'], 'perm_ban_panel'))
    {
        derp(101, array('origin' => 'ADMIN'));
    }
    
    $dat = '';
    
    if ($mode === 'list')
    {
        $dat .= generate_header($dataforce, 'ADMIN', array());
        $dat .= generate_ban_panel($dataforce, array(), 'HEAD');
        $result = $dbh->query('SELECT * FROM ' . BANTABLE . ' ORDER BY id DESC');
        
        $j = 0;
        while ($baninfo = $result->fetch(PDO::FETCH_ASSOC))
        {
            if ($baninfo['type'] === 'SPAMBOT')
            {
                ;
            }
            else
            {
                $dataforce['j_increment'] = $j;
                $dat .= generate_ban_panel($dataforce, $baninfo, 'LIST');
            }
            ++ $j;
        }
        
        unset($result);
        $dat .= generate_ban_panel($dataforce, array(), 'END');
        $dat .= footer(FALSE, FALSE, FALSE, FALSE);
        echo $dat;
    }
    else if ($mode === 'modify')
    {
        $result = $dbh->query('SELECT * FROM ' . BANTABLE . ' WHERE id=' . $dataforce['banid'] . '');
        $baninfo = $result->fetch(PDO::FETCH_ASSOC);
        unset($result);
        
        $dat .= generate_header($dataforce, 'ADMIN', array());
        $dat .= generate_ban_panel($dataforce, $baninfo, 'MODIFY');
        $dat .= footer(FALSE, FALSE, FALSE, FALSE);
        echo $dat;
    }
    else if ($mode === 'new')
    {
        $dat .= generate_header($dataforce, 'ADMIN', array());
        $dat .= generate_ban_panel($dataforce, array(), 'ADD');
        $dat .= footer(FALSE, FALSE, FALSE, FALSE);
        echo $dat;
    }
}

//
// Thread management panel
//
function thread_panel($dataforce, $mode, $dbh)
{
    global $rendervar;
    
    // $rendervar['dotdot'] = '';
    $rendervar['expand_thread'] = FALSE;
    
    if (!is_authorized($_SESSION['username'], 'perm_thread_panel'))
    {
        derp(103, array('origin' => 'ADMIN'));
    }
    
    if ($mode === 'update')
    {
        $updates = thread_updates($dataforce, $dbh);
        regen($dataforce, $updates, 'thread', FALSE, $dbh);
        regen($dataforce, NULL, 'main', FALSE, $dbh);
    }
    
    $dat = generate_header($dataforce, 'ADMIN', array());
    $dat .= generate_thread_panel($dataforce, array(), 'FORM');
    
    if ($mode === 'expand')
    {
        $thread_id = utf8_str_replace('Expand ', '', $_POST['expand_thread']);
        $rendervar['expand_thread'] = TRUE;
        $prepared = $dbh->prepare('SELECT * FROM ' . POSTTABLE . ' WHERE response_to=:threadid OR post_number=:threadid2 ORDER BY post_number ASC');
        $prepared->bindParam(':threadid', $thread_id, PDO::PARAM_INT);
        $prepared->bindParam(':threadid2', $thread_id, PDO::PARAM_INT); // This really shouldn't be necessary :|
        $prepared->execute();
    }
    else
    {
        $prepared = $dbh->query('SELECT * FROM ' . POSTTABLE . ' WHERE response_to=0 ORDER BY post_number DESC');
    }
    
    $j = 0;
    $all = 0;
    $thread_data = $prepared->fetchALL(PDO::FETCH_ASSOC);
    unset($prepared);
    $post_count = count($thread_data);
    
    foreach ($thread_data as $thread)
    {
        if ($thread['has_file'] === '1')
        {
            $result = $dbh->query('SELECT * FROM ' . FILETABLE . ' WHERE post_ref=' . $thread['post_number'] . ' ORDER BY file_order asc');
            $thread['files'] = $result->fetchALL(PDO::FETCH_ASSOC);
            unset($result);
            $thread['filesize_total'] = 0;
            
            foreach ($thread['files'] as $file)
            {
                $thread['filesize_total'] += $file['filesize'];
            }
            
            $all += $thread['filesize_total'];
        }
        
        $dataforce['j_increment'] = $j;
        $dat .= generate_thread_panel($dataforce, $thread, 'THREAD');
        $j ++;
    }
    
    $dataforce['all_filesize'] = (int) ($all / 1024);
    $dat .= generate_thread_panel($dataforce, $thread_data, 'END');
    $dat .= footer(FALSE, FALSE, FALSE, FALSE);
    echo $dat;
}

//
// Apply b&hammer
//
function ban_hammer($dataforce, $dbh)
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