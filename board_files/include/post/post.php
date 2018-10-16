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
    $archive = new \Nelliel\ArchiveAndPrune($dbh, $board_id, new \Nelliel\FileHandler());
    $thread_handler = new \Nelliel\ThreadHandler($dbh, $board_id);
    $file_handler = new \Nelliel\FileHandler();
    $file_upload = new \Nelliel\post\FilesUpload($board_id, $_FILES);
    $data_handler = new \Nelliel\post\PostData($board_id);
    $post = new \Nelliel\Content\ContentPost($dbh, new \Nelliel\ContentID(), $board_id);
    $data_handler->processPostData($post);
    $time = get_millisecond_time();
    $post->content_data['post_time'] = $time;

    // Check if post is ok
    nel_is_post_ok($board_id, $post->content_data, $time);

    // Process FGSFDS
    if (!empty($post->content_data['fgsfds']))
    {
        $fgsfds = new \Nelliel\FGSFDS($post->content_data['fgsfds']);

        if($fgsfds->getCommand('sage') !== false)
        {
            $fgsfds->modifyCommandData('sage', 'value', true);
        }
    }

    $post->content_data['sage'] = $fgsfds->getCommandData('sage', 'value');
    $files = $file_upload->processFiles($post->content_data['response_to']);
    $spoon = !empty($files);
    $post->content_data['file_count'] = count($files);

    if (!$spoon)
    {
        if (!$post->content_data['comment'])
        {
            nel_derp(10, _gettext('Post contains no content or file. Dumbass.'), $error_data);
        }

        if ($board_settings['require_image_always'])
        {
            nel_derp(11, _gettext('Image or file required when making a new post.'), $error_data);
        }

        if ($board_settings['require_image_start'] && $post->content_data['response_to'] == 0)
        {
            nel_derp(12, _gettext('Image or file required to make new thread.'), $error_data);
        }
    }

    if (utf8_strlen($post->content_data['comment']) > $board_settings['max_comment_length'])
    {
        nel_derp(13, _gettext('Post is too long. Try looking up the word concise.'), $error_data);
    }

    if (isset($post->content_data['post_password']))
    {
        $cpass = $post->content_data['post_password'];
        $post->content_data['post_password'] = nel_generate_salted_hash(
                nel_parameters_and_data()->siteSettings('post_password_algorithm'), $post->content_data['post_password']);
    }
    else
    {
        $cpass = utf8_substr(rand(), 0, 8);
    }

    // Cookies OM NOM NOM NOM
    setrawcookie('pwd-' . $board_id, $cpass, time() + 30 * 24 * 3600, '/'); // 1 month cookie expiration
    setrawcookie('name-' . $board_id, $post->content_data['poster_name'], time() + 30 * 24 * 3600, '/'); // 1 month cookie expiration

    // Go ahead and put post into database
    $post->content_data['op'] = ($post->content_data['parent_thread'] == 0) ? 1 : 0;
    $post->content_data['has_file'] = ($post->content_data['file_count'] > 0) ? 1 : 0;
    $post->reserveDatabaseRow($time);
    $thread = new \Nelliel\Content\ContentThread($dbh, new \Nelliel\ContentID(), $board_id);

    if ($post->content_data['response_to'] == 0)
    {
        $thread->content_id->thread_id = $post->content_id->post_id;
        $thread->content_data['first_post'] = $post->content_id->post_id;
        $thread->content_data['last_post'] = $post->content_id->post_id;
        $thread->content_data['last_bump_time'] = $time;
        $thread->content_data['total_files'] = $post->content_data['file_count'];
        $thread->content_data['last_update'] = $time;
        $thread->content_data['post_count'] = 1;
        $thread->writeToDatabase();
        $thread->createDirectories();
    }
    else
    {
        $thread->content_id->thread_id = $post->content_data['parent_thread'];
        $thread->loadFromDatabase();
        $thread->content_data['total_files'] = $thread->content_data['total_files'] + $post->content_data['file_count'];
        $thread->content_data['last_update'] = $time;
        $thread->content_data['post_count'] = $thread->content_data['post_count'] + 1;

        if ($thread->content_data['post_count'] <= $board_settings['max_bumps'] && !$fgsfds->getCommandData('sage', 'value'))
        {
            $thread->content_data['last_bump_time'] = $time;
        }

        $thread->writeToDatabase();
    }

    $post->writeToDatabase();
    $post->createDirectories();
    $fgsfds->modifyCommandData('noko', 'topic', $thread->content_id->thread_id);
    $src_path = $references['src_path'] . $thread->content_id->thread_id . '/' . $post->content_id->post_id . '/';
    $preview_path = $references['thumb_path'] . $thread->content_id->thread_id . '/' . $post->content_id->post_id .
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
            $file->content_data['parent_thread'] = $thread->content_id->thread_id;
            $file->content_id->post_id = $post->content_id->post_id;
            $file->content_data['post_ref'] = $post->content_id->post_id;
            $file->content_id->order_id = $order;
            $file->content_data['file_order'] = $order;
            $file_handler->moveFile($file->content_data['location'], $src_path . $file->content_data['fullname'], true,
                    DIRECTORY_PERM);
            chmod($src_path . $file->content_data['fullname'], octdec(FILE_PERM));
            $file->writeToDatabase();
            ++ $order;
        }
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
