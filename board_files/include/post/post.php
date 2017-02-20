<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'post/file-functions.php';

function nel_process_new_post($dataforce, $plugins)
{
    global $enabled_types, $fgsfds, $plugins;
    $dbh = nel_get_db_handle();
    $new_thread_dir = '';

    // Get time
    $time = get_millisecond_time();
    $reply_delay = $time - (BS_REPLY_DELAY * 1000);

    // Check if post is ok
    $post_count = nel_is_post_ok($dataforce, $time);

    // Process FGSFDS
    if (!is_null($dataforce['fgsfds']))
    {
        if (utf8_strripos($dataforce['fgsfds'], 'noko') !== FALSE)
        {
            $fgsfds['noko'] = TRUE;
        }

        if (utf8_strripos($dataforce['fgsfds'], 'sage') !== FALSE)
        {
            $fgsfds['sage'] = TRUE;
        }

        $fgsfds = $plugins->plugin_hook('fgsfds_field', FALSE, array($fgsfds));
    }

    // Start collecting file info
    $files = nel_process_file_info();
    $there_is_no_spoon = TRUE;
    $files_count = 0;
    $poster_info = array('name' => $dataforce['name'], 'email' => $dataforce['email'], 'subject' => $dataforce['subject'], 'comment' => $dataforce['comment'], 'tripcode' => '', 'secure_tripcode' => '');

    if (!empty($files))
    {
        $files_count = count($files);
        $there_is_no_spoon = FALSE;
    }
    else
    {
        $files = array();

        if (!$poster_info['comment'])
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
    if (utf8_strlen($poster_info['comment']) > BS_MAX_COMMENT_LENGTH || utf8_strlen($poster_info['name']) > BS_MAX_NAME_LENGTH || utf8_strlen($poster_info['email']) > BS_MAX_EMAIL_LENGTH || utf8_strlen($poster_info['subject']) > BS_MAX_SUBJECT_LENGTH || utf8_strlen($dataforce['file_source']) > BS_MAX_SOURCE_LENGTH || utf8_strlen($dataforce['file_license']) > BS_MAX_LICENSE_LENGTH)
    {
        nel_derp(11, array('origin' => 'POST'));
    }

    if (isset($dataforce['pass']))
    {
        $cpass = $dataforce['pass'];
        $poster_info['pass'] = nel_password_hash($dataforce['pass'], NELLIEL_PASS_ALGORITHM);
        //$poster_info['pass'] = utf8_substr($hashed_pass, 0, 16);
    }
    else
    {
        $cpass = utf8_substr(rand(), 0, 8);
    }

    nel_banned_text($poster_info['comment'], $files);

    // Name and tripcodes
    $poster_info['modpost'] = 0;
    $cookie_name = $poster_info['name'];

    if ($poster_info['name'] !== '' && !BS_FORCE_ANONYMOUS)
    {
        nel_banned_name($poster_info['name'], $files);

        $faggotry = utf8_strpos($poster_info['name'], nel_stext('THREAD_MODPOST'));
        if ($faggotry)
        {
            $poster_info['name'] = nel_stext('FAKE_STAFF_ATTEMPT');
        }

        $faggotry = utf8_strpos($poster_info['name'], nel_stext('THREAD_ADMINPOST'));
        if ($faggotry)
        {
            $poster_info['name'] = nel_stext('FAKE_STAFF_ATTEMPT');
        }

        $faggotry = utf8_strpos($poster_info['name'], nel_stext('THREAD_JANPOST'));
        if ($faggotry)
        {
            $poster_info['name'] = nel_stext('FAKE_STAFF_ATTEMPT');
        }

        preg_match('/^([^#]*)(#(?!#))?([^#]*)(##)?(.*)$/', $poster_info['name'], $name_pieces);
        $poster_info['name'] = $name_pieces[1];

        if (!nel_session_ignored() && $name_pieces[5] !== '')
        {
            if ($name_pieces[5] === $_SESSION['settings']['staff_trip'])
            {
                if ($_SESSION['perms']['perm_post'])
                {
                    if ($_SESSION['settings']['staff_type'] === 'admin')
                    {
                        $poster_info['modpost'] = 3;
                    }
                    else if ($_SESSION['settings']['staff_type'] === 'moderator')
                    {
                        $poster_info['modpost'] = 2;
                    }
                    else if ($_SESSION['settings']['staff_type'] === 'janitor')
                    {
                        $poster_info['modpost'] = 1;
                    }
                }

                if ($_SESSION['perms']['perm_sticky'] && utf8_strripos($dataforce['fgsfds'], 'sticky') !== FALSE)
                {
                    $fgsfds['sticky'] = TRUE;
                }

                if ($poster_info['modpost'] > 0)
                {
                    break;
                }
            }
        }

        if ($name_pieces[3] !== '' && BS_ALLOW_TRIPKEYS)
        {
            $raw_trip = iconv('UTF-8', 'SHIFT_JIS//IGNORE', $name_pieces[3]);
            $cap = strtr($raw_trip, '&amp;', '&');
            $cap = strtr($cap, '&#44;', ',');
            $salt = substr($cap . 'H.', 1, 2);
            $salt = preg_replace('#[^\.-z]#', '.#', $salt);
            $salt = strtr($salt, ':;<=>?@[\\]^_`', 'ABCDEFGabcdef');
            $final_trip = substr(crypt($cap, $salt), -10);
            $poster_info['tripcode'] = iconv('SHIFT_JIS//IGNORE', 'UTF-8', $final_trip);
        }

        $poster_info = $plugins->plugin_hook('tripcode-processing', TRUE, array($poster_info, $name_pieces));

        if ($name_pieces[5] !== '' || $poster_info['modpost'] > 0)
        {
            $raw_trip = iconv('UTF-8', 'SHIFT_JIS//IGNORE', $name_pieces[5]);
            $trip = nel_hash($raw_trip, $plugins);
            $trip = base64_encode(pack("H*", $trip));
            $final_trip = substr($trip, -12);
            $poster_info['secure_tripcode'] = iconv('SHIFT_JIS//IGNORE', 'UTF-8', $final_trip);
        }

        $poster_info = $plugins->plugin_hook('secure-tripcode-processing', TRUE, array($poster_info, $name_pieces, $poster_info['modpost']));

        if ($name_pieces[1] === '' || (!empty($_SESSION) && $_SESSION['perms']['perm_post_anon']))
        {
            $poster_info['name'] = nel_stext('THREAD_NONAME');
            $poster_info['email'] = '';
        }
    }
    else
    {
        $poster_info['name'] = nel_stext('THREAD_NONAME');
        $poster_info['email'] = '';
    }

    // Cookies OM NOM NOM NOM
    setcookie('pwd-' . CONF_BOARD_DIR, $cpass, time() + 30 * 24 * 3600, '/'); // 1 month cookie expiration
    setcookie('name-' . CONF_BOARD_DIR, $cookie_name, time() + 30 * 24 * 3600, '/'); // 1 month cookie expiration
    $poster_info = $plugins->plugin_hook('after-post-info-processing', TRUE, array($poster_info));
    $i = 0;

    while ($i < $files_count)
    {
        if (file_exists($files[$i]['dest']))
        {
            $files[$i]['md5'] = md5_file($files[$i]['dest'], TRUE);
            nel_banned_md5(bin2hex($files[$i]['md5']), $files[$i]);
            $prepared = $dbh->prepare('SELECT post_ref FROM ' . FILE_TABLE . ' WHERE md5=? LIMIT 1');
            $prepared->bindParam(1, $files[$i]['md5'], PDO::PARAM_STR);
            $prepared->execute();

            if ($prepared->fetchColumn())
            {
                $prepared->closeCursor();
                nel_derp(12, array('origin' => 'POST', 'bad-filename' => $files[i]['basic_filename'] . $files[i]['ext'], 'files' => $files));
            }

            $prepared->closeCursor();
        }
        ++ $i;
    }

    // Go ahead and put post into database
    require_once INCLUDE_PATH . 'post/database-functions.php';

    if ($dataforce['response_to'] === 0)
    {
        $poster_info['op'] = 1;
    }
    else
    {
        $poster_info['op'] = 0;
    }
    nel_db_insert_initial_post($time, $poster_info);
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

    $dbh->query('UPDATE ' . POST_TABLE . ' SET parent_thread=' . $thread_info['id'] . ' WHERE post_number=' . $new_post_info['post_number']);

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
    $thread_delay = $time - (BS_THREAD_DELAY * 1000);
    $prepared = $dbh->prepare('SELECT COUNT(*) FROM ' . POST_TABLE . ' WHERE post_time > ? AND host = ?');
    $prepared->bindValue(1, $thread_delay, PDO::PARAM_STR);
    $prepared->bindValue(2, @inet_pton($_SERVER["REMOTE_ADDR"]), PDO::PARAM_STR);
    $prepared->execute();
    $renzoku = $prepared->fetchColumn();
    $prepared->closeCursor();

    if ($renzoku > 0)
    {
        nel_derp(1, array('origin' => 'POST'));
    }

    $post_count = 1;

    if ($dataforce['response_to'] !== 0)
    {
        $result = $dbh->query('SELECT * FROM ' . THREAD_TABLE . ' WHERE thread_id=' . $dataforce['response_to'] . ' LIMIT 1');

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
