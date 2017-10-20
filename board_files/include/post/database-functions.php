<?php

function nel_db_insert_initial_post($time, $post_data)
{
    $columns = array('poster_name', 'post_password', 'tripcode', 'secure_tripcode', 'email', 'subject', 'comment',
        'ip_address', 'has_file', 'file_count', 'post_time', 'op', 'sage', 'mod_post');
    $values = nel_pdo_create_parameter_ids($columns);
    $query = 'INSERT INTO "' . POST_TABLE . '" ' . nel_format_multiple_columns($columns) . ' VALUES ' .
         nel_format_multiple_values($values);
    $bind_values = array();
    nel_pdo_bind_set($bind_values, ':poster_name', $post_data['name'], PDO::PARAM_STR);
    nel_pdo_bind_set($bind_values, ':post_password', $post_data['password'], PDO::PARAM_STR);
    nel_pdo_bind_set($bind_values, ':tripcode', $post_data['tripcode'] === '' ? null : $post_data['tripcode']);
    nel_pdo_bind_set($bind_values, ':secure_tripcode', $post_data['secure_tripcode'] === '' ? null : $post_data['secure_tripcode']);
    nel_pdo_bind_set($bind_values, ':email', $post_data['email'], PDO::PARAM_STR);
    nel_pdo_bind_set($bind_values, ':subject', $post_data['subject'], PDO::PARAM_STR);
    nel_pdo_bind_set($bind_values, ':comment', $post_data['comment'], PDO::PARAM_STR);
    nel_pdo_bind_set($bind_values, ':ip_address', $_SERVER["REMOTE_ADDR"], PDO::PARAM_STR);
    nel_pdo_bind_set($bind_values, ':has_file', $post_data['has_file'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':file_count', $post_data['file_count'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':post_time', $time, PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':op', $post_data['op'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':sage', 0, PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':mod_post', $post_data['modpost'], PDO::PARAM_STR);
    nel_pdo_prepared_query($query, $bind_values, true);
}

function nel_db_insert_new_thread($thread_info, $files_count) // TODO: Update for externals and other new data
{
    $columns = array('thread_id', 'first_post', 'last_post', 'last_bump_time', 'total_files', 'last_update',
        'post_count');
    $values = nel_pdo_create_parameter_ids($columns);
    $query = 'INSERT INTO "' . THREAD_TABLE . '" ' . nel_format_multiple_columns($columns) . ' VALUES ' .
         nel_format_multiple_values($values);
    $bind_values = array();
    nel_pdo_bind_set($bind_values, ':thread_id', $thread_info['id'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':first_post', $thread_info['id'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':last_post', $thread_info['id'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':last_bump_time', $thread_info['last_bump_time'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':total_files', $thread_info['total_files'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':last_update', $thread_info['last_update'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':post_count', 1, PDO::PARAM_INT);
    nel_pdo_prepared_query($query, $bind_values, true);
}

function nel_db_update_thread($new_post_info, $thread_info)
{
    $query = 'UPDATE "' . THREAD_TABLE .
         '" SET "last_post" = ?, "last_bump_time" = ?, "last_update" = ?, "post_count" = ?, "total_files" = ? WHERE "thread_id" = ?';
    $bind_values = array();
    nel_pdo_bind_set($bind_values, 1, $new_post_info['post_number'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, 2, $thread_info['last_bump_time'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, 3, $thread_info['last_update'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, 4, $thread_info['post_count'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, 5, $thread_info['total_files'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, 6, $thread_info['id'], PDO::PARAM_INT);
    nel_pdo_prepared_query($query, $bind_values, true);
}

function nel_db_insert_new_files($parent_id, $new_post_info, $files)
{
    $i = 1;

    foreach ($files as $file)
    {
        $columns = array('parent_thread', 'post_ref', 'file_order', 'supertype', 'subtype', 'mime', 'filename',
        'extension', 'image_width', 'image_height', 'preview_name', 'preview_width', 'preview_height', 'filesize',
        'md5', 'sha1', 'source', 'license');
        $values = nel_pdo_create_parameter_ids($columns);
        $query = 'INSERT INTO ' . FILE_TABLE . ' ' . nel_format_multiple_columns($columns) . ' VALUES ' .
        nel_format_multiple_values($values);
        $bind_values = array();
        nel_pdo_bind_set($bind_values, ':parent_thread', $parent_id, PDO::PARAM_INT);
        nel_pdo_bind_set($bind_values, ':post_ref', $new_post_info['post_number'], PDO::PARAM_INT);
        nel_pdo_bind_set($bind_values, ':file_order', $i, PDO::PARAM_INT);
        nel_pdo_bind_set($bind_values, ':supertype', $file['supertype'], PDO::PARAM_STR);
        nel_pdo_bind_set($bind_values, ':subtype', $file['subtype'], PDO::PARAM_STR);
        nel_pdo_bind_set($bind_values, ':mime', $file['mime'], PDO::PARAM_STR);
        nel_pdo_bind_set($bind_values, ':filename', $file['filename'], PDO::PARAM_STR);
        nel_pdo_bind_set($bind_values, ':extension', $file['ext'], PDO::PARAM_STR);
        nel_pdo_bind_set($bind_values, ':image_width', $file['im_x'], PDO::PARAM_INT);
        nel_pdo_bind_set($bind_values, ':image_height', $file['im_y'], PDO::PARAM_INT);
        nel_pdo_bind_set($bind_values, ':preview_name', $file['thumbfile'], PDO::PARAM_STR);
        nel_pdo_bind_set($bind_values, ':preview_width', $file['pre_x'], PDO::PARAM_INT);
        nel_pdo_bind_set($bind_values, ':preview_height', $file['pre_y'], PDO::PARAM_INT);
        nel_pdo_bind_set($bind_values, ':filesize', $file['filesize'], PDO::PARAM_INT);
        nel_pdo_bind_set($bind_values, ':md5', $file['md5'], PDO::PARAM_STR);
        nel_pdo_bind_set($bind_values, ':sha1', $file['sha1'], PDO::PARAM_STR);
        nel_pdo_bind_set($bind_values, ':source', $file['source'], PDO::PARAM_STR);
        nel_pdo_bind_set($bind_values, ':license', $file['license'], PDO::PARAM_STR);
        nel_pdo_prepared_query($query, $bind_values, true);
        ++ $i;
    }
}