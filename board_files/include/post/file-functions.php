<?php

function nel_process_file_info()
{
    $files = array();
    $file_count = 1;

    foreach ($_FILES as $entry => $file)
    {
        $new_file = array();

        if (empty($file['name']))
        {
            ++ $file_count;
            continue;
        }

        nel_check_upload_errors($file, $files);
        preg_match('#[0-9]+$#', $entry, $matches);
        $file_order = $matches[0];
        $post_file_info = $_POST['new_post']['file_info']['file_' . $file_order . ''];

        // Grab/strip the file extension
        $info = pathinfo($file['name']);
        $new_file['ext'] = $info['extension'];
        $new_file['filename'] = $info['filename'];
        $new_file['fullname'] = $info['basename'];
        $new_file['dest'] = $file['tmp_name'];
        $new_file['filesize'] = $file['size'];
        $new_file = nel_check_for_existing_file($new_file, $files);
        $new_file = nel_get_filetype($new_file, $files);
        $new_file['dest'] = SRC_PATH . $file['name'] . '.tmp';
        move_uploaded_file($file['tmp_name'], $new_file['dest']);
        chmod($new_file['dest'], octdec(FILE_PERM));

        $new_file['source'] = nel_check_post_entry($post_file_info['sauce'], 'string');
        $new_file['license'] = nel_check_post_entry($post_file_info['lol_drama'], 'string');
        $new_file['alt_text'] = nel_check_post_entry($post_file_info['alt_text'], 'string');
        array_push($files, $new_file);

        if ($file_count == BS_MAX_POST_FILES)
        {
            break;
        }

        ++ $file_count;
    }

    return $files;
}

function nel_check_upload_errors($file, $files)
{
    $error_data = array('bad-filename' => $file['name'], 'files' => $files);

    if ($file['size'] > BS_MAX_FILESIZE * 1024)
    {
        nel_derp(100, nel_stext('ERROR_100'), $error_data);
    }

    if ($file['error'] === UPLOAD_ERR_INI_SIZE)
    {
        nel_derp(101, nel_stext('ERROR_101'), $error_data);
    }

    if ($file['error'] === UPLOAD_ERR_FORM_SIZE)
    {
        nel_derp(102, nel_stext('ERROR_102'), $error_data);
    }

    if ($file['error'] === UPLOAD_ERR_PARTIAL)
    {
        nel_derp(103, nel_stext('ERROR_103'), $error_data);
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE)
    {
        nel_derp(104, nel_stext('ERROR_104'), $error_data);
    }

    if ($file['error'] === UPLOAD_ERR_NO_TMP_DIR || $file['error'] === UPLOAD_ERR_CANT_WRITE)
    {
        nel_derp(105, nel_stext('ERROR_105'), $error_data);
    }

    if ($file['error'] !== UPLOAD_ERR_OK)
    {
        nel_derp(106, nel_stext('ERROR_106'), $error_data);
    }
}

function nel_check_for_existing_file($file, $files)
{
    $dbh = nel_database();
    $error_data = array('bad-filename' => $file['filename'], 'files' => $files);
    $file['md5'] = hash_file('md5', $file['dest'], FALSE);
    $file['sha1'] = hash_file('sha1', $file['dest'], FALSE);

    if(GENERATE_FILE_SHA256)
    {
        $file['sha256'] = hash_file('sha256', $file['dest'], FALSE);
    }

    nel_banned_hash($file['md5'], $files);
    $query = 'SELECT "post_ref" FROM "' . FILE_TABLE . '" WHERE "sha1" = ? LIMIT 1';
    $prepared = $dbh->prepare($query);
    $prepared->bindValue(1, $file['sha1'], PDO::PARAM_STR);
    $result = $dbh->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN, true);

    if ($result)
    {
        nel_derp(110, nel_stext('ERROR_110'), $error_data);
    }

    return $file;
}

function nel_get_filetype($file, $files)
{
    global $filetype_settings, $filetypes;
    $error_data = array('bad-filename' => $file['filename'], 'files' => $files);
    $test_ext = utf8_strtolower($file['ext']);
    $file_test = file_get_contents($file['dest'], NULL, NULL, 0, 65535);

    if (!array_key_exists($test_ext, $filetypes))
    {
        nel_derp(107, nel_stext('ERROR_107'), $error_data);
    }

    if (!$filetype_settings[$filetypes[$test_ext]['supertype']][$filetypes[$test_ext]['subtype']])
    {
        nel_derp(108, nel_stext('ERROR_108'), $error_data);
    }

    if (preg_match('#' . $filetypes[$test_ext]['id_regex'] . '#', $file_test))
    {
        $file['supertype'] = $filetypes[$test_ext]['supertype'];
        $file['subtype'] = $filetypes[$test_ext]['subtype'];
        $file['mime'] = $filetypes[$test_ext]['mime'];
    }
    else
    {
        nel_derp(109, nel_stext('ERROR_109'), $error_data);
    }

    return $file;
}

function nel_generate_thumbnails($files, $srcpath, $thumbpath)
{
    $i = 0;
    $files_count = count($files);

    while ($i < $files_count)
    {
        $files[$i]['im_x'] = null;
        $files[$i]['im_y'] = null;
        $files[$i]['pre_x'] = null;
        $files[$i]['pre_y'] = null;
        $files[$i]['thumbfile'] = null;

        if ($files[$i]['subtype'] === 'swf' || ($files[$i]['supertype'] === 'graphics' && !BS_USE_MAGICK))
        {
            $dim = getimagesize($files[$i]['dest']);
            $files[$i]['im_x'] = $dim[0];
            $files[$i]['im_y'] = $dim[1];
            $ratio = min((BS_MAX_HEIGHT / $files[$i]['im_y']), (BS_MAX_WIDTH / $files[$i]['im_x']));
            $files[$i]['pre_x'] = ($files[$i]['im_x'] > BS_MAX_WIDTH) ? intval($ratio * $files[$i]['im_x']) : $files[$i]['im_x'];
            $files[$i]['pre_y'] = ($files[$i]['im_y'] > BS_MAX_HEIGHT) ? intval($ratio * $files[$i]['im_y']) : $files[$i]['im_y'];
        }

        if (BS_USE_THUMB && $files[$i]['supertype'] === 'graphics')
        {
            exec("convert -version 2>/dev/null", $out, $rescode);
            //var_dump($out);
            //var_dump($rescode);

            if ($rescode === 0 && BS_USE_MAGICK)
            {
                $cmd_getinfo = 'identify -format "%wx%h" ' . escapeshellarg($files[$i]['dest'] . '[0]');
                exec($cmd_getinfo, $res);
                $dims = explode('x', $res[0]);
                $files[$i]['im_x'] = $dims[0];
                $files[$i]['im_y'] = $dims[1];
                $ratio = min((BS_MAX_HEIGHT / $files[$i]['im_y']), (BS_MAX_WIDTH / $files[$i]['im_x']));
                $files[$i]['pre_x'] = ($files[$i]['im_x'] > BS_MAX_WIDTH) ? intval($ratio * $files[$i]['im_x']) : $files[$i]['im_x'];
                $files[$i]['pre_y'] = ($files[$i]['im_y'] > BS_MAX_HEIGHT) ? intval($ratio * $files[$i]['im_y']) : $files[$i]['im_y'];

                if ($files[$i]['subtype'] === 'gif')
                {
                    $files[$i]['thumbfile'] = $files[$i]['filename'] . '-preview.gif';
                    $cmd_coalesce = 'convert ' . escapeshellarg($files[$i]['dest']) . ' -coalesce ' .
                         escapeshellarg($thumbpath . 'tmp' . $files[$i]['thumbfile']);
                    $cmd_resize = 'convert ' . escapeshellarg($thumbpath . 'tmp' . $files[$i]['thumbfile']) . ' -resize ' .
                         BS_MAX_WIDTH . 'x' . BS_MAX_HEIGHT . '\> -layers optimize ' .
                         escapeshellarg($thumbpath . $files[$i]['thumbfile']);
                    exec($cmd_coalesce);
                    exec($cmd_resize);
                    unlink($thumbpath . 'tmp' . $files[$i]['thumbfile']);
                    chmod($thumbpath . $files[$i]['thumbfile'], octdec(FILE_PERM));
                }
                else
                {
                    if (BS_USE_PNG_THUMB)
                    {
                        $files[$i]['thumbfile'] = $files[$i]['filename'] . '-preview.png';
                        $cmd_resize = 'convert ' . escapeshellarg($files[$i]['dest']) . ' -resize ' . BS_MAX_WIDTH . 'x' .
                             BS_MAX_HEIGHT . '\> -quality 00 -sharpen 0x0.5 ' .
                             escapeshellarg($thumbpath . $files[$i]['thumbfile']);
                    }
                    else
                    {
                        $files[$i]['thumbfile'] = $files[$i]['filename'] . '-preview.jpg';
                        $cmd_resize = 'convert ' . escapeshellarg($files[$i]['dest']) . ' -resize ' . BS_MAX_WIDTH . 'x' .
                             BS_MAX_HEIGHT . '\> -quality ' . BS_JPEG_QUALITY . ' -sharpen 0x0.5 ' .
                             escapeshellarg($thumbpath . $files[$i]['thumbfile']);
                    }

                    exec($cmd_resize);
                    chmod($thumbpath . $files[$i]['thumbfile'], octdec(FILE_PERM));
                }
            }
            else
            {
                // Test is really only for GIF support, which had a long absence
                // If your GD is somehow so old (or dumb) it can't do JPEG or PNG get a new host. Srsly.
                $gd_test = gd_info();

                switch ($files[$i]['subtype'])
                {
                    case 'jpeg':
                        $image = imagecreatefromjpeg($files[$i]['dest']);
                        break;

                    case 'gif':
                        $image = imagecreatefromgif($files[$i]['dest']);
                        break;

                    case 'png':
                        $image = imagecreatefrompng($files[$i]['dest']);
                        break;
                }

                $files[$i]['thumbnail'] = imagecreatetruecolor($files[$i]['pre_x'], $files[$i]['pre_y']);
                $files[$i]['thumbfile'] = $files[$i]['filename'] . '-preview.jpg';
                imagecopyresampled($files[$i]['thumbnail'], $image, 0, 0, 0, 0, $files[$i]['pre_x'], $files[$i]['pre_y'], $files[$i]['im_x'], $files[$i]['im_y']);

                if (BS_USE_PNG_THUMB)
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

        if (!file_exists($srcpath . $files[$i]['filename'] . $files[$i]['ext']))
        {
            rename($files[$i]['dest'], $srcpath . $files[$i]['filename'] . '.' . $files[$i]['ext']);
        }
        else
        {
            $files[$i]['filename'] = 'copy' . utf8_substr($time, -4) . '--' . $files[$i]['filename'];
            rename($files[$i]['dest'], $srcpath . $files[$i]['filename'] . '.' . $files[$i]['ext']);
        }
        ++ $i;
    }

    return $files;
}
