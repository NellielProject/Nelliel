<?php
if (!defined (NELLIEL_VERSION))
{
    die ("NOPE.AVI");
}

function new_post($dataforce, $authorized)
{
    global $enabled_types, $fgsfds, $dbh;
    
    $new_thread_dir = '';
    
    // Get time
    $time = floor (microtime (TRUE) * 1000);
    $reply_delay = $time - (BS_REPLY_DELAY * 1000);
    
    //
    // Check if post is ok
    //
    $post_count = is_post_ok ($dataforce, $time);
    
    //
    // Process FGSFDS
    //
    if (isset ($dataforce['fgsfds']))
    {
        if (strripos ($dataforce['fgsfds'], 'noko') !== FALSE)
        {
            $fgsfds['noko'] = TRUE;
        }
        
        if (strripos ($dataforce['fgsfds'], 'sage') !== FALSE)
        {
            $fgsfds['sage'] = TRUE;
        }
    }
    
    //
    // Start collecting file info
    //
    
    $files = file_info ();
    $there_is_no_spoon = TRUE;
    
    if (!empty ($files))
    {
        $files_count = count ($files);
        $there_is_no_spoon = FALSE;
    }
    
    //
    // Start processing post data
    //
    
    // Cancer-fighting tools and lulz
    
    if (strlen (utf8_decode ($dataforce['comment'])) > BS_MAX_COMMENT_LENGTH || strlen (utf8_decode ($dataforce['name'])) > BS_MAX_NAME_LENGTH || strlen (utf8_decode ($dataforce['email'])) > BS_MAX_EMAIL_LENGTH || strlen (utf8_decode ($dataforce['subject'])) > BS_MAX_SUBJECT_LENGTH || strlen (utf8_decode ($dataforce['file_source'])) > BS_MAX_SOURCE_LENGTH || strlen (utf8_decode ($dataforce['file_license'])) > BS_MAX_LICENSE_LENGTH)
    {
        derp (11, LANG_ERROR_11, 'POST', array(), $files);
    }
    
    if (isset ($dataforce['pass']))
    {
        $cpass = $dataforce['pass'];
        $hashed_pass = asdfg ($dataforce['pass']);
        $dataforce['pass'] = substr ($hashed_pass, 0, 16);
    }
    else
    {
        $cpass = substr (rand (), 0, 8);
    }
    
    // Text plastic surgery (rorororor) - wat.
    
    if ($dataforce['name'] !== '')
    {
        banned_name ($dataforce['name'], $files);
    }
    
    $dataforce['email'] = cleanse_the_aids ($dataforce['email']);
    $dataforce['subject'] = cleanse_the_aids ($dataforce['subject']);
    
    if ($dataforce['comment'] !== '')
    {
        banned_text ($dataforce['comment'], $files);
        $dataforce['comment'] = word_filters ($dataforce['comment']);
        $dataforce['comment'] = cleanse_the_aids ($dataforce['comment']);
    }
    
    // Cookies OM NOM NOM NOM
    setcookie ('pwd-' . CONF_BOARD_DIR, $cpass, time () + 30 * 24 * 3600, '/'); // 1 month cookie expiration
    setcookie ('name-' . CONF_BOARD_DIR, $dataforce['name'], time () + 30 * 24 * 3600, '/'); // 1 month cookie expiration
                                                                                             
    // Name and tripcodes
    $modpostc = 0;
    $dataforce['tripcode'] = '';
    $dataforce['secure_tripcode'] = '';
    
    if ($dataforce['name'] !== '')
    {
        if (BS1_FORCE_ANONYMOUS)
        {
            $dataforce['name'] = LANG_THREAD_NONAME;
            $dataforce['email'] = '';
        }
        else
        {
            $faggotry = strpos ($dataforce['name'], LANG_THREAD_MODPOST);
            if ($faggotry)
            {
                $dataforce['name'] = LANG_FAKE_STAFF_ATTEMPT;
            }
            
            $faggotry = strpos ($dataforce['name'], LANG_THREAD_ADMINPOST);
            if ($faggotry)
            {
                $dataforce['name'] = LANG_FAKE_STAFF_ATTEMPT;
            }
            
            $faggotry = strpos ($dataforce['name'], LANG_THREAD_JANPOST);
            if ($faggotry)
            {
                $dataforce['name'] = LANG_FAKE_STAFF_ATTEMPT;
            }
            
            preg_match ('/^([^#]*)(#(?!#))?([^#]*)(##)?(.*)$/', $dataforce['name'], $name_pieces);
            $dataforce['name'] = cleanse_the_aids ($name_pieces[1]);
            
            if ($name_pieces[5] !== '')
            {
                $num_auth = array_keys ($authorized);
                $auth_count = count ($authorized);
                $i = 0;
                
                while ($i < $auth_count)
                {
                    if ($name_pieces[5] === $authorized[$num_auth[$i]]['staff_trip'])
                    {
                        if ($authorized[$num_auth[$i]]['perm_post'])
                        {
                            if ($authorized[$staff_id]['staff_type'] === 'admin')
                            {
                                $modpostc = 3;
                            }
                            else if ($authorized[$staff_id]['staff_type'] === 'moderator')
                            {
                                $modpostc = 2;
                            }
                            else if ($authorized[$staff_id]['staff_type'] === 'janitor')
                            {
                                $modpostc = 1;
                            }
                        }
                        
                        if ($authorized[$num_auth[$i]]['perm_sticky'] && strripos ($dataforce['fgsfds'], 'sticky') !== FALSE)
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
            
            if ($name_pieces[3] !== '' && BS1_ALLOW_TRIPKEYS)
            {
                $cap = strtr ($name_pieces[3], '&amp;', '&');
                $cap = strtr ($cap, '&#44;', ',');
                $salt = substr ($cap . 'H.', 1, 2);
                $salt = preg_replace ('#[^\.-z]#', '.#', $salt);
                $salt = strtr ($salt, ':;<=>?@[\\]^_`', 'ABCDEFGabcdef');
                $dataforce['tripcode'] = substr (crypt ($cap, $salt), -10);
            }
            
            if ($name_pieces[5] !== '' || $modpostc > 0)
            {
                $trip = asdfg ($name_pieces[5]);
                $dataforce['secure_tripcode'] = substr (crypt ($trip, '42'), -12);
            }
            
            if ($name_pieces[1] === '' || $authorized[$staff_id]['perm_post_anon'])
            {
                $dataforce['name'] = LANG_THREAD_NONAME;
                $dataforce['email'] = '';
            }
        }
    }
    else
    {
        $dataforce['name'] = LANG_THREAD_NONAME;
    }
    
    // Comment processing, mostly dealing with \n
    if ($dataforce['comment'] !== '')
    {
        // Set up comment field with proper newlines, etc
        $dataforce['comment'] = str_replace ("\r", "\n", $dataforce['comment']);
        
        if (substr_count ($dataforce['comment'], "\n") < BS_MAX_COMMENT_LINES)
        {
            $dataforce['comment'] = str_replace ("\n\n", "<br>", $dataforce['comment']);
            $dataforce['comment'] = str_replace ("\n", "<br>", $dataforce['comment']);
        }
        else
        {
            $dataforce['comment'] = str_replace ("\n", "", $dataforce['comment']); // \n is erased
        }
    }
    else
    {
        $dataforce['comment'] = LANG_THREAD_NOTEXT;
    }
    
    $i = 0;
    
    while ($i < $files_count)
    {
        if (file_exists ($files[$i]['dest']))
        {
            $files[$i]['md5'] = md5_file ($files[$i]['dest']);
            banned_md5 ($files[$i]['md5'], $files[$i]);
            $prepared = $dbh->prepare ('SELECT post_ref FROM ' . FILETABLE . ' WHERE md5=:md5 LIMIT 1');
            $prepared->bindParam (':md5', $files[$i]['md5'], PDO::PARAM_STR);
            
            if ($prepared->execute ())
            {
                $post_ref = $prepared->fetchColumn ();
                unset ($prepared);
                
                if ($dataforce['response_to'] === 0)
                {
                    $prepared = $dbh->prepare ('SELECT COUNT(*) FROM ' . POSTTABLE . ' WHERE post_number=:postref AND response_to=0');
                    $prepared->bindParam (':postref', $post_ref, PDO::PARAM_INT);
                }
                else
                {
                    $prepared = $dbh->prepare ('SELECT COUNT(*) FROM ' . POSTTABLE . ' WHERE post_number=:postref AND response_to=:respto');
                    $prepared->bindParam (':postref', $post_ref, PDO::PARAM_INT);
                    $prepared->bindParam (':respto', $dataforce['response_to'], PDO::PARAM_INT);
                }
                
                if ($prepared->execute ())
                {
                    $same_thread = $prepared->fetchColumn ();
                    
                    if ($same_thread > 0)
                    {
                        derp (12, LANG_ERROR_12, 'POST', array(), $files[$i]);
                    }
                }
                
                unset ($prepared);
            }
        }
        ++ $i;
    }
    
    //
    // Go ahead and put post into database
    //
    
    $prepared = $dbh->prepare ('INSERT INTO ' . POSTTABLE . ' 
	(name, tripcode, secure_tripcode, email, subject, comment, host, password, post_time, last_update, response_to, last_response, post_count, sticky, mod_post, mod_comment, archive_status, locked) VALUES 
	(:name, :tripcode, :secure_tripcode, :email, :subject, :comment, :host, :password, :time, :last_update, :respto, 0, 1, :sticky, :modpost, :mcomment, 0, 0)');
    
    $prepared->bindValue (':name', $dataforce['name'], PDO::PARAM_STR);
    
    if ($dataforce['tripcode'] === '')
    {
        $prepared->bindValue (':tripcode', NULL, PDO::PARAM_NULL);
    }
    else
    {
        $prepared->bindValue (':tripcode', $dataforce['tripcode'], PDO::PARAM_STR);
    }
    
    if ($dataforce['secure_tripcode'] === '')
    {
        $prepared->bindValue (':secure_tripcode', NULL, PDO::PARAM_NULL);
    }
    else
    {
        $prepared->bindValue (':secure_tripcode', $dataforce['secure_tripcode'], PDO::PARAM_STR);
    }
    
    $prepared->bindValue (':email', $dataforce['email'], PDO::PARAM_STR);
    $prepared->bindValue (':subject', $dataforce['subject'], PDO::PARAM_STR);
    $prepared->bindValue (':comment', $dataforce['comment'], PDO::PARAM_STR);
    $prepared->bindValue (':host', @inet_pton ($_SERVER["REMOTE_ADDR"]), PDO::PARAM_STR);
    $prepared->bindValue (':password', $dataforce['pass'], PDO::PARAM_STR);
    $prepared->bindValue (':time', $time, PDO::PARAM_STR);
    $prepared->bindValue (':last_update', $time, PDO::PARAM_STR);
    $prepared->bindValue (':respto', $dataforce['response_to'], PDO::PARAM_INT);
    
    if ($fgsfds['sticky'])
    {
        $prepared->bindValue (':sticky', 1, PDO::PARAM_INT);
    }
    else
    {
        $prepared->bindValue (':sticky', 0, PDO::PARAM_INT);
    }
    
    $prepared->bindValue (':modpost', $modpostc, PDO::PARAM_INT);
    $prepared->bindValue (':mcomment', NULL, PDO::PARAM_NULL);
    $prepared->execute ();
    unset ($prepared);
    
    $result = $dbh->query ('SELECT post_number FROM ' . POSTTABLE . ' WHERE post_time=' . $time . ' AND response_to=' . $dataforce['response_to'] . '');
    
    $row = $result->fetch ();
    
    $post_number = $row[0];
    unset ($result);
    
    if ($dataforce['response_to'] === 0)
    {
        $fgsfds['noko_topic'] = $post_number;
        $new_thread_dir = $post_number;
        create_thread_directories ($new_thread_dir);
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
            $dim = getimagesize ($files[$i]['dest']);
            $files[$i]['im_x'] = $dim[0];
            $files[$i]['im_y'] = $dim[1];
            $ratio = min ((BS_MAX_HEIGHT / $files[$i]['im_y']), (BS_MAX_WIDTH / $files[$i]['im_x']));
            $files[$i]['pre_x'] = ($files[$i]['im_x'] > BS_MAX_WIDTH) ? intval ($ratio * $files[$i]['im_x']) : $files[$i]['im_x'];
            $files[$i]['pre_y'] = ($files[$i]['im_y'] > BS_MAX_HEIGHT) ? intval ($ratio * $files[$i]['im_y']) : $files[$i]['im_y'];
        }
        
        if (BS1_USE_THUMB && $files[$i]['supertype'] === 'GRAPHICS')
        {
            exec ("convert -version", $out, $rescode);
            
            if ($rescode === 0 && BS1_USE_MAGICK)
            {
                $cmd_getinfo = 'identify -format "%wx%h" ' . escapeshellarg ($files[$i]['dest'] . '[0]');
                exec ($cmd_getinfo, $res);
                $dims = explode ('x', $res[0]);
                $files[$i]['im_x'] = $dims[0];
                $files[$i]['im_y'] = $dims[1];
                $ratio = min ((BS_MAX_HEIGHT / $files[$i]['im_y']), (BS_MAX_WIDTH / $files[$i]['im_x']));
                $files[$i]['pre_x'] = ($files[$i]['im_x'] > BS_MAX_WIDTH) ? intval ($ratio * $files[$i]['im_x']) : $files[$i]['im_x'];
                $files[$i]['pre_y'] = ($files[$i]['im_y'] > BS_MAX_HEIGHT) ? intval ($ratio * $files[$i]['im_y']) : $files[$i]['im_y'];
                
                if ($files[$i]['subtype'] === 'GIF')
                {
                    $files[$i]['thumbfile'] = $files[$i]['basic_filename'] . '-preview.gif';
                    $cmd_coalesce = 'convert ' . escapeshellarg ($files[$i]['dest']) . ' -coalesce ' . escapeshellarg ($thumbpath . 'tmp' . $files[$i]['thumbfile']);
                    $cmd_resize = 'convert ' . escapeshellarg ($thumbpath . 'tmp' . $files[$i]['thumbfile']) . ' -resize ' . BS_MAX_WIDTH . 'x' . BS_MAX_HEIGHT . '\> -layers optimize ' . escapeshellarg ($thumbpath . $files[$i]['thumbfile']);
                    exec ($cmd_coalesce);
                    exec ($cmd_resize);
                    unlink ($thumbpath . 'tmp' . $files[$i]['thumbfile']);
                    chmod ($thumbpath . $files[$i]['thumbfile'], 0644);
                }
                else
                {
                    if (BS1_USE_PNG_THUMB)
                    {
                        $files[$i]['thumbfile'] = $files[$i]['basic_filename'] . '-preview.png';
                        $cmd_resize = 'convert ' . escapeshellarg ($files[$i]['dest']) . ' -resize ' . BS_MAX_WIDTH . 'x' . BS_MAX_HEIGHT . '\> -quality 00 -sharpen 0x0.5 ' . escapeshellarg ($thumbpath . $files[$i]['thumbfile']);
                    }
                    else
                    {
                        $files[$i]['thumbfile'] = $files[$i]['basic_filename'] . '-preview.jpg';
                        $cmd_resize = 'convert ' . escapeshellarg ($files[$i]['dest']) . ' -resize ' . BS_MAX_WIDTH . 'x' . BS_MAX_HEIGHT . '\> -quality ' . BS_JPEG_QUALITY . ' -sharpen 0x0.5 ' . escapeshellarg ($thumbpath . $files[$i]['thumbfile']);
                    }
                    
                    exec ($cmd_resize);
                    chmod ($thumbpath . $files[$i]['thumbfile'], 0644);
                }
            }
            else
            {
                // Test is really only for GIF support, which had a long absence
                // If your GD is somehow so old (or dumb) it can't do JPEG or PNG get a new host. Srsly.
                $gd_test = gd_info ();
                
                switch ($files[$i]['subtype'])
                {
                    case 'JPEG':
                        $image = imagecreatefromjpeg ($files[$i]['dest']);
                        break;
                    
                    case 'GIF':
                        if ($gd_test['GIF Read Support'])
                        {
                            $image = imagecreatefromgif ($files[$i]['dest']);
                        }
                        break;
                    
                    case 'PNG':
                        $image = imagecreatefrompng ($files[$i]['dest']);
                        break;
                }
                
                $files[$i]['thumbnail'] = imagecreatetruecolor ($files[$i]['pre_x'], $files[$i]['pre_y']);
                $files[$i]['thumbfile'] = $files[$i]['basic_filename'] . '-preview.jpg';
                imagecopyresampled ($files[$i]['thumbnail'], $image, 0, 0, 0, 0, $files[$i]['pre_x'], $files[$i]['pre_y'], $files[$i]['im_x'], $files[$i]['im_y']);
                if (BS1_USE_PNG_THUMB)
                {
                    imagepng ($files[$i]['thumbnail'], $thumbpath . $files[$i]['thumbfile'], -1); // Quality
                }
                else
                {
                    imagejpeg ($files[$i]['thumbnail'], $thumbpath . $files[$i]['thumbfile'], BS_JPEG_QUALITY);
                }
            }
        }
        
        clearstatcache ();
        if (!file_exists ($srcpath . $files[$i]['basic_filename'] . $files[$i]['ext']))
        {
            rename ($files[$i]['dest'], $srcpath . $files[$i]['basic_filename'] . '.' . $files[$i]['ext']);
        }
        else
        {
            $files[$i]['basic_filename'] = "cc" . substr ($time, -4) . "--" . $files[$i]['basic_filename'];
            rename ($files[$i]['dest'], $srcpath . $files[$i]['basic_filename'] . '.' . $files[$i]['ext']);
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
        $dbh->query ('UPDATE ' . POSTTABLE . ' SET last_update=' . $time . ', last_response=' . $post_number . ', post_count=' . $post_count . ' WHERE post_number=' . $dataforce['response_to'] . '');
        $dbh->query ('UPDATE ' . POSTTABLE . ' SET last_update=0 WHERE post_number=' . $post_number . '');
        $parent_id = $dataforce['response_to'];
    }
    
    if (!$there_is_no_spoon)
    {
        $i = 0;
        
        while ($i < $files_count)
        {
            $dbh->query ('UPDATE ' . POSTTABLE . ' SET has_file=1 WHERE post_number=' . $post_number . '');
            $prepared = $dbh->prepare ('INSERT INTO ' . FILETABLE . ' (parent_thread,post_ref,ord,supertype,subtype,mime,filename,extension,filesize,md5,source,license)
				VALUES (' . '' . $parent_id . ',' . '' . $post_number . ',' . '"' . ($i + 1) . '",' . '"' . $files[$i]['supertype'] . '",' . '"' . $files[$i]['subtype'] . '",' . '"' . $files[$i]['mime'] . '",' . '"' . $files[$i]['basic_filename'] . '",' . '"' . $files[$i]['ext'] . '",' . '"' . $files[$i]['fsize'] . '",' . '"' . $files[$i]['md5'] . '",' . '"' . $files[$i]['file_source'] . '",' . '"' . $files[$i]['file_license'] . '")');
            
            $prepared->execute ();
            unset ($prepared);
            
            if ($files[$i]['supertype'] === 'GRAPHICS')
            {
                $dbh->query ('UPDATE ' . FILETABLE . ' SET image_width=' . $files[$i]['im_x'] . ', image_height=' . $files[$i]['im_y'] . ', preview_name="' . $files[$i]['thumbfile'] . '", preview_width=' . $files[$i]['pre_x'] . ', preview_height=' . $files[$i]['pre_y'] . ', md5="' . $files[$i]['md5'] . '" WHERE post_ref=' . $post_number . ' AND ord=' . ($i + 1) . '');
            }
            else if ($files[$i]['subtype'] === 'SWF')
            {
                $dbh->query ('UPDATE ' . FILETABLE . ' SET image_width=' . $files[$i]['im_x'] . ', image_height=' . $files[$i]['im_y'] . ', md5="' . $files[$i]['md5'] . '" WHERE post_ref=' . $post_number . ' AND ord=' . ($i + 1) . '');
            }
            
            ++ $i;
        }
    }
    
    //
    // Run the archiving routine if this is a new thread or deleted/expired thread
    //
    
    update_archive_status ($dataforce);
    
    //
    // Generate response page if it doesn't exist, otherwise update
    //
    
    if (!empty ($_SESSION))
    {
        $temp = $_SESSION['ignore_login'];
        $_SESSION['ignore_login'] = TRUE;
    }
    
    $return_res = ($dataforce['response_to'] === 0) ? $new_thread_dir : $dataforce['response_to'];
    regen ($dataforce, $return_res, 'thread', FALSE);
    cache_post_links ();
    $dataforce['archive_update'] = TRUE;
    regen ($dataforce, NULL, 'main', FALSE);
    
    if (!empty ($_SESSION))
    {
        $_SESSION['ignore_login'] = $temp;
    }
    
    return $return_res;
}

function is_post_ok($dataforce, $time)
{
    global $dbh;
    
    $thread_delay = $time - (BS_THREAD_DELAY * 1000);
    
    // Check for flood
    // If post is a reply, check if the thread still exists
    
    if ($dataforce['response_to'] === 0)
    {
        $prepared = $dbh->prepare ('SELECT COUNT(*) FROM ' . POSTTABLE . ' WHERE post_time > ' . $thread_delay . ' AND host = :host');
        $prepared->bindParam (':host', @inet_pton ($_SERVER["REMOTE_ADDR"]), PDO::PARAM_STR);
        $prepared->execute ();
        $renzoku = $prepared->fetchColumn ();
        unset ($prepared);
        
        if ($renzoku > 0)
        {
            derp (1, LANG_ERROR_1, 'POST', array(), '');
        }
        
        $post_count = 1;
    }
    else
    {
        $result = $dbh->query ('SELECT post_number,post_count,archive_status,locked FROM ' . POSTTABLE . ' WHERE post_number=' . $dataforce['response_to'] . ' LIMIT 1');
        
        if ($result !== FALSE)
        {
            $op_post = $result->fetch (PDO::FETCH_ASSOC);
            if (!empty ($op_post))
            {
                if ($op_post['post_number'] === '')
                {
                    derp (2, LANG_ERROR_2, 'POST', array(), '');
                }
                
                if ($op_post['locked'] === '1')
                {
                    derp (3, LANG_ERROR_3, 'POST', array(), '');
                }
                
                if ($op_post['archive_status'] !== '0')
                {
                    derp (14, LANG_ERROR_14, 'POST', array(), '');
                }
                
                $post_count = $op_post['post_count'];
            }
            else
            {
                $post_count = 1;
            }
        }
        
        unset ($result);
        $prepared = $dbh->prepare ('SELECT COUNT(*) FROM ' . POSTTABLE . ' WHERE post_time > ' . $thread_delay . ' AND host = :host LIMIT 1');
        $prepared->bindParam (':host', @inet_pton ($_SERVER["REMOTE_ADDR"]), PDO::PARAM_STR);
        $result = $prepared->execute ();
        $renzoku = $prepared->fetchColumn ();
        unset ($prepared);
        
        if ($renzoku > 0)
        {
            derp (1, LANG_ERROR_1, 'POST', array(), '');
        }
        
        if ($post_count >= BS_MAX_POSTS)
        {
            derp (4, LANG_ERROR_4, 'POST', array(), '');
        }
    }
    
    return $post_count;
}

function file_info()
{
    global $enabled_types;
    
    $files = array();
    $i = 0;
    $filetypes_loaded = FALSE;
    
    foreach ($_FILES as $file)
    {
        if ($file['error'] === UPLOAD_ERR_OK)
        {
            if (!empty ($file['name']))
            {
                if (!$filetypes_loaded)
                {
                    include INCLUDE_PATH . 'filetype.php';
                    $filetypes_loaded = TRUE;
                }
                
                // Grab/strip the file extension
                $files[$i]['ext'] = ltrim (strrchr ($file['name'], '.'), '.');
                $files[$i]['basic_filename'] = str_replace ('.' . $files[$i]['ext'], "", $file['name']);
                
                $max_upload = ini_get ('upload_max_filesize');
                $size_unit = strtolower (substr ($max_upload, -1, 1));
                $max_upload = strtolower (substr ($max_upload, 0, -1));
                
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
                    derp (24, LANG_ERROR_24, 'POST', array(), $files[$i]);
                }
                
                $files[$i]['dest'] = SRC_PATH . $file['name'] . '.tmp';
                move_uploaded_file ($file['tmp_name'], $files[$i]['dest']);
                chmod ($files[$i]['dest'], 0644);
                $files[$i]['fsize'] = filesize ($files[$i]['dest']);
                $test_ext = strtolower ($files[$i]['ext']);
                $file_test = file_get_contents ($files[$i]['dest'], NULL, NULL, 0, 65535);
                $file_good = FALSE;
                $file_allowed = FALSE;
                
                // Graphics
                if (array_key_exists ($test_ext, $filetypes))
                {
                    if ($enabled_types['enable_' . strtolower ($filetypes[$test_ext]['subtype'])] && $enabled_types['enable_' . strtolower ($filetypes[$test_ext]['supertype'])])
                    {
                        $file_allowed = TRUE;
                        
                        if (preg_match ('#' . $filetypes[$test_ext]['id_regex'] . '#', $file_test))
                        {
                            $files[$i]['supertype'] = $filetypes[$test_ext]['supertype'];
                            $files[$i]['subtype'] = $filetypes[$test_ext]['subtype'];
                            $files[$i]['mime'] = $filetypes[$test_ext]['mime'];
                            $file_good = TRUE;
                        }
                    }
                }
                else
                {
                    derp (6, LANG_ERROR_6, 'POST', array(), $files[$i]);
                }
                
                if (!$file_good)
                {
                    derp (23, LANG_ERROR_23, 'POST', array(), $files[$i]);
                }
                
                $files[$i]['file_source'] = cleanse_the_aids ($_POST['sauce' . ($i + 1)]);
                $files[$i]['file_license'] = cleanse_the_aids ($_POST['loldrama' . ($i + 1)]);
                ++ $i;
            }
            
            if ($files_count == BS_MAX_POST_FILES)
            {
                break;
            }
        }
        else if ($file['error'] === UPLOAD_ERR_INI_SIZE)
        {
            derp (24, LANG_ERROR_24, 'POST', array(), '');
        }
        
        if ($there_is_no_spoon)
        {
            $files = array();
            
            if (!$dataforce['comment'])
            {
                derp (10, LANG_ERROR_10, 'POST', array(), $files);
            }
            
            if (BS1_REQUIRE_IMAGE_ALWAYS)
            {
                derp (8, LANG_ERROR_8, 'POST', array(), $files);
            }
            
            if (BS1_REQUIRE_IMAGE_START && $dataforce['response_to'] === 0)
            {
                derp (9, LANG_ERROR_9, 'POST', array(), $files);
            }
        }
    }
    
    return $files;
}

?>