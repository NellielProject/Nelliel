<?php
if (!defined (NELLIEL_VERSION))
{
    die ("NOPE.AVI");
}

function valid($dataforce)
{
    global $rendervar, $authorized;
    
    $dat = '';
    $rendervar['dotdot'] = '';
    if (!empty ($_SESSION))
    {
        
        $dat .= generate_header ($dataforce, 'ADMIN', array());
        $rendervar = array_merge ($rendervar, (array) $authorized[$_SESSION['username']]);
        $dat .= parse_template ('manage_options.tpl', FALSE);
        $dat .= footer ($authorized, FALSE, FALSE, FALSE, FALSE);
        echo $dat;
    }
    else
    {
        
        $dat .= generate_header ($dataforce, 'ADMIN', array());
        $dat .= parse_template ('manage_login.tpl', FALSE);
        $dat .= footer ($authorized, FALSE, FALSE, FALSE, FALSE);
        echo $dat;
    }
    
    die ();
}

//
// Update ban info
//
function update_ban($dataforce, $mode)
{
    global $dbh, $authorized;
    
    if ($authorized[$_SESSION['username']]['perm_ban_panel'])
    {
        if ($mode === 'remove')
        {
            $dbh->query ('DELETE FROM ' . BANTABLE . ' WHERE id=' . $dataforce['banid'] . '');
        }
        
        if ($mode === 'update')
        {
            $ban_input = array(
                    'days' => 0,
                    'hours' => 0,
                    'reason' => '',
                    'response' => '',
                    'review' => FALSE,
                    'status' => 0,
                    'length' => '' );
            
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
            
            $prepared = $dbh->prepare ('UPDATE ' . BANTABLE . ' SET reason=:reason, length=:length, appeal_response=:response, appeal_status=:status WHERE id=:banid');
            $prepared->bindParam (':reason', $ban_input['reason'], PDO::PARAM_STR);
            $prepared->bindParam (':length', $bantotal, PDO::PARAM_INT);
            $prepared->bindParam (':response', $ban_input['response'], PDO::PARAM_STR);
            $prepared->bindParam (':status', $ban_input['status'], PDO::PARAM_INT);
            $prepared->bindParam (':banid', $dataforce['banid'], PDO::PARAM_INT);
            $prepared->execute ();
            unset ($prepared);
        }
    }
    else
    {
        derp (31, LANG_ERROR_31, 'SEC', array(), '');
    }
}

//
// Staff control panel
// /

// This whole section is messy but works. Will clean up later. Really.
function staff_panel($dataforce, $mode)
{
    global $dbh, $rendervar, $authorized;
    
    $rendervar['dotdot'] = '';
    $rendervar['enter_staff'] = TRUE;
    $rendervar['edit_staff'] = FALSE;
    
    if ($authorized[$_SESSION['username']]['perm_staff_panel'] !== TRUE)
    {
        derp (32, LANG_ERROR_32, 'SEC', array(), '');
    }
    
    if ($mode === 'edit' || $mode === 'add')
    {
        
        if ($mode === 'add')
        {
            while ($item = each ($_POST))
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
            
            if (!isset ($authorized[$rendervar['staff_name']]))
            {
                gen_new_staff ($rendervar['staff_name'], $rendervar['staff_type']);
            }
        }
        else if ($mode === 'edit')
        {
            while ($item = each ($_POST))
            {
                if ($item[0] === 'staff_name')
                {
                    $rendervar['staff_name'] = $item[1];
                }
            }
            
            if (!isset ($authorized[$rendervar['staff_name']]))
            {
                derp (60, LANG_ERROR_60, 'SEC', array(), '');
            }
        }
        
        $temp_auth = $authorized[$rendervar['staff_name']];
        array_walk ($temp_auth, create_function ('&$item1', '$item1 = $item1 ? "checked" : "";'));
        $rendervar = array_merge ($rendervar, (array) $temp_auth);
        $rendervar['edit_staff'] = TRUE;
        $rendervar['enter_staff'] = FALSE;
    }
    else if ($mode === 'update')
    {
        $rendervar['enter_staff'] = TRUE;
        $rendervar['edit_staff'] = FALSE;
        $staff_name = $_POST['staff_name'];
        $old_pass = $authorized[$staff_name]['staff_password'];
        $new_pass = '';
        array_walk ($authorized[$staff_name], 'change_true_false');
        array_walk ($authorized[$staff_name], 'clear_auth_settings');
        $authorized[$staff_name]['staff_password'] = $old_pass;
        $change_pass = FALSE;
        
        foreach ($_POST as $key => $val)
        {
            if ($key === 'staff_password')
            {
                $new_pass = asdfg ($val);
            }
            
            if ($key === 'change_pass')
            {
                $change_pass = TRUE;
            }
            
            if ($change_pass && $new_pass != '')
            {
                $authorized[$staff_name]['staff_password'] = $new_pass;
                $change_pass = FALSE;
            }
            
            if ($key !== 'adminmode' && $item[0] !== 'mode' && $key !== 'username' && $key !== 'super_sekrit' && $key !== 'staff_password' && $key !== 'change_pass')
            {
                if ($val === '1')
                {
                    $val = TRUE;
                    $authorized[$staff_name][$key] = $val;
                }
                else
                {
                    $authorized[$staff_name][$key] = $val;
                }
            }
        }
        
        $new_auth = '<?php
		
$authorized = ' . var_export ($authorized, TRUE) . '?>';
        write_file (FILES_PATH . '/auth_data.nel.php', $new_auth, 0644);
    }
    else if ($mode === 'delete')
    {
        $rendervar['enter_staff'] = TRUE;
        $rendervar['edit_staff'] = FALSE;
        unset ($authorized[$_POST['staff_name']]);
        $new_auth = '<?php
		
$authorized = ' . var_export ($authorized, TRUE) . '?>';
        write_file (FILES_PATH . '/auth_data.nel.php', $new_auth, 0644);
    }
    
    $dat = generate_header ($dataforce, 'ADMIN', array());
    $dat .= parse_template ('staff_panel.tpl', FALSE);
    $dat .= footer ($authorized, FALSE, FALSE, FALSE, FALSE);
    echo $dat;
    die ();
}

function gen_new_staff($new_name, $new_type)
{
    global $authorized;
    
    if ($new_type === 'admin')
    {
        $authorized[$new_name] = array(
                'staff_password' => '',
                'staff_type' => 'admin',
                'staff_trip' => '',
                'perm_config' => TRUE,
                'perm_staff_panel' => TRUE,
                'perm_ban_panel' => TRUE,
                'perm_thread_panel' => TRUE,
                'perm_mod_mode' => TRUE,
                'perm_ban' => TRUE,
                'perm_delete' => TRUE,
                'perm_post' => TRUE,
                'perm_post_anon' => FALSE,
                'perm_sticky' => TRUE,
                'perm_update_pages' => TRUE,
                'perm_update_cache' => TRUE );
    }
    else if ($new_type === 'moderator')
    {
        $authorized[$new_name] = array(
                'staff_password' => '',
                'staff_type' => 'moderator',
                'staff_trip' => '',
                'perm_config' => FALSE,
                'perm_staff_panel' => FALSE,
                'perm_ban_panel' => TRUE,
                'perm_thread_panel' => TRUE,
                'perm_mod_mode' => TRUE,
                'perm_ban' => TRUE,
                'perm_delete' => TRUE,
                'perm_post' => TRUE,
                'perm_post_anon' => TRUE,
                'perm_sticky' => TRUE,
                'perm_update_pages' => FALSE,
                'perm_update_cache' => FALSE );
    }
    else if ($new_type === 'janitor')
    {
        $authorized[$new_name] = array(
                'staff_password' => '',
                'staff_type' => 'janitor',
                'staff_trip' => '',
                'perm_config' => FALSE,
                'perm_staff_panel' => FALSE,
                'perm_ban_panel' => FALSE,
                'perm_thread_panel' => FALSE,
                'perm_mod_mode' => TRUE,
                'perm_ban' => FALSE,
                'perm_delete' => TRUE,
                'perm_post' => FALSE,
                'perm_post_anon' => TRUE,
                'perm_sticky' => FALSE,
                'perm_update_pages' => FALSE,
                'perm_update_cache' => FALSE );
    }
    else
    {
        derp (61, LANG_ERROR_61, 'SEC', array(), '');
    }
    
    $new_auth = '<?php
		
$authorized = ' . var_export ($authorized, TRUE) . '?>';
    write_file (FILES_PATH . '/auth_data.nel.php', $new_auth, 0644);
    return $authorized;
}

function change_true_false(&$item1, $key)
{
    if (is_bool ($item1))
    {
        $item1 = FALSE;
    }
}

function clear_auth_settings(&$item1, $key)
{
    if (is_string ($item1))
    {
        $item1 = '';
    }
}

//
// Board settings
//
function admin_control($dataforce, $mode)
{
    global $dbh, $rendervar, $rule_list, $authorized;
    
    $rendervar['dotdot'] = '';
    $update = FALSE;
    
    if ($authorized[$_SESSION['username']]['perm_config'] !== TRUE)
    {
        derp (32, LANG_ERROR_32, 'SEC', array(), '');
    }
    
    if ($mode === 'set')
    {
        // Apply settings from admin panel
        
        $dbh->query ('UPDATE ' . CONFIGTABLE . ' SET setting=""');
        
        while ($item = each ($_POST))
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
                
                $dbh->query ('UPDATE ' . CONFIGTABLE . ' SET setting="' . $item[1] . '" WHERE config_name="' . $item[0] . '"');
            }
        }
        
        $rule_list = cache_rules ();
        cache_settings ();
        regen ($dataforce, NULL, 'full', FALSE);
    }
    
    $nolink = FALSE;
    
    // Get Filetype settings
    $result = $dbh->query ('SELECT * FROM ' . CONFIGTABLE . '');
    
    $rows = $result->fetchAll (PDO::FETCH_ASSOC);
    unset ($result);
    $board_settings = array(
            'iso' => '',
            'com' => '',
            'us' => '',
            'archive' => '',
            'prune' => '',
            'nothing' => '' );
    
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
    
    $rendervar = array_merge ($rendervar, (array) $board_settings);
    
    $dat = generate_header ($dataforce, 'ADMIN', array());
    $dat .= parse_template ('admin_panel.tpl', FALSE);
    $dat .= footer ($authorized, FALSE, FALSE, FALSE, FALSE);
    echo $dat;
    die ();
}

//
// Ban control panel
//
function ban_control($dataforce, $mode)
{
    global $dbh, $rendervar, $authorized;
    
    $rendervar['dotdot'] = '';
    
    if ($authorized[$_SESSION['username']]['perm_ban_panel'] !== TRUE)
    {
        derp (31, LANG_ERROR_31, 'SEC', array(), '');
    }
    
    $dat = '';
    
    if ($mode === 'list')
    {
        $dat .= generate_header ($dataforce, 'ADMIN', array());
        $dat .= generate_ban_panel ($dataforce, array(), 'HEAD');
        $result = $dbh->query ('SELECT * FROM ' . BANTABLE . ' ORDER BY id DESC');
        
        $j = 0;
        while ($baninfo = $result->fetch (PDO::FETCH_ASSOC))
        {
            if ($baninfo['type'] === 'SPAMBOT')
            {
                ;
            }
            else
            {
                $dataforce['j_increment'] = $j;
                $dat .= generate_ban_panel ($dataforce, $baninfo, 'LIST');
            }
            ++ $j;
        }
        
        unset ($result);
        $dat .= generate_ban_panel ($dataforce, array(), 'END');
        $dat .= footer ($authorized, FALSE, FALSE, FALSE, FALSE);
        echo $dat;
    }
    else if ($mode === 'modify')
    {
        $result = $dbh->query ('SELECT * FROM ' . BANTABLE . ' WHERE id=' . $dataforce['banid'] . '');
        $baninfo = $result->fetch (PDO::FETCH_ASSOC);
        unset ($result);
        
        $dat .= generate_header ($dataforce, 'ADMIN', array());
        $dat .= generate_ban_panel ($dataforce, $baninfo, 'MODIFY');
        $dat .= footer ($authorized, FALSE, FALSE, FALSE, FALSE);
        echo $dat;
    }
    else if ($mode === 'new')
    {
        $dat .= generate_header ($dataforce, 'ADMIN', array());
        $dat .= generate_ban_panel ($dataforce, array(), 'ADD');
        $dat .= footer ($authorized, FALSE, FALSE, FALSE, FALSE);
        echo $dat;
    }
    
    die ();
}

//
// Thread management panel
//
function thread_panel($dataforce, $mode)
{
    global $dbh, $rendervar, $authorized;
    
    // $rendervar['dotdot'] = '';
    $rendervar['expand_thread'] = FALSE;
    
    if ($authorized[$_SESSION['username']]['perm_thread_panel'] !== TRUE)
    {
        derp (33, LANG_ERROR_33, 'SEC', array(), '');
    }
    
    if ($mode === 'update')
    {
        $updates = thread_updates ($dataforce);
        regen ($dataforce, $updates, 'thread', FALSE);
        regen ($dataforce, NULL, 'main', FALSE);
    }
    
    $dat = generate_header ($dataforce, 'ADMIN', array());
    $dat .= generate_thread_panel ($dataforce, array(), 'FORM');
    
    if ($mode === 'expand')
    {
        $thread_id = str_replace ('Expand ', '', $_POST['expand_thread']);
        $rendervar['expand_thread'] = TRUE;
        $prepared = $dbh->prepare ('SELECT * FROM ' . POSTTABLE . ' WHERE response_to=:threadid OR post_number=:threadid2 ORDER BY post_number ASC');
        $prepared->bindParam (':threadid', $thread_id, PDO::PARAM_INT);
        $prepared->bindParam (':threadid2', $thread_id, PDO::PARAM_INT); // This really shouldn't be necessary :|
        $prepared->execute ();
    }
    else
    {
        $prepared = $dbh->query ('SELECT * FROM ' . POSTTABLE . ' WHERE response_to=0 ORDER BY post_number DESC');
    }
    
    $j = 0;
    $all = 0;
    $thread_data = $prepared->fetchALL (PDO::FETCH_ASSOC);
    unset ($prepared);
    $post_count = count ($thread_data);
    
    foreach ($thread_data as $thread)
    {
        if ($thread['has_file'] === '1')
        {
            $result = $dbh->query ('SELECT * FROM ' . FILETABLE . ' WHERE post_ref=' . $thread['post_number'] . ' ORDER BY ord asc');
            $thread['files'] = $result->fetchALL (PDO::FETCH_ASSOC);
            unset ($result);
            $thread['filesize_total'] = 0;
            
            foreach ($thread['files'] as $file)
            {
                $thread['filesize_total'] += $file['filesize'];
            }
            
            $all += $thread['filesize_total'];
        }
        
        $dataforce['j_increment'] = $j;
        $dat .= generate_thread_panel ($dataforce, $thread, 'THREAD');
        $j ++;
    }
    
    $dataforce['all_filesize'] = (int) ($all / 1024);
    $dat .= generate_thread_panel ($dataforce, $thread_data, 'END');
    $dat .= footer ($authorized, FALSE, FALSE, FALSE, FALSE);
    echo $dat;
    die ();
}

//
// Apply b&hammer
//
function ban_hammer($dataforce)
{
    global $dbh, $authorized;
    
    $ban_input = array();
    
    if ($dataforce['admin_mode'] === 'add_ban')
    {
        $prepared = $dbh->prepare ('INSERT INTO ' . BANTABLE . ' (board,type,host,name,reason,length,ban_time) 
								VALUES ("' . POSTTABLE . '",NULL,NULL,NULL,:reason,:length,' . time () . ')');
        $prepared->bindParam (':host', @inet_pton ($dataforce['banip']), PDO::PARAM_STR);
        $prepared->bindParam (':reason', $dataforce['banreason'], PDO::PARAM_STR);
        $prepared->bindParam (':length', (($dataforce['timedays'] * 86400) + ($dataforce['timehours'] * 3600)), PDO::PARAM_INT);
        $prepared->execute ();
        unset ($prepared);
        return;
    }
    
    reset ($_POST);
    
    $manual = FALSE;
    $manual_host = '';
    $i = 0;
    $current_num = '';
    
    while ($item = each ($_POST))
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
            $ban_input[$i] = array(
                    'num' => $item[1],
                    'days' => 0,
                    'hours' => 0,
                    'message' => '',
                    'reason' => '',
                    'name' => '',
                    'host' => '' );
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
        $count_posts = count ($ban_input);
        $i = 0;
        while ($i < $count_posts)
        {
            $banlength = $ban_input[$i]['days'] + $ban_input[$i]['hours'];
            $prepared = $dbh->prepare ('INSERT INTO ' . BANTABLE . ' (board,type,host,name,reason,length,ban_time) 
									VALUES ("' . POSTTABLE . '",NULL,:host,NULL,:reason,:length,:time)');
            $prepared->bindParam (':host', @inet_pton ($ban_input[$i]['host']), PDO::PARAM_STR);
            $prepared->bindParam (':reason', $ban_input[$i]['reason'], PDO::PARAM_STR);
            $prepared->bindParam (':length', $banlength, PDO::PARAM_INT);
            $prepared->bindParam (':time', time (), PDO::PARAM_INT);
            $prepared->execute ();
            unset ($prepared);
            ++ $i;
        }
    }
    else
    {
        $count_posts = count ($ban_input);
        $i = 0;
        
        while ($i < $count_posts)
        {
            $prepared = $dbh->prepare ('SELECT host,mod_comment FROM ' . POSTTABLE . ' WHERE post_number=:bannum');
            $prepared->bindParam (':bannum', $ban_input[$i]['num'], PDO::PARAM_INT);
            $prepared->execute ();
            $baninfo1 = $prepared->fetch (PDO::FETCH_ASSOC);
            unset ($prepared);
            
            if (!empty ($baninfo1))
            {
                $prepared = $dbh->prepare ('SELECT * FROM ' . BANTABLE . ' WHERE host=:host');
                $prepared->bindParam (':host', @inet_ntop ($ban_input[$i]['host']), PDO::PARAM_STR);
                $result = $prepared->execute ();
                
                if ($result != FALSE)
                {
                    $baninfo2 = $prepared->fetch (PDO::FETCH_ASSOC);
                    
                    if ($baninfo2['id'] && $baninfo2['board'] === TABLEPREFIX)
                    {
                        $dbh->query ('DELETE FROM ' . BANTABLE . ' WHERE id=' . $baninfo2['id'] . '');
                    }
                }
                
                unset ($prepared);
            }
            
            if ($ban_input[$i]['message'] !== '')
            {
                $mod_comment = $baninfo1['mod_comment'] . '<br>(' . $ban_input[$i]['message'] . ')';
                $prepared = $dbh->prepare ('UPDATE ' . POSTTABLE . ' SET mod_comment=:mcomment WHERE post_number=:bannum');
                $prepared->bindParam (':mcomment', $mod_comment, PDO::PARAM_STR);
                $prepared->bindParam (':bannum', $ban_input[$i]['num'], PDO::PARAM_INT);
                $prepared->execute ();
                unset ($prepared);
            }
            
            $banlength = $ban_input[$i]['days'] + $ban_input[$i]['hours'];
            $prepared = $dbh->prepare ('INSERT INTO ' . BANTABLE . ' (type,host,name,reason,length,ban_time) 
									VALUES (NULL,:host,:name,:reason,:length,:time)');
            $prepared->bindParam (':host', $baninfo1['host'], PDO::PARAM_STR);
            $prepared->bindParam (':name', $ban_input[$i]['name'], PDO::PARAM_STR);
            $prepared->bindParam (':reason', $ban_input[$i]['reason'], PDO::PARAM_STR);
            $prepared->bindParam (':length', $banlength, PDO::PARAM_INT);
            $prepared->bindParam (':time', time (), PDO::PARAM_INT);
            $prepared->execute ();
            unset ($prepared);
            ++ $i;
        }
    }
}
?>