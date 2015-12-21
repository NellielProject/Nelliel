<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_process_new_post($dataforce, $dbh)
{
    global $enabled_types, $fgsfds, $plugins;
    
    $new_thread_dir = '';
    
    // Get time
    $time = floor(microtime(TRUE) * 1000);
    $reply_delay = $time - (BS_REPLY_DELAY * 1000);
    
    // Check if post is ok
    $post_count = nel_is_post_ok($dataforce, $time, $dbh);
    
    // Process FGSFDS
    if (isset($dataforce['fgsfds']))
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
    
    $poster_info = array('name' => $dataforce['name'], 'email' => $dataforce['email'],
                        'subject' => $dataforce['subject'], 'comment' => $dataforce['comment'],
                        'tripcode' => '', 'secure_tripcode' => '');
    
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
        
        if (BS1_REQUIRE_IMAGE_ALWAYS)
        {
            nel_derp(8, array('origin' => 'POST'));
        }
        
        if (BS1_REQUIRE_IMAGE_START && $dataforce['response_to'] === 0)
        {
            nel_derp(9, array('origin' => 'POST'));
        }
    }
    
    // Cancer-fighting tools and lulz
    

    if (utf8_strlen($poster_info['comment']) > BS_MAX_COMMENT_LENGTH
        || utf8_strlen($poster_info['name']) > BS_MAX_NAME_LENGTH
        || utf8_strlen($poster_info['email']) > BS_MAX_EMAIL_LENGTH
        || utf8_strlen($poster_info['subject']) > BS_MAX_SUBJECT_LENGTH
        || utf8_strlen($dataforce['file_source']) > BS_MAX_SOURCE_LENGTH
        || utf8_strlen($dataforce['file_license']) > BS_MAX_LICENSE_LENGTH)
    {
        nel_derp(11, array('origin' => 'POST'));
    }
    
    if (isset($dataforce['pass']))
    {
        $cpass = $dataforce['pass'];
        $hashed_pass = nel_hash($dataforce['pass']);
        $dataforce['pass'] = utf8_substr($hashed_pass, 0, 16);
    }
    else
    {
        $cpass = utf8_substr(rand(), 0, 8);
    }
    
    // Text plastic surgery (rorororor) - wat.
    $poster_info = $plugins->plugin_hook('before-post-info-processing', TRUE, array($poster_info));
    $poster_info = $plugins->plugin_hook('post-info-processing', TRUE, array($poster_info));
    
    $poster_info['email'] = nel_cleanse_the_aids($poster_info['email']);
    $poster_info['subject'] = nel_cleanse_the_aids($poster_info['subject']);
    
    if ($poster_info['comment'] !== '')
    {
        nel_banned_text($poster_info['comment'], $files);
        $poster_info['comment'] = nel_word_filters($poster_info['comment']);
        $poster_info['comment'] = nel_cleanse_the_aids($poster_info['comment']);
    }
    
    // Comment processing, mostly dealing with \n
    if ($poster_info['comment'] !== '')
    {
        // Set up comment field with proper newlines, etc
        $poster_info['comment'] = utf8_str_replace("\r", "\n", $poster_info['comment']);
        
        if (utf8_substr_count($dataforce['comment'], "\n") < BS_MAX_COMMENT_LINES)
        {
            $poster_info['comment'] = utf8_str_replace("\n\n", "<br>", $poster_info['comment']);
            $poster_info['comment'] = utf8_str_replace("\n", "<br>", $poster_info['comment']);
        }
        else
        {
            $poster_info['comment'] = utf8_str_replace("\n", "", $poster_info['comment']); // \n is erased
        }
    }
    else
    {
        $poster_info['comment'] = nel_stext('THREAD_NOTEXT');
    }
    
    // Name and tripcodes
    $modpostc = 0;
    $cookie_name = $poster_info['name'];
    
    if ($poster_info['name'] !== '' && !BS1_FORCE_ANONYMOUS)
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
        $poster_info['name'] = nel_cleanse_the_aids($name_pieces[1]);
        
        if ($name_pieces[5] !== '')
        {
            $full_auth = array_keys(nel_authorization(NULL, NULL, NULL, NULL));
            $auth_count = count($full_auth);
            $i = 0;
            
            while ($i < $auth_count)
            {
                if ($name_pieces[5] === nel_get_user_setting($full_auth[$i], 'staff_trip'))
                {
                    if (nel_is_authorized($full_auth[$i], 'perm_post'))
                    {
                        if (nel_get_user_setting($staff_id, 'staff_type') === 'admin')
                        {
                            $modpostc = 3;
                        }
                        else if (nel_get_user_setting($staff_id, 'staff_type') === 'moderator')
                        {
                            $modpostc = 2;
                        }
                        else if (nel_get_user_setting($staff_id, 'staff_type') === 'janitor')
                        {
                            $modpostc = 1;
                        }
                    }
                    
                    if (nel_is_authorized($full_auth[$i], 'perm_sticky') && utf8_strripos($dataforce['fgsfds'], 'sticky') !== FALSE)
                    {
                        $fgsfds['sticky'] = TRUE;
                    }
                    
                    if ($modpostc > 0)
                    {
                        break;
                    }
                }
                
                ++ $i;
            }
        }
        
        $poster_info = $plugins->plugin_hook('tripcode-processing', TRUE, array($poster_info, $name_pieces));
        $poster_info = $plugins->plugin_hook('secure-tripcode-processing', TRUE, array($poster_info, $name_pieces, $modpostc));
        
        if ($name_pieces[1] === '' || nel_is_authorized($staff_id, 'perm_post_anon'))
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
            $files[$i]['md5'] = md5_file($files[$i]['dest']);
            nel_banned_md5($files[$i]['md5'], $files[$i]);
            $prepared = $dbh->prepare('SELECT post_ref FROM ' . FILETABLE . ' WHERE md5=:md5 LIMIT 1');
            $prepared->bindParam(':md5', $files[$i]['md5'], PDO::PARAM_STR);
            
            if ($prepared->execute())
            {
                $post_ref = $prepared->fetchColumn();
                unset($prepared);
                
                if ($dataforce['response_to'] === 0)
                {
                    $prepared = $dbh->prepare('SELECT COUNT(*) FROM ' . POSTTABLE . ' WHERE post_number=:postref AND response_to=0');
                    $prepared->bindParam(':postref', $post_ref, PDO::PARAM_INT);
                }
                else
                {
                    $prepared = $dbh->prepare('SELECT COUNT(*) FROM ' . POSTTABLE . ' WHERE post_number=:postref AND response_to=:respto');
                    $prepared->bindParam(':postref', $post_ref, PDO::PARAM_INT);
                    $prepared->bindParam(':respto', $dataforce['response_to'], PDO::PARAM_INT);
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
    

    $prepared = $dbh->prepare('INSERT INTO ' . POSTTABLE . ' 
	(name, tripcode, secure_tripcode, email, subject, comment, host, password, post_time, last_update, response_to, last_response, post_count, sticky, mod_post, mod_comment, archive_status, locked) VALUES 
	(:name, :tripcode, :secure_tripcode, :email, :subject, :comment, :host, :password, :time, :last_update, :respto, 0, 1, :sticky, :modpost, :mcomment, 0, 0)');
    
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
    $prepared->bindValue(':password', $dataforce['pass'], PDO::PARAM_STR);
    $prepared->bindValue(':time', $time, PDO::PARAM_STR);
    $prepared->bindValue(':last_update', $time, PDO::PARAM_STR);
    $prepared->bindValue(':respto', $dataforce['response_to'], PDO::PARAM_INT);
    
    if ($fgsfds['sticky'])
    {
        $prepared->bindValue(':sticky', 1, PDO::PARAM_INT);
    }
    else
    {
        $prepared->bindValue(':sticky', 0, PDO::PARAM_INT);
    }
    
    $prepared->bindValue(':modpost', $modpostc, PDO::PARAM_INT);
    $prepared->bindValue(':mcomment', NULL, PDO::PARAM_NULL);
    $prepared->execute();
    unset($prepared);
    
    $result = $dbh->query('SELECT post_number FROM ' . POSTTABLE . ' WHERE post_time=' . $time . ' AND response_to=' . $dataforce['response_to'] . '');
    $row = $result->fetch();
    $post_number = $row[0];
    unset($result);
    
    if ($dataforce['response_to'] === 0)
    {
        $fgsfds['noko_topic'] = $post_number;
        $new_thread_dir = $post_number;
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
        
        if ($files[$i]['subtype'] === 'SWF' || ($files[$i]['supertype'] === 'GRAPHICS' && !BS1_USE_MAGICK))
        {
            $dim = getimagesize($files[$i]['dest']);
            $files[$i]['im_x'] = $dim[0];
            $files[$i]['im_y'] = $dim[1];
            $ratio = min((BS_MAX_HEIGHT / $files[$i]['im_y']), (BS_MAX_WIDTH / $files[$i]['im_x']));
            $files[$i]['pre_x'] = ($files[$i]['im_x'] > BS_MAX_WIDTH) ? intval($ratio * $files[$i]['im_x']) : $files[$i]['im_x'];
            $files[$i]['pre_y'] = ($files[$i]['im_y'] > BS_MAX_HEIGHT) ? intval($ratio * $files[$i]['im_y']) : $files[$i]['im_y'];
        }
        
        if (BS1_USE_THUMB && $files[$i]['supertype'] === 'GRAPHICS')
        {
            exec("convert -version", $out, $rescode);
            
            if ($rescode === 0 && BS1_USE_MAGICK)
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
                    if (BS1_USE_PNG_THUMB)
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
                if (BS1_USE_PNG_THUMB)
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
        $parent_id = $post_number;
    }
    else
    {
        $parent_id = $dataforce['response_to'];
    }
    
    if ($dataforce['response_to'] !== 0 && !$fgsfds['sage'] && $post_count < BS_MAX_BUMPS)
    {
        ++ $post_count;
        $dbh->query('UPDATE ' . POSTTABLE . ' SET last_update=' . $time . ', last_response=' . $post_number . ', post_count=' . $post_count . ' WHERE post_number=' . $dataforce['response_to'] . '');
        $dbh->query('UPDATE ' . POSTTABLE . ' SET last_update=0 WHERE post_number=' . $post_number . '');
        $parent_id = $dataforce['response_to'];
    }
    
    if (!$there_is_no_spoon)
    {
        $i = 0;
        
        while ($i < $files_count)
        {
            $dbh->query('UPDATE ' . POSTTABLE . ' SET has_file=1 WHERE post_number=' . $post_number . '');
            $prepared = $dbh->prepare('INSERT INTO ' . FILETABLE . ' (parent_thread,post_ref,file_order,supertype,subtype,mime,filename,extension,filesize,md5,source,license)
				VALUES (' . '' . $parent_id . ',' . '' . $post_number . ',' . '"' . ($i + 1) . '",' . '"' . $files[$i]['supertype'] . '",' . '"' . $files[$i]['subtype'] . '",' . '"' . $files[$i]['mime'] . '",' . '"' . $files[$i]['basic_filename'] . '",' . '"' . $files[$i]['ext'] . '",' . '"' . $files[$i]['fsize'] . '",' . '"' . $files[$i]['md5'] . '",' . '"' . $files[$i]['file_source'] . '",' . '"' . $files[$i]['file_license'] . '")');
            $prepared->execute();
            unset($prepared);
            
            if ($files[$i]['supertype'] === 'GRAPHICS')
            {
                $dbh->query('UPDATE ' . FILETABLE . ' SET image_width=' . $files[$i]['im_x'] . ', image_height=' . $files[$i]['im_y'] . ', preview_name="' . $files[$i]['thumbfile'] . '", preview_width=' . $files[$i]['pre_x'] . ', preview_height=' . $files[$i]['pre_y'] . ', md5="' . $files[$i]['md5'] . '" WHERE post_ref=' . $post_number . ' AND file_order=' . ($i + 1) . '');
            }
            else if ($files[$i]['subtype'] === 'SWF')
            {
                $dbh->query('UPDATE ' . FILETABLE . ' SET image_width=' . $files[$i]['im_x'] . ', image_height=' . $files[$i]['im_y'] . ', md5="' . $files[$i]['md5'] . '" WHERE post_ref=' . $post_number . ' AND file_order=' . ($i + 1) . '');
            }
            
            ++ $i;
        }
    }
    
    //
    // Run the archiving routine if this is a new thread or deleted/expired thread
    //
    

    nel_update_archive_status($dataforce, $dbh);
    
    //
    // Generate response page if it doesn't exist, otherwise update
    //
    

    if (!empty($_SESSION))
    {
        $temp = $_SESSION['ignore_login'];
        $_SESSION['ignore_login'] = TRUE;
    }
    
    $return_res = ($dataforce['response_to'] === 0) ? $new_thread_dir : $dataforce['response_to'];
    nel_regen($dataforce, $return_res, 'thread', FALSE, $dbh);
    // cache_post_links($post_link_reference);
    $dataforce['archive_update'] = TRUE;
    nel_regen($dataforce, NULL, 'main', FALSE, $dbh);
    
    if (!empty($_SESSION))
    {
        $_SESSION['ignore_login'] = $temp;
    }
    
    return $return_res;
}

//
// Clean up user input
//
function nel_cleanse_the_aids($string)
{
    if ($string === '' || preg_match("#^\s*$#", $string))
    {
        return '';
    }
    else
    {
        if (get_magic_quotes_gpc())
        {
            $string = stripslashes($string);
        }
        
        $string = trim($string);
        $string = htmlspecialchars($string);
        return $string;
    }
}

function nel_is_post_ok($dataforce, $time, $dbh)
{
    $thread_delay = $time - (BS_THREAD_DELAY * 1000);
    
    // Check for flood
    // If post is a reply, check if the thread still exists
    

    if ($dataforce['response_to'] === 0)
    {
        $prepared = $dbh->prepare('SELECT COUNT(*) FROM ' . POSTTABLE . ' WHERE post_time > ' . $thread_delay . ' AND host = :host');
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
        $result = $dbh->query('SELECT post_number,post_count,archive_status,locked FROM ' . POSTTABLE . ' WHERE post_number=' . $dataforce['response_to'] . ' LIMIT 1');
        
        if ($result !== FALSE)
        {
            $op_post = $result->fetch(PDO::FETCH_ASSOC);
            if (!empty($op_post))
            {
                if ($op_post['post_number'] === '')
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
        $prepared = $dbh->prepare('SELECT COUNT(*) FROM ' . POSTTABLE . ' WHERE post_time > ' . $thread_delay . ' AND host = :host LIMIT 1');
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

function nel_process_file_info()
{
    global $enabled_types;
    
    $files = array();
    $i = 0;
    $filetypes_loaded = FALSE;
    
    foreach ($_FILES as $file)
    {
        if ($file['error'] === UPLOAD_ERR_OK)
        {
            if (!empty($file['name']))
            {
                if (!$filetypes_loaded)
                {
                    include INCLUDE_PATH . 'filetype.php';
                    $filetypes_loaded = TRUE;
                }
                
                // Grab/strip the file extension
                $files[$i]['ext'] = ltrim(strrchr($file['name'], '.'), '.');
                $files[$i]['basic_filename'] = utf8_str_replace('.' . $files[$i]['ext'], "", $file['name']);
                
                $max_upload = ini_get('upload_max_filesize');
                $size_unit = utf8_strtolower(utf8_substr($max_upload, -1, 1));
                $max_upload = utf8_strtolower(utf8_substr($max_upload, 0, -1));
                
                if ($size_unit === 'g')
                {
                    $max_upload = $max_upload * 1024 * 1024 * 1024;
                }
                else if ($size_unit === 'm')
                {
                    $max_upload = $max_upload * 1024 * 1024;
                }
                else if ($size_unit === 'k')
                {
                    $max_upload = $max_upload * 1024;
                }
                else
                {
                    ; // Already in bytes
                }
                
                if ($file['size'] > BS_MAX_FILESIZE * 1024)
                {
                    nel_derp(19, array('origin' => 'POST', 'bad-filename' => $files[i]['basic_filename'] . $files[i]['ext'], 'files' => array($files[$i])));
                }
                
                $files[$i]['dest'] = SRC_PATH . $file['name'] . '.tmp';
                move_uploaded_file($file['tmp_name'], $files[$i]['dest']);
                chmod($files[$i]['dest'], 0644);
                $files[$i]['fsize'] = filesize($files[$i]['dest']);
                $test_ext = utf8_strtolower($files[$i]['ext']);
                $file_test = file_get_contents($files[$i]['dest'], NULL, NULL, 0, 65535);
                $file_good = FALSE;
                $file_allowed = FALSE;
                
                // Graphics
                if (array_key_exists($test_ext, $filetypes))
                {
                    if ($enabled_types['enable_' . utf8_strtolower($filetypes[$test_ext]['subtype'])] && $enabled_types['enable_' . utf8_strtolower($filetypes[$test_ext]['supertype'])])
                    {
                        $file_allowed = TRUE;
                        
                        if (preg_match('#' . $filetypes[$test_ext]['id_regex'] . '#', $file_test))
                        {
                            $files[$i]['supertype'] = $filetypes[$test_ext]['supertype'];
                            $files[$i]['subtype'] = $filetypes[$test_ext]['subtype'];
                            $files[$i]['mime'] = $filetypes[$test_ext]['mime'];
                            $file_good = TRUE;
                        }
                    }
                }
                
                if (!$file_allowed)
                {
                    nel_derp(6, array('origin' => 'POST', 'bad-filename' => $files[i]['basic_filename'] . $files[i]['ext'], 'files' => array($files[$i])));
                }
                
                if (!$file_good)
                {
                    nel_derp(18, array('origin' => 'POST', 'bad-filename' => $files[i]['basic_filename'] . $files[i]['ext'], 'files' => array($files[$i])));
                }
                
                $files[$i]['file_source'] = nel_cleanse_the_aids($_POST['sauce' . ($i + 1)]);
                $files[$i]['file_license'] = nel_cleanse_the_aids($_POST['loldrama' . ($i + 1)]);
                ++ $i;
            }
            
            if ($files_count == BS_MAX_POST_FILES)
            {
                break;
            }
        }
        else if ($file['error'] === UPLOAD_ERR_INI_SIZE)
        {
            nel_derp(19, array('origin' => 'POST', 'bad-filename' => $files[i]['basic_filename'] . $files[i]['ext'], 'files' => array($files[$i])));
        }
    }
    
    return $files;
}

?>