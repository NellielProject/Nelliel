<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_process_new_post($board_id)
{
    global $plugins;
    $dbh = nel_database();
    $error_data = array('board_id' => $board_id);
    $references = nel_parameters_and_data()->boardReferences($board_id);
    $archive = new \Nelliel\ArchiveAndPrune($board_id);
    $thread_handler = new \Nelliel\ThreadHandler($board_id);
    $file_handler = new \Nelliel\FileHandler();
    $file_upload = new \Nelliel\FilesUpload($board_id, $_FILES);
    $data_handler = new \Nelliel\PostData($board_id);
    $post_data = $data_handler->collectData();
    $new_thread_dir = '';

    // Get time
    $time = get_millisecond_time();
    $reply_delay = $time - (nel_parameters_and_data()->boardSettings($board_id, 'reply_delay')* 1000);

    // Check if post is ok
    $post_count = nel_is_post_ok($board_id, $post_data, $time);

    // Process FGSFDS
    if (!empty($post_data['fgsfds']))
    {
        $fgsfds_commands = preg_split('#[\s,]#u', $post_data['fgsfds']);
        nel_fgsfds('noko', in_array('noko', $fgsfds_commands));
        nel_fgsfds('sage', in_array('sage', $fgsfds_commands));
    }

    $post_data['sage'] = (empty(nel_fgsfds('sage'))) ? 0 : nel_fgsfds('sage');
    $files = $file_upload->processFiles($post_data['response_to']);
    $spoon = !empty($files);
    $post_data['file_count'] = count($files);

    if(!$spoon)
    {
        if (!$post_data['comment'])
        {
            nel_derp(10, _gettext('Post contains no content or file. Dumbass.'), $error_data);
        }

        if (nel_parameters_and_data()->boardSettings($board_id, 'require_image_always'))
        {
            nel_derp(11, _gettext('Image or file required when making a new post.'), $error_data);
        }

        if (nel_parameters_and_data()->boardSettings($board_id, 'require_image_start') && $post_data['response_to'] === 0)
        {
            nel_derp(12, _gettext('Image or file required to make new thread.'), $error_data);
        }
    }

    // Cancer-fighting tools and lulz
    if (utf8_strlen($post_data['comment']) > nel_parameters_and_data()->boardSettings($board_id, 'max_comment_length'))
    {
        nel_derp(13, _gettext('Post is too long. Try looking up the word concise.'), $error_data);
    }

    if (isset($post_data['password']))
    {
        $cpass = $post_data['password'];
        $post_data['password'] = nel_generate_salted_hash(nel_parameters_and_data()->siteSettings('post_password_algorithm'), $post_data['password']);
    }
    else
    {
        $cpass = utf8_substr(rand(), 0, 8);
    }

    nel_banned_text($post_data['comment'], $files);

    // Cookies OM NOM NOM NOM
    setrawcookie('pwd-' . $board_id, $cpass, time() + 30 * 24 * 3600, '/'); // 1 month cookie expiration
    setrawcookie('name-' . $board_id, $post_data['name'], time() + 30 * 24 * 3600, '/'); // 1 month cookie expiration

    // Go ahead and put post into database
    require_once INCLUDE_PATH . 'post/database_functions.php';
    $post_data['op'] = ($post_data['parent_thread'] === 0) ? 1 : 0;
    $post_data['has_file'] = ($post_data['file_count'] > 0) ? 1 : 0;
    nel_db_insert_initial_post($board_id, $time, $post_data);
    $prepared = $dbh->prepare('SELECT * FROM "' . $references['post_table'] . '" WHERE "post_time" = ? LIMIT 1');
    $new_post_info = $dbh->executePreparedFetch($prepared, array($time), PDO::FETCH_ASSOC, true);
    $thread_info = array();

    if ($post_data['parent_thread'] === 0)
    {
        $thread_info['last_update'] = $time;
        $thread_info['post_count'] = 1;
        $thread_info['last_bump_time'] = $time;
        $thread_info['id'] = $new_post_info['post_number'];
        $thread_info['total_files'] = $post_data['file_count'];
        nel_db_insert_new_thread($board_id, $thread_info);
        $thread_handler->createThreadDirectories($thread_info['id']);
    }
    else
    {
        $thread_info['id'] = $post_data['parent_thread'];
        $prepared = $dbh->prepare('SELECT * FROM "' . $references['thread_table']. '" WHERE "thread_id" = ? LIMIT 1');
        $current_thread = $dbh->executePreparedFetch($prepared, array($thread_info['id']), PDO::FETCH_ASSOC, true);
        $thread_info['last_update'] = $current_thread['last_update'];
        $thread_info['post_count'] = $current_thread['post_count'] + 1;
        $thread_info['last_bump_time'] = $time;
        $thread_info['total_files'] = $current_thread['total_files'] + $post_data['file_count'];

        if ($current_thread['post_count'] > nel_parameters_and_data()->boardSettings($board_id, 'max_bumps') || nel_fgsfds('sage'))
        {
            $thread_info['last_bump_time'] = $current_thread['last_bump_time'];
        }

        nel_db_update_thread($board_id, $new_post_info, $thread_info);
    }

    $prepared = $dbh->prepare('UPDATE "' . $references['post_table']. '" SET parent_thread = ? WHERE post_number = ?');
    $dbh->executePrepared($prepared, array($thread_info['id'], $new_post_info['post_number']), true);
    nel_fgsfds('noko_topic', $thread_info['id']);
    $src_path = $references['src_path'] . $thread_info['id'] . '/' . $new_post_info['post_number'] . '/';
    $preview_path = $references['thumb_path'] . $thread_info['id'] . '/' . $new_post_info['post_number'] . '/';

    // Make thumbnails and do final file processing
    $gen_previews = new \Nelliel\GeneratePreviews($board_id);
    $files = $gen_previews->generate($files, $preview_path);
    clearstatcache();

    // Add file data and move uploads to final location if applicable
    if ($spoon)
    {
        foreach($files as $file)
        {
            $file_handler->moveFile($file['location'], $src_path . $file['fullname'], true, DIRECTORY_PERM);
            chmod($src_path . $file['fullname'], octdec(FILE_PERM));
        }

        nel_db_insert_new_files($board_id, $thread_info['id'], $new_post_info, $files);
    }

    // Run the archiving routine if this is a new thread or deleted/expired thread
    $archive->updateAllArchiveStatus();

    if(nel_parameters_and_data()->boardSettings($board_id, 'old_threads') === 'ARCHIVE')
    {
        $archive->moveThreadsToArchive();
    }
    else if(nel_parameters_and_data()->boardSettings($board_id, 'old_threads') === 'PRUNE')
    {
        $archive->pruneThreads();
    }

    // Generate response page if it doesn't exist, otherwise update
    nel_regen_threads($board_id, true, array($thread_info['id']));
    nel_regen_index($board_id);
    return $thread_info['id'];
}

function nel_is_post_ok($board_id, $post_data, $time)
{
    $dbh = nel_database();
    $references = nel_parameters_and_data()->boardReferences($board_id);
    $error_data = array('board_id' => $board_id);

    // Check for flood
    // If post is a reply, also check if the thread still exists

    if ($post_data['parent_thread'] == 0) // TODO: Update this, doesn't look right
    {
        $thread_delay = $time - (nel_parameters_and_data()->boardSettings($board_id, 'thread_delay') * 1000);
        $prepared = $dbh->prepare('SELECT COUNT(*) FROM "' . $references['post_table']. '" WHERE "post_time" > ? AND "ip_address" = ?');
        $prepared->bindValue(1, $thread_delay, PDO::PARAM_STR);
        $prepared->bindValue(2, @inet_pton($_SERVER["REMOTE_ADDR"]), PDO::PARAM_LOB);
        $renzoku = $dbh->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
    }
    else
    {
        $thread_delay = $time - (nel_parameters_and_data()->boardSettings($board_id, 'reply_delay') * 1000);
        $prepared = $dbh->prepare('SELECT COUNT(*) FROM "' . $references['post_table'].
             '" WHERE "parent_thread" = ? AND "post_time" > ? AND "ip_address" = ?');
        $prepared->bindValue(1, $post_data['parent_thread'], PDO::PARAM_INT);
        $prepared->bindValue(2, $thread_delay, PDO::PARAM_STR);
        $prepared->bindValue(3, @inet_pton($_SERVER["REMOTE_ADDR"]), PDO::PARAM_LOB);
        $renzoku = $dbh->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
    }

    if ($renzoku > 0)
    {
        nel_derp(1, _gettext('Flood detected! You\'re posting too fast, slow the fuck down.'), $error_data);
    }

    $post_count = 1;

    if ($post_data['parent_thread'] != 0)
    {
        $prepared = $dbh->prepare('SELECT * FROM "' . $references['thread_table']. '" WHERE "thread_id" = ? LIMIT 1');
        $op_post = $dbh->executePreparedFetch($prepared, array($post_data['parent_thread']), PDO::FETCH_ASSOC, true);

        if (!empty($op_post))
        {
            if ($op_post['locked'] == 1)
            {
                nel_derp(2, _gettext('This thread is locked.'), $error_data);
            }

            if ($op_post['archive_status'] != 0)
            {
                nel_derp(3, _gettext('The thread you have tried posting in is currently inaccessible or archived.'), $error_data);
            }

            $post_count = $op_post['post_count'];
        }
        else
        {
            nel_derp(4, _gettext('The thread you have tried posting in could not be found.'), $error_data);
        }

        if ($post_count >= nel_parameters_and_data()->boardSettings($board_id, 'max_posts'))
        {
            nel_derp(5, _gettext('The thread has reached maximum posts.'), $error_data);
        }

        if ($op_post['archive_status'] != 0)
        {
            nel_derp(6, _gettext('The thread is archived or buffered and cannot be posted to.'), $error_data);
        }
    }

    return $post_count;
}
