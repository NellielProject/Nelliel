<?php

function nel_db_insert_initial_post($time, $poster_info)
{
    $columns = array('poster_name', 'post_password', 'tripcode', 'secure_tripcode', 'email', 'subject', 'comment',
        'ip_address', 'post_time', 'op', 'sage', 'mod_post');
    $values = nel_pdo_create_parameter_ids($columns);
    $query = 'INSERT INTO ' . POST_TABLE . ' ' . nel_format_multiple_columns($columns) . ' VALUES ' .
         nel_format_multiple_values($values);
    $bind_values = array();
    $bind_values = nel_pdo_bind_set(':poster_name', $poster_info['name'], PDO::PARAM_STR, $bind_values);
    $bind_values = nel_pdo_bind_set(':post_password', $poster_info['password'], PDO::PARAM_STR, $bind_values);
    $bind_values = nel_pdo_bind_set(':tripcode', $poster_info['tripcode'] === '' ? null : $poster_info['tripcode'], null, $bind_values);
    $bind_values = nel_pdo_bind_set(':secure_tripcode', $poster_info['secure_tripcode'] === '' ? null : $poster_info['secure_tripcode'], null, $bind_values);
    $bind_values = nel_pdo_bind_set(':email', $poster_info['email'], PDO::PARAM_STR, $bind_values);
    $bind_values = nel_pdo_bind_set(':subject', $poster_info['subject'], PDO::PARAM_STR, $bind_values);
    $bind_values = nel_pdo_bind_set(':comment', $poster_info['comment'], PDO::PARAM_STR, $bind_values);
    $bind_values = nel_pdo_bind_set(':ip_address', $_SERVER["REMOTE_ADDR"], PDO::PARAM_STR, $bind_values);
    $bind_values = nel_pdo_bind_set(':post_time', $time, PDO::PARAM_INT, $bind_values);
    $bind_values = nel_pdo_bind_set(':op', $poster_info['op'], PDO::PARAM_INT, $bind_values);
    $bind_values = nel_pdo_bind_set(':sage', 0, PDO::PARAM_INT, $bind_values);
    $bind_values = nel_pdo_bind_set(':mod_post', $poster_info['modpost'], PDO::PARAM_STR, $bind_values);
    $results = nel_pdo_prepared_query($query, $bind_values);
}

function nel_db_insert_new_thread($thread_info, $files_count) // TODO: Update for externals and other new data
{
    $columns = array('thread_id', 'first_post', 'last_post', 'last_bump_time', 'total_files', 'last_update',
        'post_count');
    $values = nel_pdo_create_parameter_ids($columns);
    $query = 'INSERT INTO ' . THREAD_TABLE . ' ' . nel_format_multiple_columns($columns) . ' VALUES ' .
         nel_format_multiple_values($values);
    $bind_values = array();
    $bind_values = nel_pdo_bind_set(':thread_id', $thread_info['id'], PDO::PARAM_INT, $bind_values);
    $bind_values = nel_pdo_bind_set(':first_post', $thread_info['id'], PDO::PARAM_INT, $bind_values);
    $bind_values = nel_pdo_bind_set(':last_post', $thread_info['id'], PDO::PARAM_INT, $bind_values);
    $bind_values = nel_pdo_bind_set(':last_bump_time', $thread_info['last_bump_time'], PDO::PARAM_INT, $bind_values);
    $bind_values = nel_pdo_bind_set(':total_files', $files_count, PDO::PARAM_INT, $bind_values);
    $bind_values = nel_pdo_bind_set(':last_update', $thread_info['last_update'], PDO::PARAM_INT, $bind_values);
    $bind_values = nel_pdo_bind_set(':post_count', 1, PDO::PARAM_INT, $bind_values);
    $results = nel_pdo_prepared_query($query, $bind_values);
}

function nel_db_update_thread($new_post_info, $thread_info)
{
    $query = 'UPDATE ' . THREAD_TABLE .
         ' SET last_post=?, last_bump_time=?, last_update=?, post_count=? WHERE thread_id=?';
    $bind_values = array();
    $bind_values = nel_pdo_bind_set(1, $new_post_info['post_number'], PDO::PARAM_INT, $bind_values);
    $bind_values = nel_pdo_bind_set(2, $thread_info['last_bump_time'], PDO::PARAM_INT, $bind_values);
    $bind_values = nel_pdo_bind_set(3, $thread_info['last_update'], PDO::PARAM_INT, $bind_values);
    $bind_values = nel_pdo_bind_set(4, $thread_info['post_count'], PDO::PARAM_INT, $bind_values);
    $bind_values = nel_pdo_bind_set(5, $thread_info['id'], PDO::PARAM_INT, $bind_values);
    $results = nel_pdo_prepared_query($query, $bind_values);
}

function nel_db_insert_new_files($parent_id, $new_post_info, $files)
{
    $query = 'UPDATE ' . POST_TABLE . ' SET has_file=1 WHERE post_number=?';
    $bind_values = array();
    $bind_values = nel_pdo_bind_set(1, $new_post_info['post_number'], PDO::PARAM_INT, $bind_values);
    $results = nel_pdo_prepared_query($query, $bind_values);

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
        $bind_values = nel_pdo_bind_set(':parent_thread', $parent_id, PDO::PARAM_INT, $bind_values);
        $bind_values = nel_pdo_bind_set(':post_ref', $new_post_info['post_number'], PDO::PARAM_INT, $bind_values);
        $bind_values = nel_pdo_bind_set(':file_order', $i, PDO::PARAM_INT, $bind_values);
        $bind_values = nel_pdo_bind_set(':supertype', $file['supertype'], PDO::PARAM_STR, $bind_values);
        $bind_values = nel_pdo_bind_set(':subtype', $file['subtype'], PDO::PARAM_STR, $bind_values);
        $bind_values = nel_pdo_bind_set(':mime', $file['mime'], PDO::PARAM_STR, $bind_values);
        $bind_values = nel_pdo_bind_set(':filename', $file['filename'], PDO::PARAM_STR, $bind_values);
        $bind_values = nel_pdo_bind_set(':extension', $file['ext'], PDO::PARAM_STR, $bind_values);
        $bind_values = nel_pdo_bind_set(':image_width', $file['im_x'], PDO::PARAM_INT, $bind_values);
        $bind_values = nel_pdo_bind_set(':image_height', $file['im_y'], PDO::PARAM_INT, $bind_values);
        $bind_values = nel_pdo_bind_set(':preview_name', $file['thumbfile'], PDO::PARAM_STR, $bind_values);
        $bind_values = nel_pdo_bind_set(':preview_width', $file['pre_x'], PDO::PARAM_INT, $bind_values);
        $bind_values = nel_pdo_bind_set(':preview_height', $file['pre_y'], PDO::PARAM_INT, $bind_values);
        $bind_values = nel_pdo_bind_set(':filesize', $file['filesize'], PDO::PARAM_INT, $bind_values);
        $bind_values = nel_pdo_bind_set(':md5', $file['md5'], PDO::PARAM_STR, $bind_values);
        $bind_values = nel_pdo_bind_set(':sha1', $file['sha1'], PDO::PARAM_STR, $bind_values);
        $bind_values = nel_pdo_bind_set(':source', $file['source'], PDO::PARAM_STR, $bind_values);
        $bind_values = nel_pdo_bind_set(':license', $file['license'], PDO::PARAM_STR, $bind_values);
        $results = nel_pdo_prepared_query($query, $bind_values);
        ++ $i;
    }
}