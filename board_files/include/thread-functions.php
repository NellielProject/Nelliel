<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_thread_updates($dataforce)
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
                nel_delete_content($dataforce, $sub, 'FILE');
                $push = $sub[1];
                break;

            case 'deletethread':
                nel_delete_content($dataforce, $sub, 'THREAD');
                $push = $sub[1];
                break;

            case 'deletepost':
                nel_delete_content($dataforce, $sub, 'POST');
                $push = $sub[2];
                break;

            case 'threadsticky':
                nel_make_thread_sticky($dataforce, $sub);
                $push = $sub[1];
                break;

            case 'threadunsticky':
                nel_unsticky_thread($dataforce, $sub);
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

function nel_make_thread_sticky($dataforce, $sub)
{
    $dbh = nel_get_db_handle();
    $id = $sub[1];
    $result = $dbh->query('SELECT parent_thread FROM "' . POST_TABLE . '" WHERE "post_number" = ' . $id . '');
    $post_data = $result->fetch(PDO::FETCH_ASSOC);
    unset($result);

    // If this is not already a thread, make the post into one
    if ($post_data['parent_thread'] != $id)
    {
        nel_make_post_thread($dataforce, $id);
    }

    $dbh->query('UPDATE "' . THREAD_TABLE . '" SET "sticky" = 1 WHERE "thread_id" = ' . $id . '');
    nel_update_archive_status($dataforce);
    nel_regen($dataforce, $id, 'thread', FALSE);
    nel_regen($dataforce, NULL, 'main', FALSE);
    return;
}

function nel_unsticky_thread($dataforce, $sub)
{
    $dbh = nel_get_db_handle();
    $id = $sub[1];
    $dbh->query('UPDATE "' . THREAD_TABLE . '" SET "sticky" = 0 WHERE "thread_id" = ' . $id . '');
    nel_update_archive_status($dataforce, $dbh);
    nel_toggle_session();
    $dataforce['response_id'] = $id;
    nel_regen($dataforce, $dataforce['response_id'], 'thread', FALSE);
    $dataforce['archive_update'] = TRUE;
    nel_regen($dataforce, NULL, 'main', FALSE);
    nel_toggle_session();
}

function nel_get_thread_data($thread_id)
{
    $dbh = nel_get_db_handle();
    $result = $dbh->query('SELECT *  FROM "' . THREAD_TABLE . '" WHERE "thread_id" = ' . $thread_id . '');
    $thread_data = $result->fetch(PDO::FETCH_ASSOC);
    return $thread_data;
}

function nel_get_thread_all_posts($thread_id)
{
    $dbh = nel_get_db_handle();
    $result = $dbh->query('SELECT * FROM "' . POST_TABLE . '" WHERE "parent_thread" = ' . $thread_id . '');
    $thread_posts = $result->fetchAll(PDO::FETCH_ASSOC);
    return $thread_posts;
}

function nel_get_post_data($post_id)
{
    $dbh = nel_get_db_handle();
    $result = $dbh->query('SELECT *  FROM "' . POST_TABLE . '" WHERE "post_number" = ' . $post_id . '');
    $post_data = $result->fetch(PDO::FETCH_ASSOC);
    return $post_data;
}

function nel_get_thread_last_post($thread_id)
{
    $dbh = nel_get_db_handle();
    $result = $dbh->query('SELECT *  FROM "' . POST_TABLE . '" WHERE "parent_thread" = ' . $thread_id .
         ' ORDER BY "post_number" DESC LIMIT 1');
    $post_data = $result->fetch(PDO::FETCH_ASSOC);
    return $post_data;
}

function nel_get_thread_last_nosage_post($thread_id)
{
    $dbh = nel_get_db_handle();
    $result = $dbh->query('SELECT *  FROM "' . POST_TABLE . '" WHERE "parent_thread" = ' . $thread_id .
         ' AND "sage" = 0 ORDER BY "post_number" DESC LIMIT 1');
    $post_data = $result->fetch(PDO::FETCH_ASSOC);
    return $post_data;
}

function nel_get_thread_second_last_post($thread_id)
{
    $dbh = nel_get_db_handle();
    $result = $dbh->query('SELECT *  FROM "' . POST_TABLE . '" WHERE "parent_thread" = ' . $thread_id .
         ' ORDER BY "post_number" DESC LIMIT 2');
    $post_data = $result->fetchAll(PDO::FETCH_ASSOC);

    if (array_key_exists(1, $post_data))
    {
        return $post_data[1];
    }

    return false;
}

function nel_make_post_thread($dataforce, $post_id)
{
    $dbh = nel_get_db_handle();
    nel_create_thread_directories($post_id);
    $post_data = nel_get_post_data($post_id);

    $prepared = $dbh->prepare('INSERT INTO "' . THREAD_TABLE . '" (
        thread_id,
        first_post,
        last_post,
        last_bump_time,
        total_files,
        total_external,
        last_update,
        post_count,
        sticky)
    VALUES
	   (:id,
        :first,
        :last,
        :bump,
        :files,
        :externals,
        :time,
        1,
        1)');

    $prepared->bindValue(':id', $post_id, PDO::PARAM_INT);
    $prepared->bindValue(':first', $post_id, PDO::PARAM_INT);
    $prepared->bindValue(':last', $post_id, PDO::PARAM_INT);
    $prepared->bindValue(':bump', $post_data['post_time']);
    $prepared->bindValue(':files', $post_data['file_count'], PDO::PARAM_INT);
    $prepared->bindValue(':externals', $post_data['external_count'], PDO::PARAM_INT);
    $prepared->bindValue(':time', $post_data['post_time'], PDO::PARAM_INT);
    $prepared->execute();
    $prepared->closeCursor();

    $dbh->query('UPDATE "' . POST_TABLE . '" SET "parent_thread" = ' . $post_id . ', "op" = 1 WHERE "post_number" = ' .
         $post_id);

    if ($post_data['has_file'])
    {
        $dbh->query('UPDATE ' . FILE_TABLE . ' SET parent_thread=' . $post_id . ' WHERE post_ref=' . $post_id . '');
        $result = $dbh->query('SELECT filename,extension,preview_name FROM ' . FILE_TABLE . ' WHERE post_ref=' .
             $post_id);
        $file_data = $result->fetchAll(PDO::FETCH_ASSOC);
        unset($result);
        $file_count = count($file_data);
        $line = 0;

        while ($line < $file_count)
        {
            nel_move_file(SRC_PATH . $post_data['parent_thread'] . '/' . $file_data[$line]['filename'] .
                 $file_data[$line]['extension'], SRC_PATH . $post_id . '/' . $file_data[$line]['filename'] .
                 $file_data[$line]['extension']);
            nel_move_file(THUMB_PATH . $post_data['parent_thread'] . '/' . $file_data[$line]['preview_name'], THUMB_PATH .
                 $post_id . '/' . $file_data[$line]['preview_name']);

            ++ $line;
        }
    }
}

function nel_update_thread_data($thread_id)
{
    $dbh = nel_get_db_handle();
    $thread_data = nel_get_thread_data($thread_id);
    $last_post = nel_get_thread_last_post($thread_id);
    $second_last_post = nel_get_thread_second_last_post($thread_id);

    $thread_posts = nel_get_thread_all_posts($thread_id);
    $first_post = 0;
    $last_post = 0;
    $post_count = 0;
    $file_count = 0;
    $external_count = 0;
    $last_update = 0;
    $last_bump = 0; // TODO: Have this account for thread bump limit

    foreach ($thread_posts as $post)
    {
        if($first_post === 0)
        {
            $first_post = $post['post_number'];
        }

        $last_post = $post['post_number'];
        $post_count += 1;
        $file_count += $post['file_count'];
        $external_count += $post['external_count'];
        $last_update = $post['post_time'];

        if($post['sage'] === '0')
        {
            $last_bump = $post['post_time'];
        }
    }

    $prepared = $dbh->prepare('UPDATE "' . THREAD_TABLE . '" SET "first_post" = :first, "last_post" = :last, "post_count" = :pcount, "file_count" = :fcount, "external_count" = :ecount, "last_update" = :update, "last_bump_time" = :bump WHERE "post_number" = ' . $thread_id . '');
    $prepared->bindValue(':first', $first_post, PDO::PARAM_INT);
    $prepared->bindValue(':last', $last_post, PDO::PARAM_INT);
    $prepared->bindValue(':pcount', $post_count, PDO::PARAM_INT);
    $prepared->bindValue(':fcount', $file_count, PDO::PARAM_INT);
    $prepared->bindValue(':ecount', $external_count, PDO::PARAM_INT);
    $prepared->bindValue(':update', $last_update);
    $prepared->bindValue(':bump', $last_bump);
    $prepared->execute();
    unset($prepared);
}

function nel_delete_content($dataforce, $sub, $type)
{
    $dbh = nel_get_db_handle();
    $id = $sub[1];

    if (!is_numeric($id))
    {
        nel_derp(13, array('origin' => 'DELETE'));
    }

    $flag = FALSE;
    $result = $dbh->query('SELECT post_number,password,parent_thread,mod_post FROM ' . POST_TABLE .
         ' WHERE post_number=' . $id . '');
    $post_data = $result->fetch(PDO::FETCH_ASSOC);
    unset($result);

    if (!nel_session_ignored())
    {
        $temp = $_SESSION['ignore_login'];

        if ($_SESSION['perms']['perm_delete'])
        {
            if ($post_data['mod_post'] === '0')
            {
                $flag = TRUE;
            }
            else
            {
                $staff_type = $_SESSION['settings']['staff_type']; //TODO: Fix this mod type stuff too

                if ($post_data['mod_post'] === '3' && $staff_type === 'admin')
                {
                    $flag = TRUE;
                }
                else if ($post_data['mod_post'] === '2' && ($staff_type === 'admin' || $staff_type === 'moderator'))
                {
                    $flag = TRUE;
                }
                else if ($post_data['mod_post'] === '1' &&
                     ($staff_type === 'admin' || $staff_type === 'moderator' || $staff_type === 'janitor'))
                {
                    $flag = TRUE;
                }
            }
        }

        $_SESSION['ignore_login'] = $flag ? TRUE : $temp;
    }
    else
    {
        $flag = hash_equals($post_data['password'], $dataforce['pass']); // Must be fixt!!!
        $temp = TRUE;
    }

    if (!$flag)
    {
        nel_derp(20, array('origin' => 'DELETE'));
    }

    if ($type === 'THREAD')
    {
        $result = $dbh->query('SELECT post_number FROM ' . POST_TABLE . ' WHERE parent_thread=' . $id);
        $content_refs = $result->fetchALL(PDO::FETCH_COLUMN, 0);
        unset($result);

        foreach ($content_refs as $ref)
        {
            $dbh->query('DELETE FROM ' . FILE_TABLE . ' WHERE post_ref=' . $ref . '');
            $dbh->query('DELETE FROM ' . POST_TABLE . ' WHERE post_number=' . $ref . '');
            $dbh->query('DELETE FROM ' . THREAD_TABLE . ' WHERE thread_id=' . $ref . '');
            preg_replace('#p([0-9]+)t' . $ref . '#', '', $dataforce['post_links']);
        }

        nel_eraser_gun(PAGE_PATH . $id, NULL, TRUE);
        nel_eraser_gun(SRC_PATH . $id, NULL, TRUE);
        nel_eraser_gun(THUMB_PATH . $id, NULL, TRUE);

        nel_update_archive_status($dataforce);
    }
    else if ($type === 'POST')
    {
        $result = $dbh->query('SELECT filename,extension,preview_name FROM ' . FILE_TABLE . ' WHERE post_ref=' . $id .
             '');
        $file_data = $result->fetchAll(PDO::FETCH_ASSOC);
        unset($result);
        $dbh->query('DELETE FROM ' . FILE_TABLE . ' WHERE post_ref=' . $id . '');

        foreach ($file_data as $refs)
        {
            nel_eraser_gun(SRC_PATH . $post_data['parent_thread'], $refs['filename'] . $refs['extension'], FALSE);

            if ($refs['preview_name'])
            {
                nel_eraser_gun(THUMB_PATH . $post_data['parent_thread'], $refs['preview_name'], FALSE);
            }
        }

        if ($dataforce['only_delete_file'])
        {
            $dbh->query('UPDATE ' . POST_TABLE . ' SET has_file=0 WHERE post_number=' . $id . '');
        }
        else
        {
            $dbh->query('DELETE FROM ' . POST_TABLE . ' WHERE post_number=' . $id . '');
            $result = $dbh->query('SELECT post_count FROM ' . THREAD_TABLE . ' WHERE thread_id=' .
                 $post_data['parent_thread'] . '');
            $pcount = $result->fetch(PDO::FETCH_ASSOC);
            unset($result);
            $result = $dbh->query('SELECT post_number,post_time FROM ' . POST_TABLE . ' WHERE parent_thread=' .
                 $post_data['parent_thread'] . ' ORDER BY post_number desc');
            $ptimes = $result->fetchAll(PDO::FETCH_ASSOC);
            unset($result);
            $result = $dbh->query('SELECT post_number,post_time FROM ' . POST_TABLE . ' WHERE parent_thread=' .
                 $post_data['parent_thread'] . ' AND sage=0 ORDER BY post_number desc');
            $ptimes2 = $result->fetchAll(PDO::FETCH_ASSOC);
            unset($result);
            $dbh->query('UPDATE ' . THREAD_TABLE . ' SET post_count=' . ($pcount['post_count'] - 1) . ', last_update=' .
                 $ptimes[0]['post_time'] . ', last_bump_time=' . $ptimes2[0]['post_time'] . ' last_post=' .
                 $ptimes[0]['post_number'] . ' WHERE thread_id=' . $post_data['parent_thread'] . '');
            preg_replace('#p' . $id . 't([0-9]+)#', '', $dataforce['post_links']);
        }
    }
    else if ($type === 'FILE')
    {
        // add check for updating post as no files if they're all gone
        $fnum = $sub[2];
        $result = $dbh->query('SELECT filename,extension,preview_name FROM ' . FILE_TABLE . ' WHERE post_ref=' . $id .
             ' AND file_order=' . $fnum . '');
        $file_data = $result->fetch(PDO::FETCH_ASSOC);
        unset($result);

        if ($file_data !== FALSE)
        {
            $dbh->query('DELETE FROM ' . FILE_TABLE . ' WHERE post_ref=' . $id . ' AND file_order=' . $fnum . '');

            if ($post_data['response_to'] == 0)
            {
                nel_eraser_gun(SRC_PATH . $post_data['post_number'], $file_data['filename'] . $file_data['extension'], FALSE);

                if ($file_data['preview_name'])
                {
                    nel_eraser_gun(THUMB_PATH . $post_data['post_number'], $file_data['preview_name'], FALSE);
                }
            }
            else
            {
                nel_eraser_gun(SRC_PATH . $post_data['parent_thread'], $file_data['filename'] . $file_data['extension'], FALSE);

                if ($file_data['preview_name'])
                {
                    nel_eraser_gun(THUMB_PATH . $post_data['parent_thread'], $file_data['preview_name'], FALSE);
                }
            }
        }
    }

    if (!empty($_SESSION))
    {
        $_SESSION['ignore_login'] = $temp;
    }
}
