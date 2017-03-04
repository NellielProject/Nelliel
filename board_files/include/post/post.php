<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'post/file-functions.php';
require_once INCLUDE_PATH . 'post/post-data.php';

function nel_process_new_post($dataforce)
{
    global $enabled_types, $fgsfds, $plugins;
    $dbh = nel_get_db_handle();

    $post_data = nel_collect_post_data();
    $new_thread_dir = '';

    // Get time
    $time = get_millisecond_time();
    $reply_delay = $time - (BS_REPLY_DELAY * 1000);

    // Check if post is ok
    $post_count = nel_is_post_ok($dataforce, $time);

    // Process FGSFDS
    if (!is_null($post_data['fgsfds']))
    {
        $fgsfds['noko'] = utf8_strripos($post_data['fgsfds'], 'noko');
        $fgsfds['sage'] = utf8_strripos($post_data['fgsfds'], 'sage');
        $fgsfds = $plugins->plugin_hook('fgsfds_field', FALSE, array($fgsfds));
    }

    // Start collecting file info
    $files = nel_process_file_info();
    $there_is_no_spoon = TRUE;
    $files_count = 0;

    if (!empty($files))
    {
        $files_count = count($files);
        $there_is_no_spoon = FALSE;
    }
    else
    {
        $files = array();

        if (!$post_data['comment'])
        {
            nel_derp(10, array('origin' => 'POST'));
        }

        if (BS_REQUIRE_IMAGE_ALWAYS)
        {
            nel_derp(8, array('origin' => 'POST'));
        }

        if (BS_REQUIRE_IMAGE_START && $dataforce['response_to'] === 0)
        {
            nel_derp(9, array('origin' => 'POST'));
        }
    }

    // Cancer-fighting tools and lulz
    if (utf8_strlen($post_data['comment']) > BS_MAX_COMMENT_LENGTH)
    {
        nel_derp(11, array('origin' => 'POST'));
    }

    if (isset($dataforce['pass']))
    {
        $cpass = $dataforce['pass'];
        $post_data['pass'] = nel_password_hash($dataforce['pass'], NELLIEL_PASS_ALGORITHM);
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

    while ($i < $files_count)
    {
        if (!file_exists($files[$i]['dest']))
        {
            continue;
        }

        $files[$i]['md5'] = md5_file($files[$i]['dest'], TRUE);
        nel_banned_md5(bin2hex($files[$i]['md5']), $files[$i]);
        $prepared = $dbh->prepare('SELECT post_ref FROM ' . FILE_TABLE . ' WHERE md5=? LIMIT 1');
        $prepared->bindParam(1, $files[$i]['md5'], PDO::PARAM_STR);
        $prepared->execute();

        if ($prepared->fetchColumn())
        {
            $prepared->closeCursor();
            nel_derp(12, array('origin' => 'POST', 'bad-filename' => $files[$i]['fullname'], 'files' => $files));
        }

        $prepared->closeCursor();
        ++ $i;
    }

    // Go ahead and put post into database
    require_once INCLUDE_PATH . 'post/database-functions.php';

    if ($dataforce['response_to'] === 0)
    {
        $post_data['op'] = 1;
    }
    else
    {
        $post_data['op'] = 0;
    }
    nel_db_insert_initial_post($time, $post_data);
    $result = $dbh->query('SELECT * FROM ' . POST_TABLE . ' WHERE post_time=' . $time . ' LIMIT 1');
    $new_post_info = $result->fetch(PDO::FETCH_ASSOC);
    unset($result);

    $thread_info = array();

    if ($dataforce['response_to'] === 0)
    {
        $thread_info['last_update'] = $time;
        $thread_info['post_count'] = 1;
        $thread_info['id'] = $new_post_info['post_number'];
        nel_db_insert_new_thread($thread_info, $files_count);
        nel_create_thread_directories($thread_info['id']);
    }
    else
    {
        $thread_info['id'] = $dataforce['response_to'];
        $result = $dbh->query('SELECT * FROM ' . THREAD_TABLE . ' WHERE thread_id=' . $thread_info['id'] . ' LIMIT 1');
        $current_thread = $result->fetch(PDO::FETCH_ASSOC);
        unset($result);
        $thread_info['last_update'] = $current_thread['last_update'];
        $thread_info['post_count'] = $current_thread['post_count'] + 1;
        nel_db_update_thread($new_post_info, $thread_info);

        if (!$fgsfds['sage'] && $current_thread['post_count'] < BS_MAX_BUMPS)
        {
            $last_update = $time;
        }
    }

    $dbh->query('UPDATE ' . POST_TABLE . ' SET parent_thread=' . $thread_info['id'] . ' WHERE post_number=' .
         $new_post_info['post_number']);

    $fgsfds['noko_topic'] = $thread_info['id'];
    $srcpath = SRC_PATH . $thread_info['id'] . '/';
    $thumbpath = THUMB_PATH . $thread_info['id'] . '/';

    // Make thumbnails and do final file processing
    $files = nel_generate_thumbnails($files, $srcpath, $thumbpath);
    clearstatcache();

    // Add file data if applicable
    if (!$there_is_no_spoon)
    {
        nel_db_insert_new_files($thread_info['id'], $new_post_info, $files);
    }

    // Run the archiving routine if this is a new thread or deleted/expired thread
    nel_update_archive_status($dataforce);

    // Generate response page if it doesn't exist, otherwise update
    nel_regen($dataforce, $thread_info['id'], 'thread', FALSE);
    $dataforce['archive_update'] = TRUE;
    nel_regen($dataforce, NULL, 'main', FALSE);
    return $thread_info['id'];
}

function nel_is_post_ok($dataforce, $time)
{
    $dbh = nel_get_db_handle();
    // Check for flood
    // If post is a reply, also check if the thread still exists
    if ($dataforce['response_to'] !== 0)
    {
        $thread_delay = $time - (BS_REPLY_DELAY * 1000);
        $prepared = $dbh->prepare('SELECT COUNT(*) FROM ' . POST_TABLE . ' WHERE parent_thread = ? AND post_time > ? AND host = ?');
        $prepared->bindValue(1, $dataforce['response_to'], PDO::PARAM_INT);
        $prepared->bindValue(2, $thread_delay, PDO::PARAM_STR);
        $prepared->bindValue(3, @inet_pton($_SERVER["REMOTE_ADDR"]), PDO::PARAM_STR);
        $prepared->execute();
        $renzoku = $prepared->fetchColumn();
        $prepared->closeCursor();
    }
    else
    {
        $thread_delay = $time - (BS_THREAD_DELAY * 1000);
        $prepared = $dbh->prepare('SELECT COUNT(*) FROM ' . POST_TABLE . ' WHERE post_time > ? AND host = ?');
        $prepared->bindValue(1, $thread_delay, PDO::PARAM_STR);
        $prepared->bindValue(2, @inet_pton($_SERVER["REMOTE_ADDR"]), PDO::PARAM_STR);
        $prepared->execute();
        $renzoku = $prepared->fetchColumn();
        $prepared->closeCursor();
    }

    if ($renzoku > 0)
    {
        nel_derp(1, array('origin' => 'POST'));
    }

    $post_count = 1;

    if ($dataforce['response_to'] !== 0)
    {
        $result = $dbh->query('SELECT * FROM ' . THREAD_TABLE . ' WHERE thread_id=' . $dataforce['response_to'] .
             ' LIMIT 1');

        if ($result !== FALSE)
        {
            $op_post = $result->fetch(PDO::FETCH_ASSOC);

            if (!empty($op_post))
            {
                if ($op_post['thread_id'] === '')
                {
                    nel_derp(2, array('origin' => 'POST'));
                }

                if ($op_post['locked'] === '1')
                {
                    nel_derp(3, array('origin' => 'POST'));
                }

                if ($op_post['archive_status'] !== '0')
                {
                    nel_derp(14, array('origin' => 'POST'));
                }

                $post_count = $op_post['post_count'];
            }
        }

        unset($result);

        if ($post_count >= BS_MAX_POSTS)
        {
            nel_derp(4, array('origin' => 'POST'));
        }
    }

    return $post_count;
}
?>
