<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'post/filetypes.php';
require_once INCLUDE_PATH . 'post/file_functions.php';
require_once INCLUDE_PATH . 'post/post_data.php';

function nel_process_new_post($board_id)
{
    global $enabled_types, $plugins, $filetypes;
    $dbh = nel_database();
    $references = nel_board_references($board_id);
    $archive = nel_archive($board_id);
    $thread_handler = nel_thread_handler($board_id);
    $post_data = nel_collect_post_data($board_id);
    $new_thread_dir = '';

    // Get time
    $time = get_millisecond_time();
    $reply_delay = $time - (nel_board_settings($board_id, 'reply_delay')* 1000);

    // Check if post is ok
    $post_count = nel_is_post_ok($board_id, $post_data, $time);

    // Process FGSFDS
    if (!is_null($post_data['fgsfds']))
    {
        nel_fgsfds('noko', nel_is_in_string($post_data['fgsfds'], 'noko'));
        nel_fgsfds('sage', nel_is_in_string($post_data['fgsfds'], 'sage'));
    }

    $post_data['sage'] = (is_null(nel_fgsfds('sage'))) ? 0 : nel_fgsfds('sage');

    // Start collecting file info
    $files = nel_process_file_info($board_id);
    $spoon = false;
    $files_count = 0;

    if (!empty($files))
    {
        $files_count = count($files);
        $spoon = true;
    }
    else
    {
        $files = array();

        if (!$post_data['comment'])
        {
            nel_derp(10, nel_stext('ERROR_10'), $board_id);
        }

        if (nel_board_settings($board_id, 'require_image_always'))
        {
            nel_derp(11, nel_stext('ERROR_11'), $board_id);
        }

        if (nel_board_settings($board_id, 'require_image_start') && $post_data['response_to'] === 0)
        {
            nel_derp(12, nel_stext('ERROR_12'), $board_id);
        }
    }

    // Cancer-fighting tools and lulz
    if (utf8_strlen($post_data['comment']) > nel_board_settings($board_id, 'max_comment_length'))
    {
        nel_derp(13, nel_stext('ERROR_13'), $board_id);
    }

    if (isset($post_data['password']))
    {
        $cpass = $post_data['password'];
        $post_data['password'] = nel_generate_salted_hash(nel_site_settings('post_password_algorithm'), $post_data['password']);
    }
    else
    {
        $cpass = utf8_substr(rand(), 0, 8);
    }

    nel_banned_text($post_data['comment'], $files);
    $cookie_name = $post_data['name'];

    // Cookies OM NOM NOM NOM
    setcookie('pwd-' . $board_id, $cpass, time() + 30 * 24 * 3600, '/'); // 1 month cookie expiration
    setcookie('name-' . $board_id, $cookie_name, time() + 30 * 24 * 3600, '/'); // 1 month cookie expiration
    $post_data = $plugins->plugin_hook('after-post-info-processing', TRUE, array($post_data));
    $i = 0;

    // Go ahead and put post into database
    require_once INCLUDE_PATH . 'post/database_functions.php';

    if ($post_data['parent_thread'] === 0)
    {
        $post_data['op'] = 1;
    }
    else
    {
        $post_data['op'] = 0;
    }

    $files_count = count($files);
    $post_data['file_count'] = $files_count;
    $post_data['has_file'] = ($files_count > 0) ? 1 : 0;
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
        $thread_info['total_files'] = $files_count;
        nel_db_insert_new_thread($board_id, $thread_info, $files_count);
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
        $thread_info['total_files'] = $current_thread['total_files'] + count($files);

        if ($current_thread['post_count'] > nel_board_settings($board_id, 'max_bumps') || nel_fgsfds('sage'))
        {
            $thread_info['last_bump_time'] = $current_thread['last_bump_time'];
        }

        nel_db_update_thread($board_id, $new_post_info, $thread_info);
    }

    $prepared = $dbh->prepare('UPDATE "' . $references['post_table']. '" SET parent_thread = ? WHERE post_number = ?');
    $dbh->executePrepared($prepared, array($thread_info['id'], $new_post_info['post_number']), true);

    nel_fgsfds('noko_topic', $thread_info['id']);
    $srcpath = $references['src_path'] . $thread_info['id'] . '/';
    $thumbpath = $references['thumb_path'] . $thread_info['id'] . '/';

    // Make thumbnails and do final file processing
    $files = nel_generate_thumbnails($board_id, $files, $srcpath, $thumbpath);
    clearstatcache();

    // Add file data if applicable
    if ($spoon)
    {
        nel_db_insert_new_files($board_id, $thread_info['id'], $new_post_info, $files);
    }

    // Run the archiving routine if this is a new thread or deleted/expired thread
    $archive->updateAllArchiveStatus();

    if(nel_board_settings($board_id, 'old_threads') === 'ARCHIVE')
    {
        $archive->moveThreadsToArchive();
    }
    else if(nel_board_settings($board_id, 'old_threads') === 'PRUNE')
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
    $references = nel_board_references($board_id);

    // Check for flood
    // If post is a reply, also check if the thread still exists

    if ($post_data['parent_thread'] == 0) // TODO: Update this, doesn't look right
    {
        $thread_delay = $time - (nel_board_settings($board_id, 'thread_delay') * 1000);
        $prepared = $dbh->prepare('SELECT COUNT(*) FROM "' . $references['post_table']. '" WHERE "post_time" > ? AND "ip_address" = ?');
        $prepared->bindValue(1, $thread_delay, PDO::PARAM_STR);
        $prepared->bindValue(2, @inet_pton($_SERVER["REMOTE_ADDR"]), PDO::PARAM_LOB);
        $renzoku = $dbh->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
    }
    else
    {
        $thread_delay = $time - (nel_board_settings($board_id, 'reply_delay') * 1000);
        $prepared = $dbh->prepare('SELECT COUNT(*) FROM "' . $references['post_table'].
             '" WHERE "parent_thread" = ? AND "post_time" > ? AND "ip_address" = ?');
        $prepared->bindValue(1, $post_data['parent_thread'], PDO::PARAM_INT);
        $prepared->bindValue(2, $thread_delay, PDO::PARAM_STR);
        $prepared->bindValue(3, @inet_pton($_SERVER["REMOTE_ADDR"]), PDO::PARAM_LOB);
        $renzoku = $dbh->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
    }

    if ($renzoku > 0)
    {
        nel_derp(1, nel_stext('ERROR_1'), $board_id);
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
                nel_derp(2, nel_stext('ERROR_2'), $board_id);
            }

            if ($op_post['archive_status'] != 0)
            {
                nel_derp(3, nel_stext('ERROR_3'), $board_id);
            }

            $post_count = $op_post['post_count'];
        }
        else
        {
            nel_derp(4, nel_stext('ERROR_4'), $board_id);
        }

        if ($post_count >= nel_board_settings($board_id, 'max_posts'))
        {
            nel_derp(5, nel_stext('ERROR_5'), $board_id);
        }

        if ($op_post['archive_status'] != 0)
        {
            nel_derp(6, nel_stext('ERROR_6'), $board_id);
        }
    }

    return $post_count;
}
