<?php

function nel_db_insert_initial_post($time, $poster_info, $dbh)
{
    global $fgsfds;
    $prepared = $dbh->prepare('INSERT INTO ' . POST_TABLE . ' (
        name,
        password,
        tripcode,
        secure_tripcode,
        email,
        subject,
        comment,
        host,
        post_time,
        op,
        sage,
        mod_post)
    VALUES
	   (:name,
        :password,
        :tripcode,
        :secure_tripcode,
        :email,
        :subject,
        :comment,
        :host,
        :time,
        :op,
        :sage,
        :modpost)');
    
    $prepared->bindValue(':name', $poster_info['name'], PDO::PARAM_STR);
    
    if ($poster_info['tripcode'] === '')
    {
        $prepared->bindValue(':tripcode', NULL, PDO::PARAM_NULL);
    }
    else
    {
        $prepared->bindValue(':tripcode', $poster_info['tripcode'], PDO::PARAM_STR);
    }
    
    if ($poster_info['secure_tripcode'] === '')
    {
        $prepared->bindValue(':secure_tripcode', NULL, PDO::PARAM_NULL);
    }
    else
    {
        $prepared->bindValue(':secure_tripcode', $poster_info['secure_tripcode'], PDO::PARAM_STR);
    }
    
    $prepared->bindValue(':email', $poster_info['email'], PDO::PARAM_STR);
    $prepared->bindValue(':subject', $poster_info['subject'], PDO::PARAM_STR);
    $prepared->bindValue(':comment', $poster_info['comment'], PDO::PARAM_STR);
    $prepared->bindValue(':host', @inet_pton($_SERVER["REMOTE_ADDR"]), PDO::PARAM_STR);
    $prepared->bindValue(':password', $poster_info['pass'], PDO::PARAM_STR);
    $prepared->bindValue(':time', $time);
    $prepared->bindValue(':op', $poster_info['op'], PDO::PARAM_INT);
    if ($fgsfds['sage'])
    {
        $prepared->bindValue(':sage', 1, PDO::PARAM_INT);
    }
    else
    {
        $prepared->bindValue(':sage', 0, PDO::PARAM_INT);
    }
    
    $prepared->bindValue(':modpost', $poster_info['modpost'], PDO::PARAM_INT);
    $prepared->execute();
    $prepared->closeCursor();
}

function nel_db_insert_new_thread($time, $new_post_info, $files_count, $dbh)
{
    $prepared = $dbh->prepare('INSERT INTO ' . THREAD_TABLE . ' (
        thread_id,
        first_post,
        last_post,
        total_files,
        last_update,
        post_count)
    VALUES
	   (:id,
        :first,
        :last,
        :files,
        :time,
        :posts)');
    
    $prepared->bindValue(':id', $new_post_info['post_number'], PDO::PARAM_INT);
    $prepared->bindValue(':first', $new_post_info['post_number'], PDO::PARAM_INT);
    $prepared->bindValue(':last', $new_post_info['post_number'], PDO::PARAM_INT);
    $prepared->bindValue(':files', $files_count, PDO::PARAM_INT);
    $prepared->bindValue(':time', $time);
    $prepared->bindValue(':posts', 1, PDO::PARAM_INT);
    $prepared->execute();
    $prepared->closeCursor();
}

function nel_db_insert_new_files($parent_id, $new_post_info, $files)
{
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
                source,
                license)
            VALUES (' . '
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
                :md5
                :source,
                :license)');
        $prepared->bindValue(':parent', $parent_id, PDO::PARAM_INT);
        $prepared->bindValue(':post', $new_post_info['post_number'], PDO::PARAM_INT);
        $prepared->bindValue(':order', $i, PDO::PARAM_INT);
        $prepared->bindValue(':super', $file['supertype'], PDO::PARAM_STR);
        $prepared->bindValue(':sub', $file['subtype'], PDO::PARAM_STR);
        $prepared->bindValue(':mime', $file['mime'], PDO::PARAM_STR);
        $prepared->bindValue(':filename', $file['basic_filename'], PDO::PARAM_STR);
        $prepared->bindValue(':ext', $file['ext'], PDO::PARAM_STR);
        $prepared->bindValue(':imgx', $file['im_x'], PDO::PARAM_INT);
        $prepared->bindValue(':imgy', $file['im_y'], PDO::PARAM_INT);
        $prepared->bindValue(':prename', $file['thumbfile'], PDO::PARAM_STR);
        $prepared->bindValue(':prex', $file['pre_x'], PDO::PARAM_INT);
        $prepared->bindValue(':prey', $file['pre_y'], PDO::PARAM_INT);
        $prepared->bindValue(':filesize', $file['fsize'], PDO::PARAM_INT);
        $prepared->bindValue(':md5', $file['md5'], PDO::PARAM_STR);
        $prepared->bindValue(':source', $file['file_source'], PDO::PARAM_STR);
        $prepared->bindValue(':license', $file['file_license'], PDO::PARAM_STR);
        $prepared->execute();
        $prepared->closeCursor();
        ++ $i;
    }
}