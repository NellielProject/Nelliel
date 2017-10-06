<?php

function nel_db_insert_initial_post($time, $poster_info)
{
    global $fgsfds;
    $dbh = nel_get_db_handle();
    $columns = array('poster_name', 'post_password', 'tripcode', 'secure_tripcode', 'email', 'subject', 'comment',
        'ip_address', 'post_time', 'op', 'sage', 'mod_post');
    $values = array(':name', ':password', ':tripcode', ':secure_tripcode', ':email', ':subject', ':comment',
        ':ip_address', ':time', ':op', ':sage', ':modpost');
    $query = 'INSERT INTO ' . POST_TABLE . ' ' . nel_format_multiple_columns($columns) . ' VALUES ' .
         nel_format_multiple_values($values);
    $bind_values = array();
    nel_pdo_bind_set($bind_values, ':name', $poster_info['name'], PDO::PARAM_STR);
    nel_pdo_bind_set($bind_values, ':password', $poster_info['password'], PDO::PARAM_STR);
    nel_pdo_bind_set($bind_values, ':tripcode', $poster_info['tripcode'] === '' ? null : $poster_info['tripcode']);
    nel_pdo_bind_set($bind_values, ':secure_tripcode', $poster_info['secure_tripcode'] === '' ? null : $poster_info['secure_tripcode']);
    nel_pdo_bind_set($bind_values, ':email', $poster_info['email'], PDO::PARAM_STR);
    nel_pdo_bind_set($bind_values, ':subject', $poster_info['subject'], PDO::PARAM_STR);
    nel_pdo_bind_set($bind_values, ':comment', $poster_info['comment'], PDO::PARAM_STR);
    nel_pdo_bind_set($bind_values, ':ip_address', $_SERVER["REMOTE_ADDR"], PDO::PARAM_STR);
    nel_pdo_bind_set($bind_values, ':time', $time);
    nel_pdo_bind_set($bind_values, ':op', $poster_info['op'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':sage', 0, PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':modpost', $poster_info['modpost'], PDO::PARAM_STR);
    $results = nel_pdo_prepared_query($query, $bind_values);
}

function nel_db_insert_new_thread($thread_info, $files_count) // TODO: Update for externals and other new data
{
    $dbh = nel_get_db_handle();
    $columns = array('thread_id', 'first_post', 'last_post', 'last_bump_time', 'total_files', 'last_update', 'post_count');
    $values = array(':id', ':first', ':last', ':bump', ':files', ':time', ':posts');
    $query = 'INSERT INTO ' . THREAD_TABLE . ' ' . nel_format_multiple_columns($columns) . ' VALUES ' .
    nel_format_multiple_values($values);
    $bind_values = array();

    /*$prepared = $dbh->prepare('INSERT INTO ' . THREAD_TABLE . ' (
        thread_id,
        first_post,
        last_post,
        last_bump_time,
        total_files,
        last_update,
        post_count)
    VALUES
	   (:id,
        :first,
        :last,
        :bump,
        :files,
        :time,
        :posts)');*/

    nel_pdo_bind_set($bind_values, ':id', $thread_info['id'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':first', $thread_info['id'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':last', $thread_info['id'], PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':bump', $thread_info['last_bump_time']);
    nel_pdo_bind_set($bind_values, ':files', $files_count, PDO::PARAM_INT);
    nel_pdo_bind_set($bind_values, ':time', $thread_info['last_update']);
    nel_pdo_bind_set($bind_values, ':posts', 1, PDO::PARAM_INT);
    $results = nel_pdo_prepared_query($query, $bind_values);
}

function nel_db_update_thread($new_post_info, $thread_info)
{
    $dbh = nel_get_db_handle();
    $prepared = $dbh->prepare('UPDATE ' . THREAD_TABLE .
         ' SET last_post=?, last_update=?, post_count=? WHERE thread_id=?');
    $prepared->bindParam(1, $new_post_info['post_number'], PDO::PARAM_INT);
    $prepared->bindParam(2, $thread_info['last_update'], PDO::PARAM_INT);
    $prepared->bindParam(3, $thread_info['post_count'], PDO::PARAM_INT);
    $prepared->bindParam(4, $thread_info['id'], PDO::PARAM_INT);
    $prepared->execute();
    $prepared->closeCursor();
}

function nel_db_insert_new_files($parent_id, $new_post_info, $files)
{
    $dbh = nel_get_db_handle();
    $i = 1;

    foreach ($files as $file)
    {
        $dbh->query('UPDATE ' . POST_TABLE . ' SET has_file=1 WHERE post_number=' . $new_post_info['post_number'] . '');
        $prepared = $dbh->prepare('INSERT INTO ' . FILE_TABLE . ' (
                parent_thread,
                post_ref,
                file_order,
                supertype,
                subtype,
                mime,
                filename,
                extension,
                image_width,
                image_height,
                preview_name,
                preview_width,
                preview_height,
                filesize,
                md5,
                sha1,
                source,
                license)
            VALUES (
                :parent,
                :post,
                :order,
                :super,
                :sub,
                :mime,
                :filename,
                :ext,
                :imgx,
                :imgy,
                :prename,
                :prex,
                :prey,
                :filesize,
                :md5,
                :sha1,
                :source,
                :license)');
        $prepared->bindValue(':parent', $parent_id, PDO::PARAM_INT);
        $prepared->bindValue(':post', $new_post_info['post_number'], PDO::PARAM_INT);
        $prepared->bindValue(':order', $i, PDO::PARAM_INT);
        $prepared->bindValue(':super', $file['supertype'], PDO::PARAM_STR);
        $prepared->bindValue(':sub', $file['subtype'], PDO::PARAM_STR);
        $prepared->bindValue(':mime', $file['mime'], PDO::PARAM_STR);
        $prepared->bindValue(':filename', $file['filename'], PDO::PARAM_STR);
        $prepared->bindValue(':ext', $file['ext'], PDO::PARAM_STR);
        $prepared->bindValue(':imgx', $file['im_x'], PDO::PARAM_INT);
        $prepared->bindValue(':imgy', $file['im_y'], PDO::PARAM_INT);
        $prepared->bindValue(':prename', $file['thumbfile'], PDO::PARAM_STR);
        $prepared->bindValue(':prex', $file['pre_x'], PDO::PARAM_INT);
        $prepared->bindValue(':prey', $file['pre_y'], PDO::PARAM_INT);
        $prepared->bindValue(':filesize', $file['filesize'], PDO::PARAM_INT);
        $prepared->bindValue(':md5', $file['md5'], PDO::PARAM_STR);
        $prepared->bindValue(':sha1', $file['sha1'], PDO::PARAM_STR);
        $prepared->bindValue(':source', $file['source'], PDO::PARAM_STR);
        $prepared->bindValue(':license', $file['license'], PDO::PARAM_STR);
        $prepared->execute();
        $prepared->closeCursor();
        ++ $i;
    }
}