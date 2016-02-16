<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'post/file-functions.php';
require_once INCLUDE_PATH . 'post/database-functions.php';

function nel_process_new_post($dataforce, $plugins, $dbh)
{
    global $enabled_types, $fgsfds, $plugins;
    
    $new_thread_dir = '';
    
    // Get time
    $time = get_millisecond_time();
    $reply_delay = $time - (BS_REPLY_DELAY * 1000);
    
    // Check if post is ok
    $post_count = nel_is_post_ok($dataforce, $time, $dbh);
    
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
        
        if (BS_BOOL_REQUIRE_IMAGE_ALWAYS)
        {
            nel_derp(8, array('origin' => 'POST'));
        }
        
        if (BS_BOOL_REQUIRE_IMAGE_START && $dataforce['response_to'] === 0)
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
        $hashed_pass = nel_hash($dataforce['pass'], $plugins);
        $poster_info['pass'] = utf8_substr($hashed_pass, 0, 16);
    }
    else
    {
        $cpass = utf8_substr(rand(), 0, 8);
    }
    
    nel_banned_text($poster_info['comment'], $files);
    
    // Name and tripcodes
    $poster_info['modpost'] = 0;
    $cookie_name = $poster_info['name'];
    
    if ($poster_info['name'] !== '' && !BS_BOOL_FORCE_ANONYMOUS)
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
        
        if ($name_pieces[3] !== '' && BS_BOOL_ALLOW_TRIPKEYS)
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
            $trip = base64_encode(pack("H*",$trip));
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
            $prepared = $dbh->prepare('SELECT post_ref FROM ' . FILE_TABLE . ' WHERE md5=:md5 LIMIT 1');
            $prepared->bindParam(':md5', $files[$i]['md5'], PDO::PARAM_STR);
            
            if ($prepared->execute())
            {
                $post_ref = $prepared->fetchColumn();
                unset($prepared);
                
                if ($dataforce['response_to'] === 0)
                {
                    $prepared = $dbh->prepare('SELECT COUNT(*) FROM ' . POST_TABLE . ' WHERE post_number=:postref');
                    $prepared->bindParam(':postref', $post_ref, PDO::PARAM_INT);
                }
                else
                {
                    $prepared = $dbh->prepare('SELECT COUNT(*) FROM ' . POST_TABLE . ' WHERE post_number=:postref');
                    $prepared->bindParam(':postref', $post_ref, PDO::PARAM_INT);
                }
                
                if ($prepared->execute())
                {
                    $same_thread = $prepared->fetchColumn();
                    
                    if ($same_thread > 0)
                    {
                        nel_derp(12, array('origin' => 'POST', 'bad-filename' => $files[i]['basic_filename'] . $files[i]['ext'], 'files' => $files));
                    }
                }
                
                unset($prepared);
            }
        }
        ++ $i;
    }
    
    //
    // Go ahead and put post into database
    //

    if($dataforce['response_to'] === 0)
    {
        $poster_info['op'] = 1;
    }
    else
    {
        $poster_info['op'] = 0;
    }
    nel_db_insert_initial_post($time, $poster_info, $dbh);
    $result = $dbh->query('SELECT * FROM ' . POST_TABLE . ' WHERE post_time=' . $time . ' LIMIT 1');
    $new_post_info = $result->fetch(PDO::FETCH_ASSOC);
    $post_count = 1;
    unset($result);

    if($dataforce['response_to'] === 0)
    {
        $dbh->query('UPDATE ' . POST_TABLE .' SET parent_thread=' . $new_post_info['post_number'] . ' WHERE post_time=' . $time);
        nel_db_insert_new_thread($time, $new_post_info, $files_count, $dbh);
    }
    else
    {
        $dbh->query('UPDATE ' . POST_TABLE .' SET parent_thread=' . $dataforce['response_to'] . ' WHERE post_time=' . $time);
        $result = $dbh->query('SELECT post_count FROM ' . THREAD_TABLE . ' WHERE thread_id=' . $dataforce['response_to'] . ' LIMIT 1');
        $dd = $result->fetch(PDO::FETCH_ASSOC);
        $post_count = $dd['post_count'];
        unset($result);
    }

    if ($dataforce['response_to'] === 0)
    {
        $fgsfds['noko_topic'] = $new_post_info['post_number'];
        $new_thread_dir = $new_post_info['post_number'];
        nel_create_thread_directories($new_thread_dir);
    }
    else
    {
        $fgsfds['noko_topic'] = $dataforce['response_to'];
        $new_thread_dir = $dataforce['response_to'];
    }
    
    $srcpath = SRC_PATH . $new_thread_dir . '/';
    $thumbpath = THUMB_PATH . $new_thread_dir . '/';
    
    //
    // Make thumbnails and do final file processing
    //
    

    $i = 0;
    while ($i < $files_count)
    {
        $files[$i]['im_x'] = 0;
        $files[$i]['im_y'] = 0;
        $files[$i]['pre_x'] = 0;
        $files[$i]['pre_y'] = 0;
        
        if ($files[$i]['subtype'] === 'SWF' || ($files[$i]['supertype'] === 'GRAPHICS' && !BS_BOOL_USE_MAGICK))
        {
            $dim = getimagesize($files[$i]['dest']);
            $files[$i]['im_x'] = $dim[0];
            $files[$i]['im_y'] = $dim[1];
            $ratio = min((BS_MAX_HEIGHT / $files[$i]['im_y']), (BS_MAX_WIDTH / $files[$i]['im_x']));
            $files[$i]['pre_x'] = ($files[$i]['im_x'] > BS_MAX_WIDTH) ? intval($ratio * $files[$i]['im_x']) : $files[$i]['im_x'];
            $files[$i]['pre_y'] = ($files[$i]['im_y'] > BS_MAX_HEIGHT) ? intval($ratio * $files[$i]['im_y']) : $files[$i]['im_y'];
        }
        
        if (BS_BOOL_USE_THUMB && $files[$i]['supertype'] === 'GRAPHICS')
        {
            exec("convert -version", $out, $rescode);
            
            if ($rescode === 0 && BS_BOOL_USE_MAGICK)
            {
                $cmd_getinfo = 'identify -format "%wx%h" ' . escapeshellarg($files[$i]['dest'] . '[0]');
                exec($cmd_getinfo, $res);
                $dims = explode('x', $res[0]);
                $files[$i]['im_x'] = $dims[0];
                $files[$i]['im_y'] = $dims[1];
                $ratio = min((BS_MAX_HEIGHT / $files[$i]['im_y']), (BS_MAX_WIDTH / $files[$i]['im_x']));
                $files[$i]['pre_x'] = ($files[$i]['im_x'] > BS_MAX_WIDTH) ? intval($ratio * $files[$i]['im_x']) : $files[$i]['im_x'];
                $files[$i]['pre_y'] = ($files[$i]['im_y'] > BS_MAX_HEIGHT) ? intval($ratio * $files[$i]['im_y']) : $files[$i]['im_y'];
                
                if ($files[$i]['subtype'] === 'GIF')
                {
                    $files[$i]['thumbfile'] = $files[$i]['basic_filename'] . '-preview.gif';
                    $cmd_coalesce = 'convert ' . escapeshellarg($files[$i]['dest']) . ' -coalesce ' . escapeshellarg($thumbpath . 'tmp' . $files[$i]['thumbfile']);
                    $cmd_resize = 'convert ' . escapeshellarg($thumbpath . 'tmp' . $files[$i]['thumbfile']) . ' -resize ' . BS_MAX_WIDTH . 'x' . BS_MAX_HEIGHT . '\> -layers optimize ' . escapeshellarg($thumbpath . $files[$i]['thumbfile']);
                    exec($cmd_coalesce);
                    exec($cmd_resize);
                    unlink($thumbpath . 'tmp' . $files[$i]['thumbfile']);
                    chmod($thumbpath . $files[$i]['thumbfile'], 0644);
                }
                else
                {
                    if (BS_BOOL_USE_PNG_THUMB)
                    {
                        $files[$i]['thumbfile'] = $files[$i]['basic_filename'] . '-preview.png';
                        $cmd_resize = 'convert ' . escapeshellarg($files[$i]['dest']) . ' -resize ' . BS_MAX_WIDTH . 'x' . BS_MAX_HEIGHT . '\> -quality 00 -sharpen 0x0.5 ' . escapeshellarg($thumbpath . $files[$i]['thumbfile']);
                    }
                    else
                    {
                        $files[$i]['thumbfile'] = $files[$i]['basic_filename'] . '-preview.jpg';
                        $cmd_resize = 'convert ' . escapeshellarg($files[$i]['dest']) . ' -resize ' . BS_MAX_WIDTH . 'x' . BS_MAX_HEIGHT . '\> -quality ' . BS_JPEG_QUALITY . ' -sharpen 0x0.5 ' . escapeshellarg($thumbpath . $files[$i]['thumbfile']);
                    }
                    
                    exec($cmd_resize);
                    chmod($thumbpath . $files[$i]['thumbfile'], 0644);
                }
            }
            else
            {
                // Test is really only for GIF support, which had a long absence
                // If your GD is somehow so old (or dumb) it can't do JPEG or PNG get a new host. Srsly.
                $gd_test = gd_info();
                
                switch ($files[$i]['subtype'])
                {
                    case 'JPEG':
                        $image = imagecreatefromjpeg($files[$i]['dest']);
                        break;
                    
                    case 'GIF':
                        if ($gd_test['GIF Read Support'])
                        {
                            $image = imagecreatefromgif($files[$i]['dest']);
                        }
                        break;
                    
                    case 'PNG':
                        $image = imagecreatefrompng($files[$i]['dest']);
                        break;
                }
                
                $files[$i]['thumbnail'] = imagecreatetruecolor($files[$i]['pre_x'], $files[$i]['pre_y']);
                $files[$i]['thumbfile'] = $files[$i]['basic_filename'] . '-preview.jpg';
                imagecopyresampled($files[$i]['thumbnail'], $image, 0, 0, 0, 0, $files[$i]['pre_x'], $files[$i]['pre_y'], $files[$i]['im_x'], $files[$i]['im_y']);
                if (BS_BOOL_USE_PNG_THUMB)
                {
                    imagepng($files[$i]['thumbnail'], $thumbpath . $files[$i]['thumbfile'], -1); // Quality
                }
                else
                {
                    imagejpeg($files[$i]['thumbnail'], $thumbpath . $files[$i]['thumbfile'], BS_JPEG_QUALITY);
                }
            }
        }
        
        clearstatcache();
        if (!file_exists($srcpath . $files[$i]['basic_filename'] . $files[$i]['ext']))
        {
            rename($files[$i]['dest'], $srcpath . $files[$i]['basic_filename'] . '.' . $files[$i]['ext']);
        }
        else
        {
            $files[$i]['basic_filename'] = "cc" . utf8_substr($time, -4) . "--" . $files[$i]['basic_filename'];
            rename($files[$i]['dest'], $srcpath . $files[$i]['basic_filename'] . '.' . $files[$i]['ext']);
        }
        ++ $i;
    }
    
    //
    // Update post info and add file data if applicable
    //
    

    if ($dataforce['response_to'] === 0)
    {
        $parent_id = $new_post_info['post_number'];
    }
    else
    {
        $parent_id = $dataforce['response_to'];
    }
    
    if ($dataforce['response_to'] !== 0)
    {
        $post_count = $post_count + 1;
        $dbh->query('UPDATE ' . THREAD_TABLE . ' SET 
        last_post=' . $new_post_info['post_number'] . ',
        post_count=' . $post_count . ' 
        WHERE thread_id=' . $dataforce['response_to'] . '');
        
        if(!$fgsfds['sage'] && $post_count < BS_MAX_BUMPS)
        {
            $dbh->query('UPDATE ' . THREAD_TABLE . ' SET last_update=' . $time .' WHERE thread_id=' . $dataforce['response_to']);
        }
    }
    
    if (!$there_is_no_spoon)
    {
        $i = 0;

        while ($i < $files_count)
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
                filesize,
                source,
                license)
            VALUES (' . '
                ' . $parent_id . ',
                ' . '' . $new_post_info['post_number'] . ',
                ' . '"' . ($i + 1) . '",
                ' . '"' . $files[$i]['supertype'] . '",
                ' . '"' . $files[$i]['subtype'] . '",
                ' . '"' . $files[$i]['mime'] . '",
                ' . '"' . $files[$i]['basic_filename'] . '",
                ' . '"' . $files[$i]['ext'] . '",
                ' . '"' . $files[$i]['fsize'] . '",
                ' . '"' . $files[$i]['file_source'] . '",
                ' . '"' . $files[$i]['file_license'] . '")');
            $prepared->execute();
            unset($prepared);
            
            $dbh->query('UPDATE ' . FILE_TABLE . ' SET md5="' . $files[$i]['md5'] . '" WHERE post_ref=' . $new_post_info['post_number'] . '');
            
            if ($files[$i]['supertype'] === 'GRAPHICS')
            {
                $dbh->query('UPDATE ' . FILE_TABLE . ' SET 
                    image_width=' . $files[$i]['im_x'] . ',
                    image_height=' . $files[$i]['im_y'] . ',
                    preview_name="' . $files[$i]['thumbfile'] . '",
                    preview_width=' . $files[$i]['pre_x'] . ',
                    preview_height=' . $files[$i]['pre_y'] . ' 
                WHERE post_ref=' . $new_post_info['post_number'] . ' AND file_order=' . ($i + 1) . '');
            }
            else if ($files[$i]['subtype'] === 'SWF')
            {
                $dbh->query('UPDATE ' . FILE_TABLE . ' SET 
                    image_width=' . $files[$i]['im_x'] . ',
                    image_height=' . $files[$i]['im_y'] . ' 
                WHERE post_ref=' . $new_post_info['post_number'] . ' AND file_order=' . ($i + 1) . '');
            }
            
            ++ $i;
        }
    }
    
    //
    // Run the archiving routine if this is a new thread or deleted/expired thread
    //
    unset($result);
    unset($prepared);

    nel_update_archive_status($dataforce, $dbh);
    
    //
    // Generate response page if it doesn't exist, otherwise update
    //

    $return_res = ($dataforce['response_to'] === 0) ? $new_thread_dir : $dataforce['response_to'];
    nel_regen($dataforce, $return_res, 'thread', FALSE, $dbh);
    $dataforce['archive_update'] = TRUE;
    nel_regen($dataforce, NULL, 'main', FALSE, $dbh);
    
    return $return_res;
}

function nel_is_post_ok($dataforce, $time, $dbh)
{
    $thread_delay = $time - (BS_THREAD_DELAY * 1000);

    // Check for flood
    // If post is a reply, check if the thread still exists


    if ($dataforce['response_to'] === 0)
    {
        $prepared = $dbh->prepare('SELECT COUNT(*) FROM ' . POST_TABLE . ' WHERE post_time > ' . $thread_delay . ' AND host = :host');
        $prepared->bindParam(':host', @inet_pton($_SERVER["REMOTE_ADDR"]), PDO::PARAM_STR);
        $prepared->execute();
        $renzoku = $prepared->fetchColumn();
        unset($prepared);

        if ($renzoku > 0)
        {
            nel_derp(1, array('origin' => 'POST'));
        }

        $post_count = 1;
    }
    else
    {
        $result = $dbh->query('SELECT thread_id,post_count,archive_status,locked FROM ' . THREAD_TABLE . ' WHERE thread_id=' . $dataforce['response_to'] . ' LIMIT 1');

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
            else
            {
                $post_count = 1;
            }
        }

        unset($result);
        $prepared = $dbh->prepare('SELECT COUNT(*) FROM ' . POST_TABLE . ' WHERE post_time > ' . $thread_delay . ' AND host = :host LIMIT 1');
        $prepared->bindParam(':host', @inet_pton($_SERVER["REMOTE_ADDR"]), PDO::PARAM_STR);
        $result = $prepared->execute();
        $renzoku = $prepared->fetchColumn();
        unset($prepared);

        if ($renzoku > 0)
        {
            nel_derp(1, array('origin' => 'POST'));
        }

        if ($post_count >= BS_MAX_POSTS)
        {
            nel_derp(4, array('origin' => 'POST'));
        }
    }

    return $post_count;
}
?>