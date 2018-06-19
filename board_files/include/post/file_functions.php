<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_generate_previews($board_id, $files, $srcpath, $preview_path)
{
    $file_handler = new \Nelliel\FileHandler();
    $i = 0;
    $files_count = count($files);
    $board_settings = nel_board_settings($board_id);

    while ($i < $files_count)
    {
        $files[$i]['im_x'] = null;
        $files[$i]['im_y'] = null;
        $files[$i]['pre_x'] = null;
        $files[$i]['pre_y'] = null;
        $files[$i]['preview_name'] = null;
        $files[$i]['preview_extension'] = null;

        if ($files[$i]['format'] === 'swf' || ($files[$i]['type'] === 'graphics'))
        {
            $dim = getimagesize($files[$i]['location']);
            $files[$i]['im_x'] = $dim[0];
            $files[$i]['im_y'] = $dim[1];
            $ratio = min(($board_settings['max_height'] / $files[$i]['im_y']), ($board_settings['max_width'] /
                 $files[$i]['im_x']));
            $files[$i]['pre_x'] = ($ratio < 1) ? intval($ratio * $files[$i]['im_x']) : $files[$i]['im_x'];
            $files[$i]['pre_y'] = ($ratio < 1) ? intval($ratio * $files[$i]['im_y']) : $files[$i]['im_y'];
        }

        if ($board_settings['use_thumb'] && $files[$i]['type'] === 'graphics')
        {
            $file_handler->createDirectory($preview_path, DIRECTORY_PERM, true);
            $magick_available = nel_is_magick_available();

            $files[$i]['preview_name'] = $files[$i]['filename'] . '-preview';

            if ($board_settings['use_png_thumb'])
            {
                $files[$i]['preview_extension'] = 'png';
            }
            else
            {
                $files[$i]['preview_extension'] = 'jpg';
            }

            if ($board_settings['use_magick'] && $magick_available !== false)
            {
                if ($magick_available === 'imagick')
                {
                    nel_create_imagick_preview($files[$i], $preview_path, $board_id);
                }
                else if ($magick_available === 'imagemagick')
                {
                    nel_create_imagemagick_preview($files[$i], $preview_path, $board_id);
                }
            }
            else
            {
                nel_create_gd_preview($files[$i], $preview_path, $board_id);
            }
        }

        clearstatcache();
        ++ $i;
    }

    return $files;
}

function nel_is_magick_available()
{
    if (extension_loaded('imagick'))
    {
        return 'imagick';
    }

    if (function_exists('exec'))
    {
        exec("convert -version 2>/dev/null", $out, $rescode);

        if ($rescode === 0)
        {
            return 'imagemagick';
        }
    }

    return false;
}

function nel_create_imagick_preview(&$file, $preview_path, $board_id)
{
    $image = new Imagick($file['location']);
    $iterations = $image->getImageIterations();
    $image = $image->coalesceimages();
    $board_settings = nel_board_settings($board_id);

    if ($file['format'] === 'gif' && $iterations > 0 && $board_settings['animated_gif_preview'])
    {
        $file['preview_extension'] = 'gif';

        if ($file['im_x'] <= $board_settings['max_width'] && $file['im_y'] <= $board_settings['max_height'])
        {
            copy($file['location'], $preview_path . $file['preview_name'] . '.' . $file['preview_extension']);
        }
        else
        {
            foreach ($image as $frame)
            {
                $frame->scaleImage($file['pre_x'], $file['pre_y'], true);
            }

            $image->writeImages($preview_path . $file['preview_name'] . '.' . $file['preview_extension'], true);
        }
    }
    else
    {
        $image->thumbnailImage($file['pre_x'], $file['pre_y'], true);
        $image->sharpenImage(0, 0.5);

        if ($board_settings['use_png_thumb'])
        {
            $image->setImageFormat('png');
        }
        else
        {
            $image->setImageFormat('jpeg');
            $image->setImageCompressionQuality($board_settings['jpeg_quality']);
        }

        $image->writeImage($preview_path . $file['preview_name'] . '.' . $file['preview_extension']);
    }
}

function nel_create_imagemagick_preview(&$file, $preview_path, $board_id)
{
    $board_settings = nel_board_settings($board_id);

    if ($file['format'] === 'gif' && $iterations > 0 && $board_settings['animated_gif_preview'])
    {
        $file['preview_extension'] = 'gif';
        $cmd_resize = 'convert ' . escapeshellarg($file['location']) . ' -coalesce -thumbnail ' .
             $board_settings['max_width'] . 'x' . $board_settings['max_height'] .
             escapeshellarg($preview_path . $file['preview_name'] . '.' . $file['preview_extension']);
        exec($cmd_resize);
        chmod($preview_path . $file['preview_name'] . '.' . $file['preview_extension'], octdec(FILE_PERM));
    }
    else
    {
        if ($board_settings['use_png_thumb'])
        {
            $cmd_resize = 'convert ' . escapeshellarg($file['location']) . ' -resize ' . $board_settings['max_width'] . 'x' .
                 $board_settings['max_height'] . '\> -quality 00 -sharpen 0x0.5 ' .
                 escapeshellarg($preview_path . $file['preview_name'] . '.' . $file['preview_extension']);
        }
        else
        {
            $cmd_resize = 'convert ' . escapeshellarg($file['location']) . ' -resize ' . $board_settings['max_width'] . 'x' .
                 $board_settings['max_height'] . '\> -quality ' . $board_settings['jpeg_quality'] . ' -sharpen 0x0.5 ' .
                 escapeshellarg($preview_path . $file['preview_name'] . '.' . $file['preview_extension']);
        }

        exec($cmd_resize);
        chmod($preview_path . $file['preview_name'] . '.' . $file['preview_extension'], octdec(FILE_PERM));
    }
}

function nel_create_gd_preview($file, $preview_path, $board_id)
{
    $board_settings = nel_board_settings($board_id);
    // This shouldn't be needed. If your host actually doesn't have these, it sucks. Get a new one, srsly.
    $gd_test = gd_info();

    if ($file['format'] === 'jpeg' && $gd_test["JPEG Support"])
    {
        $image = imagecreatefromjpeg($file['location']);
    }
    else if ($file['format'] === 'gif' && $gd_test["GIF Read Support"])
    {
        $image = imagecreatefromgif($file['location']);
    }
    else if ($file['format'] === 'png' && $gd_test["PNG Support"])
    {
        $image = imagecreatefrompng($file['location']);
    }
    else
    {
        return false;
    }

    $preview = imagecreatetruecolor($file['pre_x'], $file['pre_y']);

    if ($preview !== false)
    {
        imagecopyresampled($preview, $image, 0, 0, 0, 0, $file['pre_x'], $file['pre_y'], $file['im_x'], $file['im_y']);

        if ($board_settings['use_png_thumb'])
        {
            imagepng($preview, $preview_path . $file['preview_name'] . '.' . $file['preview_extension'], -1);
        }
        else
        {
            imagejpeg($preview, $preview_path . $file['preview_name'] . '.' . $file['preview_extension'], $board_settings['jpeg_quality']);
        }
    }
}
