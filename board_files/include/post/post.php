<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'post/filetypes.php';
require_once INCLUDE_PATH . 'post/file-functions.php';
require_once INCLUDE_PATH . 'post/post-data.php';

function nel_process_new_post($dataforce)
{
    global $enabled_types, $fgsfds, $plugins, $filetypes;
    $dbh = nel_database();
    $post_data = nel_collect_post_data();
    $new_thread_dir = '';

    // Get time
    $time = get_millisecond_time();
    $reply_delay = $time - (nel_board_settings('reply_delay')* 1000);

    // Check if post is ok
    $post_count = nel_is_post_ok($post_data, $time);

    // Process FGSFDS
    if (!is_null($post_data['fgsfds']))
    {
        $fgsfds = $plugins->plugin_hook('fgsfds_field', FALSE, array($fgsfds));
        $fgsfds['noko'] = nel_is_in_string($post_data['fgsfds'], 'noko');
        $fgsfds['sage'] = nel_is_in_string($post_data['fgsfds'], 'sage');
    }

    // Start collecting file info
    $files = nel_process_file_info();
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
            nel_derp(10, array(), nel_stext('ERROR_10'));
        }

        if (nel_board_settings('require_image_always'))
        {
            nel_derp(11, array(), nel_stext('ERROR_11'));
        }

        if (nel_board_settings('require_image_start')&& $dataforce['response_to'] === 0)
        {
            nel_derp(12, array(), nel_stext('ERROR_12'));
        }
    }

    // Cancer-fighting tools and lulz
    if (utf8_strlen($post_data['comment']) > nel_board_settings('max_comment_length'))
    {
        nel_derp(13, array(), nel_stext('ERROR_13'));
    }

    if (isset($post_data['password']))
    {
        $cpass = $post_data['password'];
        $post_data['password'] = nel_generate_salted_hash(POST_PASSWORD_ALGORITHM, $post_data['password']);
    }
    else
    {
        $cpass = utf8_substr(rand(), 0, 8);
    }

    nel_banned_text($post_data['comment'], $files);
    $cookie_name = $post_data['name'];

    // Cookies OM NOM NOM NOM
    setcookie('pwd-' . CONF_BOARD_DIR, $cpass, time() + 30 * 24 * 3600, '/'); // 1 month cookie expiration
    setcookie('name-' . CONF_BOARD_DIR, $cookie_name, time() + 30 * 24 * 3600, '/'); // 1 month cookie expiration
    $post_data = $plugins->plugin_hook('after-post-info-processing', TRUE, array($post_data));
    $i = 0;

    // Go ahead and put post into database
    require_once INCLUDE_PATH . 'post/database-functions.php';

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
    nel_db_insert_initial_post($time, $post_data);
    $prepared = $dbh->prepare('SELECT * FROM "' . POST_TABLE . '" WHERE "post_time" = ? LIMIT 1');
    $new_post_info = $dbh->executePreparedFetch($prepared, array($time), PDO::FETCH_ASSOC, true);
    $thread_info = array();

    if ($post_data['parent_thread'] === 0)
    {
        $thread_info['last_update'] = $time;
        $thread_info['post_count'] = 1;
        $thread_info['last_bump_time'] = $time;
        $thread_info['id'] = $new_post_info['post_number'];
        $thread_info['total_files'] = $files_count;
        nel_db_insert_new_thread($thread_info, $files_count);
        nel_create_thread_directories($thread_info['id']);
    }
    else
    {
        $thread_info['id'] = $post_data['parent_thread'];
        $prepared = $dbh->prepare('SELECT * FROM "' . THREAD_TABLE . '" WHERE "thread_id" = ? LIMIT 1');
        $current_thread = $dbh->executePreparedFetch($prepared, array($thread_info['id']), PDO::FETCH_ASSOC, true);
        $thread_info['last_update'] = $current_thread['last_update'];
        $thread_info['post_count'] = $current_thread['post_count'] + 1;
        $thread_info['last_bump_time'] = $time;
        $thread_info['total_files'] = $current_thread['total_files'] + count($files);

        if ($current_thread['post_count'] > nel_board_settings('max_bumps')|| $fgsfds['sage'])
        {
            $thread_info['last_bump_time'] = $current_thread['last_bump_time'];
        }

        nel_db_update_thread($new_post_info, $thread_info);
    }

    $prepared = $dbh->prepare('UPDATE "' . POST_TABLE . '" SET parent_thread = ? WHERE post_number = ?');
    $dbh->executePrepared($prepared, array($thread_info['id'], $new_post_info['post_number']), true);

    $fgsfds['noko_topic'] = $thread_info['id'];
    $srcpath = SRC_PATH . $thread_info['id'] . '/';
    $thumbpath = THUMB_PATH . $thread_info['id'] . '/';

    // Make thumbnails and do final file processing
    $files = nel_generate_thumbnails($files, $srcpath, $thumbpath);
    clearstatcache();

    // Add file data if applicable
    if ($spoon)
    {
        nel_db_insert_new_files($thread_info['id'], $new_post_info, $files);
    }

    // Run the archiving routine if this is a new thread or deleted/expired thread
    nel_archive()->updateAllArchiveStatus();
    nel_archive()->moveThreadsToArchive();

    // Generate response page if it doesn't exist, otherwise update
    nel_regen_threads($dataforce, true, array($thread_info['id']));
    nel_regen_index($dataforce);
    return $thread_info['id'];
}

function nel_is_post_ok($post_data, $time)
{
    $dbh = nel_database();
    // Check for flood
    // If post is a reply, also check if the thread still exists

    if ($post_data['parent_thread'] == 0) // TODO: Update this, doesn't look right
    {
        $thread_delay = $time - (nel_board_settings('thread_delay')* 1000);
        $prepared = $dbh->prepare('SELECT COUNT(*) FROM ' . POST_TABLE . ' WHERE post_time > ? AND ip_address = ?');
        $prepared->bindValue(1, $thread_delay, PDO::PARAM_STR);
        $prepared->bindValue(2, $_SERVER["REMOTE_ADDR"], PDO::PARAM_STR);
        $renzoku = $dbh->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
    }
    else
    {
        $thread_delay = $time - (nel_board_settings('reply_delay')* 1000);
        $prepared = $dbh->prepare('SELECT COUNT(*) FROM ' . POST_TABLE .
             ' WHERE parent_thread = ? AND post_time > ? AND ip_address = ?');
        $prepared->bindValue(1, $post_data['parent_thread'], PDO::PARAM_INT);
        $prepared->bindValue(2, $thread_delay, PDO::PARAM_STR);
        $prepared->bindValue(3, $_SERVER["REMOTE_ADDR"], PDO::PARAM_STR);
        $renzoku = $dbh->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
    }

    if ($renzoku > 0)
    {
        nel_derp(1, nel_stext('ERROR_1'));
    }

    $post_count = 1;

    if ($post_data['parent_thread'] != 0)
    {
        $prepared = $dbh->prepare('SELECT * FROM "' . THREAD_TABLE . '" WHERE "thread_id" = ? LIMIT 1');
        $op_post = $dbh->executePreparedFetch($prepared, array($post_data['parent_thread']), PDO::FETCH_ASSOC, true);

        if (!empty($op_post))
        {
            if ($op_post['locked'] == 1)
            {
                nel_derp(2, nel_stext('ERROR_2'));
            }

            if ($op_post['archive_status'] != 0)
            {
                nel_derp(3, nel_stext('ERROR_3'));
            }

            $post_count = $op_post['post_count'];
        }
        else
        {
            nel_derp(4, nel_stext('ERROR_4'));
        }

        if ($post_count >= nel_board_settings('max_posts'))
        {
            nel_derp(5, nel_stext('ERROR_5'));
        }
    }

    return $post_count;
}
