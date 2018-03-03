<?php

function nel_process_file_info($board_id)
{
    $references = nel_board_references($board_id);
    $board_settings = nel_board_settings($board_id);
    $file_handler = nel_file_handler();
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

        nel_check_upload_errors($board_id, $file, $files);
        $file['name'] = $file_handler->filterFilename($file['name']);
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
        $new_file = nel_check_for_existing_file($board_id, $new_file, $files);
        $new_file = nel_get_filetype($board_id, $new_file, $files);
        $new_file['dest'] = $references['src_path'] . $file['name'] . '.tmp';
        move_uploaded_file($file['tmp_name'], $new_file['dest']);
        chmod($new_file['dest'], octdec(FILE_PERM));

        $new_file['source'] = nel_check_post_entry($post_file_info['sauce'], 'string');
        $new_file['license'] = nel_check_post_entry($post_file_info['lol_drama'], 'string');
        $new_file['alt_text'] = nel_check_post_entry($post_file_info['alt_text'], 'string');
        array_push($files, $new_file);

        if ($file_count == $board_settings['max_post_files'])
        {
            break;
        }

        ++ $file_count;
    }

    return $files;
}

function nel_check_upload_errors($board_id, $file, $files)
{
    $board_settings = nel_board_settings($board_id);
    $error_data = array('bad-filename' => $file['name'], 'files' => $files);

    if ($file['size'] > $board_settings['max_filesize'] * 1024)
    {
        nel_derp(100, nel_stext('ERROR_100'), $board_id, $error_data);
    }

    if ($file['error'] === UPLOAD_ERR_INI_SIZE)
    {
        nel_derp(101, nel_stext('ERROR_101'), $board_id, $error_data);
    }

    if ($file['error'] === UPLOAD_ERR_FORM_SIZE)
    {
        nel_derp(102, nel_stext('ERROR_102'), $board_id, $error_data);
    }

    if ($file['error'] === UPLOAD_ERR_PARTIAL)
    {
        nel_derp(103, nel_stext('ERROR_103'), $board_id, $error_data);
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE)
    {
        nel_derp(104, nel_stext('ERROR_104'), $board_id, $error_data);
    }

    if ($file['error'] === UPLOAD_ERR_NO_TMP_DIR || $file['error'] === UPLOAD_ERR_CANT_WRITE)
    {
        nel_derp(105, nel_stext('ERROR_105'), $board_id, $error_data);
    }

    if ($file['error'] !== UPLOAD_ERR_OK)
    {
        nel_derp(106, nel_stext('ERROR_106'), $board_id, $error_data);
    }
}

function nel_check_for_existing_file($board_id, $file, $files)
{
    $dbh = nel_database();
    $references = nel_board_references($board_id);
    $board_settings = nel_board_settings($board_id);
    $error_data = array('bad-filename' => $file['filename'], 'files' => $files);
    $file['md5'] = hash_file('md5', $file['dest'], true);
    $file['sha1'] = hash_file('sha1', $file['dest'], true);

    if ($board_settings['file_sha256'])
    {
        $file['sha256'] = hash_file('sha256', $file['dest'], true);
        $query = 'SELECT 1 FROM "' . $references['file_table'] . '" WHERE "sha256" = ? OR "sha1" = ? LIMIT 1';
        $prepared = $dbh->prepare($query);
        $prepared->bindValue(1, $file['sha256'], PDO::PARAM_LOB);
        $prepared->bindValue(2, $file['sha1'], PDO::PARAM_LOB);
    }
    else
    {
        $file['sha256'] = '';
        $query = 'SELECT 1 FROM "' . $references['file_table'] . '" WHERE "sha1" = ? LIMIT 1';
        $prepared = $dbh->prepare($query);
        $prepared->bindValue(1, $file['sha1'], PDO::PARAM_LOB);
    }

    $result = $dbh->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN, true);
    nel_banned_hash($file['md5'], $files);

    if ($result)
    {
        nel_derp(110, nel_stext('ERROR_110'), $board_id, $error_data);
    }

    return $file;
}

function nel_get_filetype($board_id, $file, $files)
{
    global $filetypes;
    $filetype_settings = nel_filetype_settings($board_id);
    $error_data = array('bad-filename' => $file['filename'], 'files' => $files);
    $test_ext = utf8_strtolower($file['ext']);
    $file_test = file_get_contents($file['dest'], NULL, NULL, 0, 65535);

    if (!array_key_exists($test_ext, $filetypes))
    {
        nel_derp(107, nel_stext('ERROR_107'), $board_id, $error_data);
    }

    if (!$filetype_settings[$filetypes[$test_ext]['type']][$filetypes[$test_ext]['format']])
    {
        nel_derp(108, nel_stext('ERROR_108'), $board_id, $error_data);
    }

    if (preg_match('#' . $filetypes[$test_ext]['id_regex'] . '#', $file_test))
    {
        $file['type'] = $filetypes[$test_ext]['type'];
        $file['format'] = $filetypes[$test_ext]['format'];
        $file['mime'] = $filetypes[$test_ext]['mime'];
    }
    else
    {
        nel_derp(109, nel_stext('ERROR_109'), $board_id, $error_data);
    }

    return $file;
}

function nel_generate_thumbnails($board_id, $files, $srcpath, $thumbpath)
{
    $i = 0;
    $files_count = count($files);
    $board_settings = nel_board_settings($board_id);

    while ($i < $files_count)
    {
        $files[$i]['im_x'] = null;
        $files[$i]['im_y'] = null;
        $files[$i]['pre_x'] = null;
        $files[$i]['pre_y'] = null;
        $files[$i]['thumbfile'] = null;

        if ($files[$i]['format'] === 'swf' || ($files[$i]['type'] === 'graphics' && !$board_settings['use_magick']))
        {
            $dim = getimagesize($files[$i]['dest']);
            $files[$i]['im_x'] = $dim[0];
            $files[$i]['im_y'] = $dim[1];
            $ratio = min(($board_settings['max_height'] / $files[$i]['im_y']), ($board_settings['max_width'] /
                 $files[$i]['im_x']));
            $files[$i]['pre_x'] = ($files[$i]['im_x'] > $board_settings['max_width']) ? intval($ratio *
                 $files[$i]['im_x']) : $files[$i]['im_x'];
            $files[$i]['pre_y'] = ($files[$i]['im_y'] > $board_settings['max_height']) ? intval($ratio *
                 $files[$i]['im_y']) : $files[$i]['im_y'];
        }

        if ($board_settings['use_thumb'] && $files[$i]['type'] === 'graphics')
        {
            if ($board_settings['use_magick'])
            {
                if (extension_loaded('imagick'))
                {
                    nel_create_imagick_preview($files[$i], $thumbpath, $board_id);
                }
                else if (function_exists('exec'))
                {
                    exec("convert -version 2>/dev/null", $out, $rescode);

                    if ($rescode === 0)
                    {
                        nel_create_imagemagick_preview($files[$i], $thumbpath, $board_id);
                    }
                }
            }
            else
            {
                $files[$i]['thumbnail'] = nel_create_gd_preview($files[$i]);
                $files[$i]['thumbfile'] = $files[$i]['filename'] . '-preview.jpg';

                if ($files[$i]['thumbnail'] === false)
                {
                    $files[$i]['pre_x'] = null;
                    $files[$i]['pre_y'] = null;
                }
                else
                {
                    if ($board_settings['use_png_thumb'])
                    {
                        imagepng($files[$i]['thumbnail'], $thumbpath . $files[$i]['thumbfile'], -1);
                    }
                    else
                    {
                        imagejpeg($files[$i]['thumbnail'], $thumbpath . $files[$i]['thumbfile'], $board_settings['jpeg_quality']);
                    }
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

function nel_create_imagick_preview(&$file, $thumbpath, $board_id)
{
    $image = new Imagick($file['dest']);
    $iterations = $image->getImageIterations();
    $image = $image->coalesceimages();
    $board_settings = nel_board_settings($board_id);
    $file['im_x'] = $image->getImageWidth();
    $file['im_y'] = $image->getImageHeight();
    $ratio = min(($board_settings['max_height'] / $file['im_y']), ($board_settings['max_width'] / $file['im_x']));
    $file['pre_x'] = ($file['im_x'] > $board_settings['max_width']) ? intval($ratio * $file['im_x']) : $file['im_x'];
    $file['pre_y'] = ($file['im_y'] > $board_settings['max_height']) ? intval($ratio * $file['im_y']) : $file['im_y'];

    if ($file['format'] === 'gif' && $iterations > 0 && $board_settings['animated_gif_preview'])
    {
        $file['thumbfile'] = $file['filename'] . '-preview.gif';

        if ($file['im_x'] <= $board_settings['max_width'] && $file['im_y'] <= $board_settings['max_height'])
        {
            copy($file['dest'], $thumbpath . $file['thumbfile']);
        }
        else
        {
            foreach ($image as $frame)
            {
                $frame->scaleImage($file['pre_x'], $file['pre_y'], true);
            }

            $image->writeImages($thumbpath . $file['thumbfile'], true);
        }
    }
    else
    {
        $image->thumbnailImage($file['pre_x'], $file['pre_y'], true);
        $image->sharpenImage(0, 0.5);

        if ($board_settings['use_png_thumb'])
        {
            $file['thumbfile'] = $file['filename'] . '-preview.png';
            $image->setImageFormat('png');
        }
        else
        {
            $file['thumbfile'] = $file['filename'] . '-preview.jpg';
            $image->setImageFormat('jpeg');
            $image->setImageCompressionQuality($board_settings['jpeg_quality']);
        }

        $image->writeImage($thumbpath . $file['thumbfile']);
    }
}

function nel_create_imagemagick_preview(&$file, $thumbpath, $board_id)
{
    $board_settings = nel_board_settings($board_id);
    $cmd_getinfo = 'identify -format "%wx%h" ' . escapeshellarg($file['dest'] . '[0]');
    exec($cmd_getinfo, $res);
    $dims = explode('x', $res[0]);
    $file['im_x'] = $dims[0];
    $file['im_y'] = $dims[1];
    $ratio = min(($board_settings['max_height'] / $file['im_y']), ($board_settings['max_width'] / $file['im_x']));
    $file['pre_x'] = ($file['im_x'] > $board_settings['max_width']) ? intval($ratio * $file['im_x']) : $file['im_x'];
    $file['pre_y'] = ($file['im_y'] > $board_settings['max_height']) ? intval($ratio * $file['im_y']) : $file['im_y'];

    if ($file['format'] === 'gif' && $iterations > 0 && $board_settings['animated_gif_preview'])
    {
        $file['thumbfile'] = $file['filename'] . '-preview.gif';
        $cmd_resize = 'convert ' . escapeshellarg($file['dest']) . ' -coalesce -thumbnail ' .
             $board_settings['max_width'] . 'x' . $board_settings['max_height'] . escapeshellarg($thumbpath . $file['thumbfile']);
        exec($cmd_resize);
        chmod($thumbpath . $file['thumbfile'], octdec(FILE_PERM));
    }
    else
    {
        if ($board_settings['use_png_thumb'])
        {
            $file['thumbfile'] = $file['filename'] . '-preview.png';
            $cmd_resize = 'convert ' . escapeshellarg($file['dest']) . ' -resize ' . $board_settings['max_width'] . 'x' .
                 $board_settings['max_height'] . '\> -quality 00 -sharpen 0x0.5 ' .
                 escapeshellarg($thumbpath . $file['thumbfile']);
        }
        else
        {
            $file['thumbfile'] = $file['filename'] . '-preview.jpg';
            $cmd_resize = 'convert ' . escapeshellarg($file['dest']) . ' -resize ' . $board_settings['max_width'] . 'x' .
                 $board_settings['max_height'] . '\> -quality ' . $board_settings['jpeg_quality'] . ' -sharpen 0x0.5 ' .
                 escapeshellarg($thumbpath . $file['thumbfile']);
        }

        exec($cmd_resize);
        chmod($thumbpath . $file['thumbfile'], octdec(FILE_PERM));
    }
}

function nel_create_gd_preview($file)
{
    // This shouldn't be needed, really. If your host actually doesn't have these, it sucks. Get a new one, srsly.
    $gd_test = gd_info();

    if ($file['format'] === 'jpeg' && $gd_test["JPEG Support"])
    {
        $image = imagecreatefromjpeg($file['dest']);
    }
    else if ($file['format'] === 'gif' && $gd_test["GIF Read Support"])
    {
        $image = imagecreatefromgif($file['dest']);
    }
    else if ($file['format'] === 'png' && $gd_test["PNG Support"])
    {
        $image = imagecreatefrompng($file['dest']);
    }
    else
    {
        return false;
    }

    $preview = imagecreatetruecolor($file['pre_x'], $file['pre_y']);
    imagecopyresampled($preview, $image, 0, 0, 0, 0, $file['pre_x'], $file['pre_y'], $file['im_x'], $file['im_y']);

    return $preview;
}
