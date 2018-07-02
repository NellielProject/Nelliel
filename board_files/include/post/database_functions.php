<?php

function nel_db_insert_initial_post($board_id, $time, $post_data)
{
    $dbh = nel_database();
    $references = nel_parameters_and_data()->boardReferences($board_id);
    $columns = array('poster_name', 'post_password', 'tripcode', 'secure_tripcode', 'email', 'subject', 'comment',
        'ip_address', 'has_file', 'file_count', 'post_time', 'op', 'sage', 'mod_post');
    $values = $dbh->generateParameterIds($columns);
    $query = $dbh->buildBasicInsertQuery($references['post_table'], $columns, $values);
    $prepared = $dbh->prepare($query);
    $prepared->bindValue(':poster_name', $post_data['name'], PDO::PARAM_STR);
    $prepared->bindValue(':post_password', $post_data['password'], PDO::PARAM_STR);
    $prepared->bindValue(':tripcode', $post_data['tripcode'] === '' ? null : $post_data['tripcode']);
    $prepared->bindValue(':secure_tripcode', $post_data['secure_tripcode'] === '' ? null : $post_data['secure_tripcode']);
    $prepared->bindValue(':email', $post_data['email'], PDO::PARAM_STR);
    $prepared->bindValue(':subject', $post_data['subject'], PDO::PARAM_STR);
    $prepared->bindValue(':comment', $post_data['comment'], PDO::PARAM_STR);
    $prepared->bindValue(':ip_address', @inet_pton($_SERVER["REMOTE_ADDR"]), PDO::PARAM_LOB);
    $prepared->bindValue(':has_file', $post_data['has_file'], PDO::PARAM_INT);
    $prepared->bindValue(':file_count', $post_data['file_count'], PDO::PARAM_INT);
    $prepared->bindValue(':post_time', $time, PDO::PARAM_INT);
    $prepared->bindValue(':op', $post_data['op'], PDO::PARAM_INT);
    $prepared->bindValue(':sage', $post_data['sage'], PDO::PARAM_INT);
    $prepared->bindValue(':mod_post', $post_data['modpost'], PDO::PARAM_STR);
    $dbh->executePrepared($prepared);
}

function nel_db_insert_new_thread($board_id, $thread_info)
{
    $dbh = nel_database();
    $references = nel_parameters_and_data()->boardReferences($board_id);
    $columns = array('thread_id', 'first_post', 'last_post', 'last_bump_time', 'total_files', 'last_update',
        'post_count');
    $values = $dbh->generateParameterIds($columns);
    $query = $dbh->buildBasicInsertQuery($references['thread_table'], $columns, $values);
    $prepared = $dbh->prepare($query);
    $prepared->bindValue(':thread_id', $thread_info['id'], PDO::PARAM_INT);
    $prepared->bindValue(':first_post', $thread_info['id'], PDO::PARAM_INT);
    $prepared->bindValue(':last_post', $thread_info['id'], PDO::PARAM_INT);
    $prepared->bindValue(':last_bump_time', $thread_info['last_bump_time'], PDO::PARAM_INT);
    $prepared->bindValue(':total_files', $thread_info['total_files'], PDO::PARAM_INT);
    $prepared->bindValue(':last_update', $thread_info['last_update'], PDO::PARAM_INT);
    $prepared->bindValue(':post_count', 1, PDO::PARAM_INT);
    $dbh->executePrepared($prepared);
}

function nel_db_update_thread($board_id, $new_post_info, $thread_info)
{
    $dbh = nel_database();
    $references = nel_parameters_and_data()->boardReferences($board_id);
    $query = 'UPDATE "' . $references['thread_table'] .
         '" SET "last_post" = ?, "last_bump_time" = ?, "last_update" = ?, "post_count" = ?, "total_files" = ? WHERE "thread_id" = ?';
    $prepared = $dbh->prepare($query);
    $prepared->bindValue(1, $new_post_info['post_number'], PDO::PARAM_INT);
    $prepared->bindValue(2, $thread_info['last_bump_time'], PDO::PARAM_INT);
    $prepared->bindValue(3, $thread_info['last_update'], PDO::PARAM_INT);
    $prepared->bindValue(4, $thread_info['post_count'], PDO::PARAM_INT);
    $prepared->bindValue(5, $thread_info['total_files'], PDO::PARAM_INT);
    $prepared->bindValue(6, $thread_info['id'], PDO::PARAM_INT);
    $dbh->executePrepared($prepared);
}

function nel_db_insert_new_files($board_id, $parent_id, $new_post_info, $files)
{
    $dbh = nel_database();
    $references = nel_parameters_and_data()->boardReferences($board_id);
    $i = 1;

    foreach ($files as $file)
    {
        $columns = array('parent_thread', 'post_ref', 'file_order', 'type', 'format', 'mime', 'filename', 'extension',
            'image_width', 'image_height', 'preview_name', 'preview_extension', 'preview_width', 'preview_height',
            'filesize', 'md5', 'sha1', 'sha256', 'sha512', 'source', 'license', 'alt_text');
        $values = $dbh->generateParameterIds($columns);
        $query = $dbh->buildBasicInsertQuery($references['file_table'], $columns, $values);
        $prepared = $dbh->prepare($query);
        $prepared->bindValue(':parent_thread', $parent_id, PDO::PARAM_INT);
        $prepared->bindValue(':post_ref', $new_post_info['post_number'], PDO::PARAM_INT);
        $prepared->bindValue(':file_order', $i, PDO::PARAM_INT);
        $prepared->bindValue(':type', $file['type'], PDO::PARAM_STR);
        $prepared->bindValue(':format', $file['format'], PDO::PARAM_STR);
        $prepared->bindValue(':mime', $file['mime'], PDO::PARAM_STR);
        $prepared->bindValue(':filename', $file['filename'], PDO::PARAM_STR);
        $prepared->bindValue(':extension', $file['extension'], PDO::PARAM_STR);
        $prepared->bindValue(':image_width', $file['im_x'], PDO::PARAM_INT);
        $prepared->bindValue(':image_height', $file['im_y'], PDO::PARAM_INT);
        $prepared->bindValue(':preview_name', $file['preview_name'], PDO::PARAM_STR);
        $prepared->bindValue(':preview_extension', $file['preview_extension'], PDO::PARAM_STR);
        $prepared->bindValue(':preview_width', $file['pre_x'], PDO::PARAM_INT);
        $prepared->bindValue(':preview_height', $file['pre_y'], PDO::PARAM_INT);
        $prepared->bindValue(':filesize', $file['filesize'], PDO::PARAM_INT);
        $prepared->bindValue(':md5', $file['md5'], PDO::PARAM_LOB);
        $prepared->bindValue(':sha1', $file['sha1'], PDO::PARAM_LOB);
        $prepared->bindValue(':sha256', $file['sha256'], PDO::PARAM_LOB);
        $prepared->bindValue(':sha512', $file['sha512'], PDO::PARAM_LOB);
        $prepared->bindValue(':source', $file['source'], PDO::PARAM_STR);
        $prepared->bindValue(':license', $file['license'], PDO::PARAM_STR);
        $prepared->bindValue(':alt_text', $file['alt_text'], PDO::PARAM_STR);
        $dbh->executePrepared($prepared);
        ++ $i;
    }
}