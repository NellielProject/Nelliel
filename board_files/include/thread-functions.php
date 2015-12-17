<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function thread_updates($dataforce, $dbh)
{
    $threadlist = array();
    $postlist = array();
    $filelist = array();
    $returned_list = array();
    
    foreach ($_POST as $input)
    {
        $push = NULL;
        $sub = explode('_', $input, 4);
        
        switch ($sub[0])
        {
            case 'deletefile':
                delete_content($dataforce, $sub, 'FILE', $dbh);
                $push = $sub[1];
                break;
            
            case 'deletethread':
                delete_content($dataforce, $sub, 'THREAD', $dbh);
                $push = $sub[1];
                break;
            
            case 'deletepost':
                delete_content($dataforce, $sub, 'POST', $dbh);
                $push = $sub[2];
                break;
            
            case 'sticky':
                make_sticky($dataforce, $sub, $dbh);
                $push = $sub[1];
                break;
            
            case 'unsticky':
                unsticky($dataforce, $sub, $dbh);
                $push = $sub[1];
                break;
        }
        
        if ($push !== NULL)
        {
            if (!in_array($push, $returned_list))
            {
                array_push($returned_list, $push);
            }
        }
    }
    
    return $returned_list;
}

function make_sticky($dataforce, $sub, $dbh)
{
    $id = $sub[1];
    $result = $dbh->query('SELECT response_to,has_file,post_time FROM ' . POSTTABLE . ' WHERE post_number=' . $id . '');
    $post_data = $result->fetch(PDO::FETCH_ASSOC);
    unset($result);
    
    $dbh->query('UPDATE ' . POSTTABLE . ' SET response_to=0, sticky=1, last_update=' . $post_data['post_time'] . ' WHERE post_number=' . $id . '');
    create_thread_directories($id);
    
    if ($post_data['has_file'])
    {
        $dbh->query('UPDATE ' . FILETABLE . ' SET parent_thread=0 WHERE post_ref=' . $id . '');
        $result = $dbh->query('SELECT filename,extension,preview_name FROM ' . FILETABLE . ' WHERE post_ref=' . $id);
        $file_data = $result->fetchAll(PDO::FETCH_ASSOC);
        unset($result);
        
        $file_count = count($file_data);
        $line = 0;
        
        while ($line < $file_count)
        {
            move_file(SRC_PATH . $post_data['response_to'] . '/' . $file_data[$line]['filename'] . $file_data[$line]['extension'], SRC_PATH . $id . '/' . $file_data[$line]['filename'] . $file_data[$line]['extension']);
            move_file(THUMB_PATH . $post_data['response_to'] . '/' . $file_data[$line]['preview_name'], THUMB_PATH . $id . '/' . $file_data[$line]['preview_name']);
            
            ++ $line;
        }
    }
    
    $result = $dbh->query('SELECT post_count FROM ' . POSTTABLE . ' WHERE post_number=' . $post_data['response_to'] . '');
    $pcount = $result->fetch(PDO::FETCH_ASSOC);
    unset($result);
    $result = $dbh->query('SELECT post_number,post_time FROM ' . POSTTABLE . ' WHERE response_to=' . $post_data['response_to'] . ' ORDER BY post_number desc');
    $ptimes = $result->fetchAll(PDO::FETCH_ASSOC);
    unset($result);
    $dbh->query('UPDATE ' . POSTTABLE . ' SET post_count=' . ($pcount['post_count'] - 1) . ', last_update=' . $ptimes[0]['post_time'] . ', last_response=' . $ptimes[0]['post_number'] . ' WHERE post_number=' . $post_data['response_to'] . '');
    preg_replace('#p' . $id . 't' . $post_data['response_to'] . '#', 'p' . $id . 't0', $dataforce['post_links']);
    
    update_archive_status($dataforce, $dbh);
    
    if (!empty($_SESSION))
    {
        $temp = $_SESSION['ignore_login'];
        $_SESSION['ignore_login'] = TRUE;
    }
    
    if (!file_exists(PAGE_PATH . $id . '/' . $id . '.html'))
    {
        $dataforce['response_id'] = $id;
        regen($dataforce, $dataforce['response_id'], 'thread', FALSE, $dbh);
    }
    
    cache_links();
    $dataforce['archive_update'] = TRUE;
    regen($dataforce, NULL, 'main', FALSE, $dbh);
    
    if (!empty($_SESSION))
    {
        $_SESSION['ignore_login'] = $temp;
    }
}

function unsticky($dataforce, $sub, $dbh)
{
    $id = $sub[1];
    $dbh->query('UPDATE ' . POSTTABLE . ' SET sticky=0 WHERE post_number=' . $id . '');
    
    update_archive_status($dataforce, $dbh);
    
    if (!empty($_SESSION))
    {
        $temp = $_SESSION['ignore_login'];
        $_SESSION['ignore_login'] = TRUE;
    }
    
    if (!file_exists(PAGE_PATH . $id . '/' . $id . '.html'))
    {
        $dataforce['response_id'] = $id;
        regen($dataforce, $dataforce['response_id'], 'thread', FALSE, $dbh);
    }
    
    cache_post_links();
    $dataforce['archive_update'] = TRUE;
    regen($dataforce, NULL, 'main', FALSE, $dbh);
    
    if (!empty($_SESSION))
    {
        $_SESSION['ignore_login'] = $temp;
    }
}

function delete_content($dataforce, $sub, $type, $dbh)
{
    $id = $sub[1];
    
    if (!is_numeric($id))
    {
        derp(13, array('origin' => 'DELETE'));
    }
    
    $flag = FALSE;
    $hashed_pass = nel_hash($dataforce['pass']);
    $hashed_pass = substr($hashed_pass, 0, 16);
    $result = $dbh->query('SELECT post_number,password,response_to,mod_post FROM ' . POSTTABLE . ' WHERE post_number=' . $id . '');
    $post_data = $result->fetch(PDO::FETCH_ASSOC);
    unset($result);
    
    if (!empty($_SESSION) && !$_SESSION['ignore_login'])
    {
        $temp = $_SESSION['ignore_login'];
        
        if (is_authorized($_SESSION['username'], 'perm_delete'))
        {
            if ($post_data['mod_post'] === '0')
            {
                $flag = TRUE;
            }
            else
            {
                $staff_type = get_user_setting($_SESSION['username'], 'staff_type');
                
                if ($post_data['mod_post'] === '3' && $staff_type === 'admin')
                {
                    $flag = TRUE;
                }
                else if ($post_data['mod_post'] === '2' && $staff_type === 'admin' || $staff_type === 'moderator')
                {
                    $flag = TRUE;
                }
                else if ($flag = $post_data['mod_post'] === '1' && $staff_type === 'admin' || $staff_type === 'moderator' || $staff_type === 'janitor')
                {
                    $flag = TRUE;
                }
            }
        }
        
        $_SESSION['ignore_login'] = $flag ? TRUE : $temp;
    }
    else
    {
        $flag = $hashed_pass === $post_data['password'] ? TRUE : FALSE;
        $temp = TRUE;
    }
    
    if ($flag)
    {
        if ($type === 'THREAD')
        {
            $result = $dbh->query('SELECT post_number FROM ' . POSTTABLE . ' WHERE response_to=' . $id . ' OR post_number=' . $id . '');
            $content_refs = $result->fetchALL(PDO::FETCH_COLUMN, 0);
            unset($result);
            
            foreach ($content_refs as $ref)
            {
                $dbh->query('DELETE FROM ' . FILETABLE . ' WHERE post_ref=' . $ref . '');
                $dbh->query('DELETE FROM ' . POSTTABLE . ' WHERE post_number=' . $ref . '');
                preg_replace('#p([0-9]+)t' . $ref . '#', '', $dataforce['post_links']);
            }
            
            eraser_gun(PAGE_PATH . $id, NULL, TRUE);
            eraser_gun(SRC_PATH . $id, NULL, TRUE);
            eraser_gun(THUMB_PATH . $id, NULL, TRUE);
            
            update_archive_status($dataforce, $dbh);
        }
        else if ($type === 'POST')
        {
            $result = $dbh->query('SELECT filename,extension,preview_name FROM ' . FILETABLE . ' WHERE post_ref=' . $id . '');
            $file_data = $result->fetchAll(PDO::FETCH_ASSOC);
            unset($result);
            $dbh->query('DELETE FROM ' . FILETABLE . ' WHERE post_ref=' . $id . '');
            
            foreach ($file_data as $refs)
            {
                eraser_gun(SRC_PATH . $post_data['response_to'], $refs['filename'] . $refs['extension'], FALSE);
                
                if ($refs['preview_name'])
                {
                    eraser_gun(THUMB_PATH . $post_data['response_to'], $refs['preview_name'], FALSE);
                }
            }
            
            if ($dataforce['only_delete_file'])
            {
                $dbh->query('UPDATE ' . POSTTABLE . ' SET has_file=0 WHERE post_number=' . $id . '');
            }
            else
            {
                $dbh->query('DELETE FROM ' . POSTTABLE . ' WHERE post_number=' . $id . '');
                $result = $dbh->query('SELECT post_count FROM ' . POSTTABLE . ' WHERE post_number=' . $post_data['response_to'] . '');
                $pcount = $result->fetch(PDO::FETCH_ASSOC);
                unset($result);
                $result = $dbh->query('SELECT post_number,post_time FROM ' . POSTTABLE . ' WHERE response_to=' . $post_data['response_to'] . ' ORDER BY post_number desc');
                $ptimes = $result->fetchAll(PDO::FETCH_ASSOC);
                unset($result);
                $dbh->query('UPDATE ' . POSTTABLE . ' SET post_count=' . ($pcount['post_count'] - 1) . ', last_update=' . $ptimes[0]['post_time'] . ', last_response=' . $ptimes[0]['post_number'] . ' WHERE post_number=' . $post_data['response_to'] . '');
                preg_replace('#p' . $id . 't([0-9]+)#', '', $dataforce['post_links']);
            }
        }
        else if ($type === 'FILE')
        {
            // add check for updating post as no files if they're all gone
            $fnum = $sub[2];
            $result = $dbh->query('SELECT filename,extension,preview_name FROM ' . FILETABLE . ' WHERE post_ref=' . $id . ' AND file_order=' . $fnum . '');
            $file_data = $result->fetch(PDO::FETCH_ASSOC);
            unset($result);
            
            if ($file_data !== FALSE)
            {
                $dbh->query('DELETE FROM ' . FILETABLE . ' WHERE post_ref=' . $id . ' AND file_order=' . $fnum . '');
                
                if ($post_data['response_to'] == 0)
                {
                    eraser_gun(SRC_PATH . $post_data['post_number'], $file_data['filename'] . $file_data['extension'], FALSE);
                    if ($file_data['preview_name'])
                    {
                        eraser_gun(THUMB_PATH . $post_data['post_number'], $file_data['preview_name'], FALSE);
                    }
                }
                else
                {
                    eraser_gun(SRC_PATH . $post_data['response_to'], $file_data['filename'] . $file_data['extension'], FALSE);
                    if ($file_data['preview_name'])
                    {
                        eraser_gun(THUMB_PATH . $post_data['response_to'], $file_data['preview_name'], FALSE);
                    }
                }
            }
        }
        
        cache_links();
    }
    else
    {
        derp(20, array('origin' => 'DELETE'));
    }
    
    if (!empty($_SESSION))
    {
        $_SESSION['ignore_login'] = $temp;
    }
}

?>