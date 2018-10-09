<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_process_new_post($inputs)
{
    $dbh = nel_database();
    $board_id = $inputs['board_id'];
    $board_settings = nel_parameters_and_data()->boardSettings($board_id);
    $error_data = array('board_id' => $board_id);
    $references = nel_parameters_and_data()->boardReferences($board_id);
    $archive = new \Nelliel\ArchiveAndPrune($board_id);
    $thread_handler = new \Nelliel\ThreadHandler($board_id);
    $file_handler = new \Nelliel\FileHandler();
    $file_upload = new \Nelliel\post\FilesUpload($board_id, $_FILES);
    $data_handler = new \Nelliel\post\PostData($board_id);
    $database_functions = new \Nelliel\post\PostDatabaseFunctions($board_id);
    $post = new \Nelliel\ContentPost($dbh, new \Nelliel\ContentID('nci_0_0_0'), $board_id);
    $data_handler->processPostData($post);
    $post_data = $data_handler->collectData();
    $time = get_millisecond_time();
    $post->post_data['post_time'] = $time;

    // Check if post is ok
    nel_is_post_ok($board_id, $post_data, $time);

    // Process FGSFDS
    if (!empty($post_data['fgsfds']))
    {
        $fgsfds_commands = preg_split('#[\s,]#u', $post_data['fgsfds']);
        nel_fgsfds('noko', in_array('noko', $fgsfds_commands));
        nel_fgsfds('sage', in_array('sage', $fgsfds_commands));
    }

    $post_data['sage'] = (empty(nel_fgsfds('sage'))) ? 0 : nel_fgsfds('sage');
    $post->post_data['sage'] = (empty(nel_fgsfds('sage'))) ? 0 : nel_fgsfds('sage');
    $files = $file_upload->processFiles($post_data['response_to']);
    $spoon = !empty($files);
    $post_data['file_count'] = count($files);
    $post->post_data['file_count'] = count($files);

    if (!$spoon)
    {
        if (!$post_data['comment'] || !$post->post_data['comment'])
        {
            nel_derp(10, _gettext('Post contains no content or file. Dumbass.'), $error_data);
        }

        if ($board_settings['require_image_always'])
        {
            nel_derp(11, _gettext('Image or file required when making a new post.'), $error_data);
        }

        if ($board_settings['require_image_start'] &&
                ($post_data['response_to'] === 0 || $post->post_data['response_to'] == 0))
        {
            nel_derp(12, _gettext('Image or file required to make new thread.'), $error_data);
        }
    }

    if (utf8_strlen($post_data['comment']) > $board_settings['max_comment_length'] ||
            utf8_strlen($post->post_data['comment']) > $board_settings['max_comment_length'])
    {
        nel_derp(13, _gettext('Post is too long. Try looking up the word concise.'), $error_data);
    }

    if (isset($post_data['password']) || isset($post->post_data['password']))
    {
        $cpass = $post->post_data['password'];
        $cpass = $post_data['password'];
        $post->post_data['password'] = nel_generate_salted_hash(
                nel_parameters_and_data()->siteSettings('post_password_algorithm'), $post->post_data['password']);
        $post_data['password'] = nel_generate_salted_hash(
                nel_parameters_and_data()->siteSettings('post_password_algorithm'), $post_data['password']);
    }
    else
    {
        $cpass = utf8_substr(rand(), 0, 8);
    }

    // Cookies OM NOM NOM NOM
    setrawcookie('pwd-' . $board_id, $cpass, time() + 30 * 24 * 3600, '/'); // 1 month cookie expiration
    setrawcookie('name-' . $board_id, $post->post_data['name'], time() + 30 * 24 * 3600, '/'); // 1 month cookie expiration
    setrawcookie('name-' . $board_id, $post_data['name'], time() + 30 * 24 * 3600, '/'); // 1 month cookie expiration

    // Go ahead and put post into database
    $post->post_data['op'] = ($post->post_data['parent_thread'] == 0) ? 1 : 0;
    $post->post_data['has_file'] = ($post->post_data['file_count'] > 0) ? 1 : 0;
    $post_data['op'] = ($post_data['parent_thread'] == 0) ? 1 : 0;
    $post_data['has_file'] = ($post_data['file_count'] > 0) ? 1 : 0;
    $post->reserveDatabaseRow($time);
    $post->writeToDatabase();
    //$database_functions->insertInitialPost($time, $post_data);
    $prepared = $dbh->prepare('SELECT * FROM "' . $references['post_table'] . '" WHERE "post_time" = ? LIMIT 1');
    $new_post_info = $dbh->executePreparedFetch($prepared, array($time), PDO::FETCH_ASSOC, true);
    $post->content_id->post_id = $new_post_info['post_number'];
    $thread = new \Nelliel\ContentThread($dbh, new \Nelliel\ContentID('nci_0_0_0'), $board_id);

    if ($post_data['parent_thread'] == 0 || $post->post_data['parent_thread'] == 0)
    {
        $thread->content_id->thread_id = $new_post_info['post_number'];
        $thread->thread_data['first_post'] = $new_post_info['post_number'];
        $thread->thread_data['last_post'] = $new_post_info['post_number'];
        $thread->thread_data['last_bump_time'] = $time;
        $thread->thread_data['total_files'] = $post->post_data['file_count'];
        $thread->thread_data['total_files'] = $post_data['file_count'];
        $thread->thread_data['last_update'] = $time;
        $thread->thread_data['post_count'] = 1;
        $thread->writeToDatabase();
        $thread->createDirectories();
        //$thread_handler->createThreadDirectories($thread->content_id->thread_id);
    }
    else
    {
        $thread->content_id->thread_id = $post->post_data['parent_thread'];
        $thread->content_id->thread_id = $post_data['parent_thread'];
        $thread->loadFromDatabase();
        $thread->thread_data['total_files'] = $thread->thread_data['total_files'] + $post->post_data['file_count'];
        $thread->thread_data['total_files'] = $thread->thread_data['total_files'] + $post_data['file_count'];
        $thread->thread_data['last_update'] = $time;
        $thread->thread_data['post_count'] = $thread->thread_data['post_count'] + 1;

        if ($thread->thread_data['post_count'] <= $board_settings['max_bumps'] && !nel_fgsfds('sage'))
        {
            $thread->thread_data['last_bump_time'] = $time;
        }

        $thread->writeToDatabase();
    }

    $post->createDirectories();

    $prepared = $dbh->prepare('UPDATE "' . $references['post_table'] . '" SET parent_thread = ? WHERE post_number = ?');
    $dbh->executePrepared($prepared, array($thread->content_id->thread_id, $new_post_info['post_number']), true);
    nel_fgsfds('noko_topic', $thread->content_id->thread_id);
    $src_path = $references['src_path'] . $thread->content_id->thread_id . '/' . $new_post_info['post_number'] . '/';
    $preview_path = $references['thumb_path'] . $thread->content_id->thread_id . '/' . $new_post_info['post_number'] .
            '/';

    // Make thumbnails and do final file processing
    $gen_previews = new \Nelliel\post\GeneratePreviews($board_id);
    $files = $gen_previews->generate($files, $preview_path);
    clearstatcache();

    // Add file data and move uploads to final location if applicable
    if ($spoon)
    {
        $order = 1;

        foreach ($files as $file)
        {
            $file->content_id->thread_id = $thread->content_id->thread_id;
            $file->file_data['parent_thread'] = $thread->content_id->thread_id;
            $file->content_id->post_id = $post->content_id->post_id;
            $file->file_data['post_ref'] = $post->content_id->post_id;
            $file->content_id->order_id = $order;
            $file->file_data['file_order'] = $order;
            //$file_handler->moveFile($file['location'], $src_path . $file['fullname'], true, DIRECTORY_PERM);
            //chmod($src_path . $file['fullname'], octdec(FILE_PERM));
            $file_handler->moveFile($file->file_data['location'], $src_path . $file->file_data['fullname'], true,
                    DIRECTORY_PERM);
            chmod($src_path . $file->file_data['fullname'], octdec(FILE_PERM));
            $file->writeToDatabase();
            ++ $order;
        }

        //$database_functions->insertNewFiles($thread->content_id->thread_id, $new_post_info, $files);
    }

    $archive->updateAllArchiveStatus();

    if ($board_settings['old_threads'] === 'ARCHIVE')
    {
        $archive->moveThreadsToArchive();
    }
    else if ($board_settings['old_threads'] === 'PRUNE')
    {
        $archive->pruneThreads();
    }

    // Generate response page if it doesn't exist, otherwise update
    $regen = new \Nelliel\Regen();
    $regen->threads($board_id, true, array($thread->content_id->thread_id));
    $regen->index($board_id);
    return $thread->content_id->thread_id;
}

function nel_is_post_ok($board_id, $post_data, $time)
{
    $dbh = nel_database();
    $board_settings = nel_parameters_and_data()->boardSettings($board_id);
    $references = nel_parameters_and_data()->boardReferences($board_id);
    $error_data = array('board_id' => $board_id);

    // Check for flood
    // If post is a reply, also check if the thread still exists

    if ($post_data['parent_thread'] === 0) // TODO: Update this, doesn't look right
    {
        $thread_delay = $time - ($board_settings['thread_delay'] * 1000);
        $prepared = $dbh->prepare(
                'SELECT COUNT(*) FROM "' . $references['post_table'] . '" WHERE "post_time" > ? AND "ip_address" = ?');
        $prepared->bindValue(1, $thread_delay, PDO::PARAM_STR);
        $prepared->bindValue(2, @inet_pton($_SERVER['REMOTE_ADDR']), PDO::PARAM_LOB);
        $renzoku = $dbh->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
    }
    else
    {
        $reply_delay = $time - ($board_settings['reply_delay'] * 1000);
        $prepared = $dbh->prepare(
                'SELECT COUNT(*) FROM "' . $references['post_table'] .
                '" WHERE "parent_thread" = ? AND "post_time" > ? AND "ip_address" = ?');
        $prepared->bindValue(1, $post_data['parent_thread'], PDO::PARAM_INT);
        $prepared->bindValue(2, $reply_delay, PDO::PARAM_STR);
        $prepared->bindValue(3, @inet_pton($_SERVER['REMOTE_ADDR']), PDO::PARAM_LOB);
        $renzoku = $dbh->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
    }

    if ($renzoku > 0)
    {
        nel_derp(1, _gettext('Flood detected! You\'re posting too fast, slow the fuck down.'), $error_data);
    }

    if ($post_data['parent_thread'] != 0)
    {
        $prepared = $dbh->prepare(
                'SELECT "post_count", "archive_status", "locked" FROM "' . $references['thread_table'] .
                '" WHERE "thread_id" = ? LIMIT 1');
        $thread_info = $dbh->executePreparedFetch($prepared, array($post_data['parent_thread']), PDO::FETCH_ASSOC, true);

        if (!empty($thread_info))
        {
            if ($thread_info['locked'] == 1)
            {
                nel_derp(2, _gettext('This thread is locked.'), $error_data);
            }

            if ($thread_info['archive_status'] != 0)
            {
                nel_derp(3, _gettext('The thread you have tried posting in is currently inaccessible or archived.'),
                        $error_data);
            }
        }
        else
        {
            nel_derp(4, _gettext('The thread you have tried posting in could not be found.'), $error_data);
        }

        if ($thread_info['post_count'] >= $board_settings['max_posts'])
        {
            nel_derp(5, _gettext('The thread has reached maximum posts.'), $error_data);
        }

        if ($thread_info['archive_status'] != 0)
        {
            nel_derp(6, _gettext('The thread is archived or buffered and cannot be posted to.'), $error_data);
        }
    }
}
