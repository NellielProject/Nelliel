<?php

function nel_process_file_info()
{
    global $enabled_types;

    $files = array();
    $i = 0;
    $filetypes_loaded = FALSE;
    $files_count = count($_FILES);

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
                    nel_derp(19, array(
                                        'origin' => 'POST',
                                        'bad-filename' => $files[i]['basic_filename'] . $files[i]['ext'],
                                        'files' => array($files[$i])));
                }

                $files[$i]['dest'] = SRC_PATH . $file['name'] . '.tmp';
                move_uploaded_file($file['tmp_name'], $files[$i]['dest']);
                chmod($files[$i]['dest'], 0644);
                $files[$i]['fsize'] = filesize($files[$i]['dest']);
                $test_ext = utf8_strtolower($files[$i]['ext']);
                $file_test = file_get_contents($files[$i]['dest'], NULL, NULL, 0, 65535);
                $file_good = FALSE;
                $file_allowed = FALSE;

                if (array_key_exists($test_ext, $filetypes))
                {
                    if ($enabled_types[$filetypes[$test_ext]['supertype']]['ENABLE']
                    && $enabled_types[$filetypes[$test_ext]['supertype']][$filetypes[$test_ext]['subtype']])
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
                    nel_derp(6, array(
                                    'origin' => 'POST', 'bad-filename' => $files[$i]['basic_filename'] . $files[$i]['ext'],
                                    'files' => array($files[$i])));
                }

                if (!$file_good)
                {
                    nel_derp(18, array(
                                        'origin' => 'POST',
                                        'bad-filename' => $files[$i]['basic_filename'] . $files[$i]['ext'],
                                        'files' => array($files[$i])));
                }

                ++ $i;
            }

            if ($files_count == BS_MAX_POST_FILES)
            {
                break;
            }
        }
        else if ($file['error'] === UPLOAD_ERR_INI_SIZE)
        {
            nel_derp(19, array(
                                'origin' => 'POST', 'bad-filename' => $files[i]['basic_filename'] . $files[i]['ext'],
                                'files' => array($files[$i])));
        }
    }

    return $files;
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

        if ($files[$i]['subtype'] === 'SWF' || ($files[$i]['supertype'] === 'GRAPHICS' && !BS_USE_MAGICK))
        {
            $dim = getimagesize($files[$i]['dest']);
            $files[$i]['im_x'] = $dim[0];
            $files[$i]['im_y'] = $dim[1];
            $ratio = min((BS_MAX_HEIGHT / $files[$i]['im_y']), (BS_MAX_WIDTH / $files[$i]['im_x']));
            $files[$i]['pre_x'] = ($files[$i]['im_x'] > BS_MAX_WIDTH) ? intval($ratio * $files[$i]['im_x']) : $files[$i]['im_x'];
            $files[$i]['pre_y'] = ($files[$i]['im_y'] > BS_MAX_HEIGHT) ? intval($ratio * $files[$i]['im_y']) : $files[$i]['im_y'];
        }

        if (BS_USE_THUMB && $files[$i]['supertype'] === 'GRAPHICS')
        {
            exec("convert -version", $out, $rescode);

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
                    if (BS_USE_PNG_THUMB)
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

        if (!file_exists($srcpath . $files[$i]['basic_filename'] . $files[$i]['ext']))
        {
            rename($files[$i]['dest'], $srcpath . $files[$i]['basic_filename'] . '.' . $files[$i]['ext']);
        }
        else
        {
            $files[$i]['basic_filename'] = 'copy' . utf8_substr($time, -4) . '--' . $files[$i]['basic_filename'];
            rename($files[$i]['dest'], $srcpath . $files[$i]['basic_filename'] . '.' . $files[$i]['ext']);
        }
        ++ $i;
    }

    return $files;
}
