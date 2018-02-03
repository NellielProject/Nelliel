<?php

function nel_process_file_info($board_id)
{
    $references = nel_board_references($board_id);
    $board_settings = nel_board_settings($board_id);
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

function nel_check_for_existing_file($board_id, $file, $files)
{
    $dbh = nel_database();
    $references = nel_board_references($board_id);
    $error_data = array('bad-filename' => $file['filename'], 'files' => $files);
    $file['md5'] = hash_file('md5', $file['dest'], true);
    $file['sha1'] = hash_file('sha1', $file['dest'], true);

    if (GENERATE_FILE_SHA256)
    {
        $file['sha256'] = hash_file('sha256', $file['dest'], true);
        $query = 'SELECT "post_ref" FROM "' . $references['file_table'] . '" WHERE "sha256" = ? OR "sha1" = ? LIMIT 1';
        $prepared = $dbh->prepare($query);
        $prepared->bindValue(1, $file['sha256'], PDO::PARAM_LOB);
        $prepared->bindValue(2, $file['sha1'], PDO::PARAM_LOB);
    }
    else
    {
        $query = 'SELECT "post_ref" FROM "' . $references['file_table'] . '" WHERE "sha1" = ? LIMIT 1';
        $prepared = $dbh->prepare($query);
        $prepared->bindValue(1, $file['sha1'], PDO::PARAM_LOB);
    }

    $result = $dbh->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN, true);
    nel_banned_hash($file['md5'], $files);

    if ($result)
    {
        nel_derp(110, nel_stext('ERROR_110'), $error_data);
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

        if ($files[$i]['subtype'] === 'swf' || ($files[$i]['supertype'] === 'graphics' && !$board_settings['use_magick']))
        {
            $dim = getimagesize($files[$i]['dest']);
            $files[$i]['im_x'] = $dim[0];
            $files[$i]['im_y'] = $dim[1];
            $ratio = min(($board_settings['max_height'] / $files[$i]['im_y']), ($board_settings['max_width'] /
                 $files[$i]['im_x']));
            $files[$i]['pre_x'] = ($files[$i]['im_x'] > $board_settings['max_width']) ? intval($ratio * $files[$i]['im_x']) : $files[$i]['im_x'];
            $files[$i]['pre_y'] = ($files[$i]['im_y'] > $board_settings['max_height']) ? intval($ratio * $files[$i]['im_y']) : $files[$i]['im_y'];
        }

        if ($board_settings['use_thumb'] && $files[$i]['supertype'] === 'graphics')
        {
            exec("convert -version 2>/dev/null", $out, $rescode);
            //var_dump($out);
            //var_dump($rescode);

            if ($rescode === 0 && $board_settings['use_magick'])
            {
                $cmd_getinfo = 'identify -format "%wx%h" ' . escapeshellarg($files[$i]['dest'] . '[0]');
                exec($cmd_getinfo, $res);
                $dims = explode('x', $res[0]);
                $files[$i]['im_x'] = $dims[0];
                $files[$i]['im_y'] = $dims[1];
                $ratio = min(($board_settings['max_height'] / $files[$i]['im_y']), ($board_settings['max_width'] /
                     $files[$i]['im_x']));
                $files[$i]['pre_x'] = ($files[$i]['im_x'] > $board_settings['max_width']) ? intval($ratio *
                     $files[$i]['im_x']) : $files[$i]['im_x'];
                     $files[$i]['pre_y'] = ($files[$i]['im_y'] > $board_settings['max_height']) ? intval($ratio *
                     $files[$i]['im_y']) : $files[$i]['im_y'];

                if ($files[$i]['subtype'] === 'gif')
                {
                    $files[$i]['thumbfile'] = $files[$i]['filename'] . '-preview.gif';
                    $cmd_coalesce = 'convert ' . escapeshellarg($files[$i]['dest']) . ' -coalesce ' .
                         escapeshellarg($thumbpath . 'tmp' . $files[$i]['thumbfile']);
                    $cmd_resize = 'convert ' . escapeshellarg($thumbpath . 'tmp' . $files[$i]['thumbfile']) . ' -resize ' .
                    $board_settings['max_width'] . 'x' . $board_settings['max_height'] . '\> -layers optimize ' .
                         escapeshellarg($thumbpath . $files[$i]['thumbfile']);
                    exec($cmd_coalesce);
                    exec($cmd_resize);
                    unlink($thumbpath . 'tmp' . $files[$i]['thumbfile']);
                    chmod($thumbpath . $files[$i]['thumbfile'], octdec(FILE_PERM));
                }
                else
                {
                    if ($board_settings['use_png_thumb'])
                    {
                        $files[$i]['thumbfile'] = $files[$i]['filename'] . '-preview.png';
                        $cmd_resize = 'convert ' . escapeshellarg($files[$i]['dest']) . ' -resize ' .
                        $board_settings['max_width'] . 'x' . $board_settings['max_height'] .
                             '\> -quality 00 -sharpen 0x0.5 ' . escapeshellarg($thumbpath . $files[$i]['thumbfile']);
                    }
                    else
                    {
                        $files[$i]['thumbfile'] = $files[$i]['filename'] . '-preview.jpg';
                        $cmd_resize = 'convert ' . escapeshellarg($files[$i]['dest']) . ' -resize ' .
                        $board_settings['max_width'] . 'x' . $board_settings['max_height'] . '\> -quality ' .
                        $board_settings['jpeg_quality'] . ' -sharpen 0x0.5 ' .
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

                if ($board_settings['use_png_thumb'])
                {
                    imagepng($files[$i]['thumbnail'], $thumbpath . $files[$i]['thumbfile'], -1); // Quality
                }
                else
                {
                    imagejpeg($files[$i]['thumbnail'], $thumbpath . $files[$i]['thumbfile'], $board_settings['jpeg_quality']);
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
