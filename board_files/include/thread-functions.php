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
        $sub = explode('_', $input, 4);

        switch ($sub[0])
        {
            case 'deletefile':
                nel_verify_delete_perms($sub);
                nel_delete_file($dataforce, $sub);
                break;

            case 'deletethread':
                $id = $sub[1];
                nel_verify_delete_perms($sub);
                nel_remove_thread_from_database($id);
                nel_delete_thread_directories($id);
                nel_update_archive_status($dataforce);
                break;

            case 'deletepost':
                nel_verify_delete_perms($sub);
                nel_delete_post($dataforce, $sub);
                break;

            case 'threadsticky':
                nel_sticky_thread($dataforce, $sub);
                break;

            case 'threadunsticky':
                nel_unsticky_thread($dataforce, $sub);
                break;
        }

        if (!in_array($sub[1], $returned_list))
        {
            array_push($returned_list, $sub[1]);
        }
    }

    return $returned_list;
}

function nel_sticky_thread($dataforce, $sub)
{
    $dbh = nel_get_db_handle();
    $id = $sub[2];
    $query = 'SELECT "parent_thread" FROM "' . POST_TABLE . '" WHERE "post_number" = ?';
    $prepared = nel_pdo_one_parameter_query($query, $id, PDO::PARAM_INT);
    $post_data = nel_pdo_do_fetch($prepared, PDO::FETCH_ASSOC, true);

    // If this is not already a thread, make the post into one
    if ($post_data['parent_thread'] != $id)
    {
        nel_make_post_thread($dataforce, $id);
    }

    $query = 'UPDATE "' . THREAD_TABLE . '" SET "sticky" = 1 WHERE "thread_id" = ?';
    nel_pdo_one_parameter_query($query, $id, PDO::PARAM_INT, true);
    nel_update_archive_status($dataforce);
    nel_regen_threads($dataforce, true, array($id));
    nel_regen_index($dataforce);
    return;
}

function nel_unsticky_thread($dataforce, $sub)
{
    $id = $sub[1];
    $query = 'UPDATE "' . THREAD_TABLE . '" SET "sticky" = 0 WHERE "thread_id" = ?';
    nel_pdo_one_parameter_query($query, $id, PDO::PARAM_INT, true);
    nel_update_archive_status($dataforce, $dbh);
    nel_toggle_session();
    $dataforce['response_id'] = $id;
    nel_regen_threads($dataforce, true, array($dataforce['response_id']));
    $dataforce['archive_update'] = TRUE;
    nel_regen_index($dataforce);
    nel_toggle_session();
}

function nel_get_post_thread_id($post_number)
{
    $query = 'SELECT "parent_thread" FROM "' . POST_TABLE . '" WHERE "post_number" = ?';
    $prepared = nel_pdo_one_parameter_query($query, $post_number, PDO::PARAM_INT);
    return nel_pdo_do_fetch($prepared, PDO::FETCH_COLUMN, true);
}

function nel_get_thread_data($thread_id)
{
    $query = 'SELECT * FROM "' . THREAD_TABLE . '" WHERE "thread_id" = ?';
    $prepared = nel_pdo_one_parameter_query($query, $thread_id, PDO::PARAM_INT);
    return nel_pdo_do_fetch($prepared, PDO::FETCH_ASSOC, true);
}

function nel_get_thread_all_posts($thread_id)
{
    $query = 'SELECT * FROM "' . POST_TABLE . '" WHERE "parent_thread" = ?';
    $prepared = nel_pdo_one_parameter_query($query, $thread_id, PDO::PARAM_INT);
    return nel_pdo_do_fetchall($prepared, PDO::FETCH_ASSOC, true);
}

function nel_get_post_data($post_id)
{
    $query = 'SELECT * FROM "' . POST_TABLE . '" WHERE "post_number" = ?';
    $prepared = nel_pdo_one_parameter_query($query, $post_id, PDO::PARAM_INT);
    return nel_pdo_do_fetch($prepared, PDO::FETCH_ASSOC, true);
}

function nel_get_post_files($post_id)
{
    $query = 'SELECT * FROM "' . FILE_TABLE . '" WHERE "post_ref" = ?';
    $prepared = nel_pdo_one_parameter_query($query, $post_id, PDO::PARAM_INT);
    return nel_pdo_do_fetch($prepared, PDO::FETCH_ASSOC, true);
}

function nel_get_thread_last_post($thread_id)
{
    $query = 'SELECT *  FROM "' . POST_TABLE . '" WHERE "parent_thread" = ? ORDER BY "post_number" DESC LIMIT 1';
    $prepared = nel_pdo_one_parameter_query($query, $thread_id, PDO::PARAM_INT);
    return nel_pdo_do_fetch($prepared, PDO::FETCH_ASSOC, true);
}

function nel_get_thread_last_nosage_post($thread_id)
{
    $query = 'SELECT *  FROM "' . POST_TABLE .
         '" WHERE "parent_thread" = ? AND "sage" = 0 ORDER BY "post_number" DESC LIMIT 1';
    $prepared = nel_pdo_one_parameter_query($query, $thread_id, PDO::PARAM_INT);
    return nel_pdo_do_fetch($prepared, PDO::FETCH_ASSOC, true);
}

function nel_get_thread_second_last_post($thread_id)
{
    $query = 'SELECT *  FROM "' . POST_TABLE . '" WHERE "parent_thread" = ? ORDER BY "post_number" DESC LIMIT 2';
    $prepared = nel_pdo_one_parameter_query($query, $thread_id, PDO::PARAM_INT);
    $post_data = nel_pdo_do_fetchall($prepared, PDO::FETCH_ASSOC);

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

    $columns = array('thread_id', 'first_post', 'last_post', 'last_bump_time', 'total_files', 'total_externals',
        'last_update', 'post_count', 'sticky');
    $values = nel_pdo_create_parameter_ids($columns);
    $query = 'INSERT INTO ' . THREAD_TABLE . ' ' . nel_format_multiple_columns($columns) . ' VALUES ' .
         nel_format_multiple_values($values);
    $bind_values = array();
    nel_pdo_bind_set($bind_values, ':thread_id', $post_id, PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':first_post', $post_id, PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':last_post', $post_id, PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':last_bump_time', $post_data['post_time'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':total_files', $post_data['file_count'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':total_externals', $post_data['external_count'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':last_update', $thread_info['last_update'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':post_count', 1, PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':sticky', 1, PDO::PARAM_INT);
    nel_pdo_prepared_query($query, $bind_values, true);

    $query = 'UPDATE "' . POST_TABLE . '" SET "parent_thread" = ?, "op" = 1 WHERE "post_number" = ?';
    $bind_values = array();
    nel_pdo_bind_set($bind_values, '1', $post_id, PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, '2', $post_id, PDO::PARAM_INT);
    nel_pdo_prepared_query($query, $bind_values, true);

    if ($post_data['has_file'])
    {
        $src_path = nel_path_file_join(SRC_PATH, $post_data['parent_thread']);
        $thumb_path = nel_path_file_join(THUMB_PATH, $post_data['parent_thread']);
        $src_dest = nel_path_file_join(SRC_PATH, $post_id);
        $thumb_dest = nel_path_file_join(THUMB_PATH, $post_id);

        $query = 'UPDATE "' . FILE_TABLE . '" SET "parent_thread" = ? WHERE "post_ref" = ?';
        $bind_values = array();
        nel_pdo_bind_set($bind_values, '1', $post_id, PDO::PARAM_INT);
        nel_pdo_bind_set($bind_values, '2', $post_id, PDO::PARAM_INT);
        nel_pdo_prepared_query($query, $bind_values, true);

        $query = 'SELECT "filename", "extension", "preview_name" FROM "' . FILE_TABLE . '" WHERE "post_ref" = ?';
        $prepared = nel_pdo_one_parameter_query($query, $thread_id, PDO::PARAM_INT);
        $file_data = nel_pdo_do_fetchall($prepared, PDO::FETCH_ASSOC);
        $file_count = count($file_data);
        $line = 0;

        while ($line < $file_count)
        {
            $filename = $file_data[$line]['filename'] . $file_data[$line]['extension'];
            $preview = $file_data[$line]['preview_name'];
            nel_move_file(nel_path_file_join($src_path, $filename), nel_path_file_join($src_dest, $filename));
            nel_move_file(nel_path_file_join($thumb_path, $preview), nel_path_file_join($thumb_dest, $preview));
            ++ $line;
        }
    }
}

function nel_update_thread_data($thread_id)
{
    $dbh = nel_get_db_handle();
    $thread_data = nel_get_thread_data($thread_id);
    $last_post = nel_get_thread_last_post($thread_id);

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
        if ($first_post === 0)
        {
            $first_post = $post['post_number'];
        }

        $last_post = $post['post_number'];
        $post_count += 1;
        $file_count += $post['file_count'];
        $external_count += $post['external_count'];
        $last_update = $post['post_time'];

        if ($post['sage'] === '0')
        {
            $last_bump = $post['post_time'];
        }
    }

    $prepared = $dbh->prepare('UPDATE "' . THREAD_TABLE . '" SET "first_post" = :first_post, "last_post" = :last_post, "post_count" = :post_count,
        "total_files" = :total_files, "total_external" = :total_external, "last_update" = :last_update,
        "last_bump_time" = :last_bump_time WHERE "thread_id" = :thread_id');
    $prepared->bindValue(':first_post', $first_post, PDO::PARAM_INT);
    $prepared->bindValue(':last_post', $last_post, PDO::PARAM_INT);
    $prepared->bindValue(':post_count', $post_count, PDO::PARAM_INT);
    $prepared->bindValue(':total_files', $file_count, PDO::PARAM_INT);
    $prepared->bindValue(':total_external', $external_count, PDO::PARAM_INT);
    $prepared->bindValue(':last_update', $last_update, PDO::PARAM_INT);
    $prepared->bindValue(':last_bump_time', $last_bump, PDO::PARAM_INT);
    $prepared->bindValue(':thread_id', $thread_id, PDO::PARAM_INT);
    $prepared->execute();
    unset($prepared);
}

function nel_remove_post_from_database($sub, $id, $post_data)
{
    $query = 'DELETE FROM "' . POST_TABLE . '" WHERE "post_number" = ?';
    nel_pdo_one_parameter_query($query, $id, PDO::PARAM_INT, true);

    $thread_data = nel_get_thread_data($post_data['parent_thread']);
    $new_count = $thread_data['post_count'] - 1;
    $new_last = nel_get_thread_last_post($sub[1]);
    $last_bump = $new_last['post_time'];
    $total_files = $thread_data['total_files'] - $post_data['file_count'];

    if ($new_last['sage'] !== '0')
    {
        $last_nosage = nel_get_thread_last_nosage_post($id);
        $last_bump = $last_nosage['post_time'];
    }

    $query = 'UPDATE "' . THREAD_TABLE .
         '" SET "post_count" = ?, "last_post" = ?, "last_update" = ?, "last_bump_time" = ?, "total_files" = ? WHERE "thread_id" = ?';
    $bind_values = array();
    nel_pdo_bind_set($bind_values, 1, $new_count, PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, 2, $new_last['post_number'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, 3, $new_last['post_time'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, 4, $last_bump, PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, 5, $total_files, PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, 6, $post_data['parent_thread'], PDO::PARAM_INT);
    nel_pdo_prepared_query($query, $bind_values, true);
}

function nel_remove_thread_from_database($thread_id)
{
    $query = 'SELECT "post_number" FROM "' . POST_TABLE . '" WHERE "parent_thread" = ?';
    $prepared = nel_pdo_one_parameter_query($query, $thread_id, PDO::PARAM_INT);
    $thread_posts = nel_pdo_do_fetchall($prepared, PDO::FETCH_COLUMN, true);

    foreach ($thread_posts as $ref)
    {
        nel_remove_files_from_database($ref);
        $query = 'DELETE FROM "' . POST_TABLE . '" WHERE "post_number" = ?';
        nel_pdo_one_parameter_query($query, $ref, PDO::PARAM_INT, true);
    }

    $query = 'DELETE FROM "' . THREAD_TABLE . '" WHERE "thread_id" = ?';
    nel_pdo_one_parameter_query($query, $thread_id, PDO::PARAM_INT, true);
}

function nel_remove_files_from_database($post_ref, $order = null)
{
    if (is_null($order))
    {
        $query = 'DELETE FROM "' . FILE_TABLE . '" WHERE "post_ref" = ?';
        nel_pdo_one_parameter_query($query, $post_ref, PDO::PARAM_INT, true);
    }
    else
    {
        $query = 'DELETE FROM "' . FILE_TABLE . '" WHERE "post_ref" = ? AND "file_order" = ?';
        $bind_values = array();
        nel_pdo_bind_set($bind_values, 1, $post_ref, PDO::PARAM_INT);
        nel_pdo_bind_set($bind_values, 2, $order, PDO::PARAM_INT);
        nel_pdo_prepared_query($query, $bind_values, true);
    }

    $thread_id = nel_get_post_thread_id($post_ref);
    subtract_from_file_count($post_ref, $thread_id, 1);
}

function subtract_from_file_count($post_number, $thread_id, $quantity)
{
    $query = 'SELECT "file_count", "has_file" FROM "' . POST_TABLE . '" WHERE "post_number" = ?';
    $prepared = nel_pdo_one_parameter_query($query, $post_number, PDO::PARAM_INT);
    $post_files = nel_pdo_do_fetch($prepared, PDO::FETCH_ASSOC, true);
    $post_files['file_count'] -= $quantity;

    if ($post_files['file_count'] <= 0)
    {
        $post_files['file_count'] = 0;
        $post_files['has_file'] = 0;
    }

    $query = 'SELECT "total_files" FROM "' . THREAD_TABLE . '" WHERE "thread_id" = ?';
    $prepared = nel_pdo_one_parameter_query($query, $thread_id, PDO::PARAM_INT);
    $total_files = nel_pdo_do_fetch($prepared, PDO::FETCH_COLUMN, true);
    $total_files -= $quantity;

    if ($total_files <= 0)
    {
        $total_files = 0;
    }

    $query = 'UPDATE "' . POST_TABLE . '" SET "has_file" = ?, "file_count" = ? WHERE "post_number" = ?';
    $bind_values = array();
    nel_pdo_bind_set($bind_values, 1, $post_files['has_file'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, 2, $post_files['file_count'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, 3, $post_number, PDO::PARAM_INT);
    nel_pdo_prepared_query($query, $bind_values, true);

    $query = 'UPDATE "' . THREAD_TABLE . '" SET "total_files" = ? WHERE "thread_id" = ?';
    $bind_values = array();
    nel_pdo_bind_set($bind_values, 1, $total_files, PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, 2, $thread_id, PDO::PARAM_INT);
    nel_pdo_prepared_query($query, $bind_values, true);
}

function nel_delete_file($dataforce, $sub)
{
    $dbh = nel_get_db_handle();
    $id = $sub[2];
    $post_data = nel_get_post_data($id);
    $fnum = $sub[3];

    $query = 'SELECT "filename", "extension", "preview_name" FROM "' . FILE_TABLE .
         '" WHERE "post_ref" = ? AND "file_order" = ?';
    $bind_values = array();
    nel_pdo_bind_set($bind_values, 1, $id, PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, 2, $fnum, PDO::PARAM_INT);
    $result = nel_pdo_prepared_query($query, $bind_values);
    $file_data = nel_pdo_do_fetch($result, PDO::FETCH_ASSOC, true);

    if ($file_data !== false)
    {
        nel_remove_files_from_database($id, $fnum);
        $filename = $file_data['filename'] . '.' . $file_data['extension'];

        if ($post_data['parent_thread'] == 0)
        {
            nel_eraser_gun(nel_path_join(SRC_PATH, $post_data['post_number']), $filename);
            nel_eraser_gun(nel_path_join(THUMB_PATH, $post_data['post_number']), $file_data['preview_name']);
        }
        else
        {
            nel_eraser_gun(nel_path_join(SRC_PATH, $post_data['parent_thread']), $filename);
            nel_eraser_gun(nel_path_join(THUMB_PATH, $post_data['parent_thread']), $file_data['preview_name']);
        }
    }
}

function nel_delete_post($dataforce, $sub)
{
    $dbh = nel_get_db_handle();
    $id = $sub[2];
    $post_data = nel_get_post_data($id);
    $post_files = nel_get_post_files($id);
    nel_remove_files_from_database($id);

    foreach ($post_files as $refs)
    {
        $filename = $refs['filename'] . '.' . $refs['extension'];
        nel_eraser_gun(nel_path_join(SRC_PATH, $post_data['parent_thread']), $refs['filename'] . $refs['extension']);
        nel_eraser_gun(nel_path_join(THUMB_PATH, $post_data['parent_thread']), $refs['preview_name']);
    }

    if ($dataforce['only_delete_file'])
    {
        $dbh->query('UPDATE ' . POST_TABLE . ' SET has_file = 0 WHERE post_number=' . $id . '');
    }
    else
    {
        nel_remove_post_from_database($sub, $id, $post_data);
    }
}

function nel_verify_delete_perms($sub)
{
    $authorize = nel_get_authorization();
    $id = $sub[2];

    if (!is_numeric($id))
    {
        nel_derp(13, array('origin' => 'DELETE'));
    }

    $query = 'SELECT * FROM "' . POST_TABLE . '" WHERE "post_number" = ?';
    $prepared = nel_pdo_one_parameter_query($query, $id, PDO::PARAM_INT);
    $post_data = nel_pdo_do_fetch($prepared, PDO::FETCH_ASSOC, true);
    $flag = false;

    if (nel_session_active())
    {
        $flag = $authorize->role_level_check($_SESSION['role_id'], $authorize->get_user_role($post_data['mod_post']));
    }
    else
    {
        $flag = nel_password_verify($post_data['post_password'], $_POST['sekrit']);
        var_dump($flag);
        var_dump($post_data['post_password']);
        var_dump($_POST['sekrit']);
        die();
    }

    if (!$flag)
    {
        nel_derp(20, array('origin' => 'DELETE'));
    }
}
