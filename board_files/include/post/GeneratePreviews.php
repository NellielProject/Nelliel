<?php

namespace Nelliel\post;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class GeneratePreviews
{
    private $board_id;

    function __construct($board_id = '')
    {
        $this->board_id = $board_id;
    }


    public function generate($files, $preview_path)
    {
        $file_handler = new \Nelliel\FileHandler();
        $i = 0;
        $files_count = count($files);
        $board_settings = nel_parameters_and_data()->boardSettings($this->board_id);

        while ($i < $files_count)
        {
            $files[$i]->file_data['im_x'] = null;
            $files[$i]->file_data['im_y'] = null;
            $files[$i]->file_data['pre_x'] = null;
            $files[$i]->file_data['pre_y'] = null;
            $files[$i]->file_data['preview_name'] = null;
            $files[$i]->file_data['preview_extension'] = null;

            if ($files[$i]->file_data['format'] === 'swf' || ($files[$i]->file_data['type'] === 'graphics'))
            {
                $dim = getimagesize($files[$i]->file_data['location']);
                $files[$i]->file_data['im_x'] = $dim[0];
                $files[$i]->file_data['im_y'] = $dim[1];
                $ratio = min(($board_settings['max_height'] / $files[$i]->file_data['im_y']), ($board_settings['max_width'] /
                        $files[$i]->file_data['im_x']));
                $files[$i]->file_data['pre_x'] = ($ratio < 1) ? intval($ratio * $files[$i]->file_data['im_x']) : $files[$i]->file_data['im_x'];
                $files[$i]->file_data['pre_y'] = ($ratio < 1) ? intval($ratio * $files[$i]->file_data['im_y']) : $files[$i]->file_data['im_y'];
            }

            if ($board_settings['use_thumb'] && $files[$i]->file_data['type'] === 'graphics')
            {
                $file_handler->createDirectory($preview_path, DIRECTORY_PERM, true);
                $magick_available = $this->magickAvailable();
                $files[$i]->file_data['preview_name'] = $files[$i]->file_data['filename'] . '-preview';

                if ($board_settings['use_png_thumb'])
                {
                    $files[$i]->file_data['preview_extension'] = 'png';
                }
                else
                {
                    $files[$i]->file_data['preview_extension'] = 'jpg';
                }

                if ($board_settings['use_magick'] && $magick_available !== false)
                {
                    if ($magick_available === 'imagick')
                    {
                        $this->imagickPreview($files[$i], $preview_path);
                    }
                    else if ($magick_available === 'imagemagick')
                    {
                        $this->imagemagickPreview($files[$i], $preview_path);
                    }
                }
                else
                {
                    $this->gdPreview($files[$i], $preview_path);
                }
            }

            clearstatcache();
            ++ $i;
        }

        return $files;
    }

    public function magickAvailable()
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

    public function imagickPreview($file, $preview_path)
    {
        $image = new \Imagick($file->file_data['location']);
        $iterations = $image->getImageIterations();
        $image = $image->coalesceimages();
        $board_settings = nel_parameters_and_data()->boardSettings($this->board_id);

        if ($file->file_data['format'] === 'gif' && $iterations > 0 && $board_settings['animated_gif_preview'])
        {
            $file->file_data['preview_extension'] = 'gif';

            if ($file->file_data['im_x'] <= $board_settings['max_width'] && $file->file_data['im_y'] <= $board_settings['max_height'])
            {
                copy($file->file_data['location'], $preview_path . $file->file_data['preview_name'] . '.' . $file->file_data['preview_extension']);
            }
            else
            {
                foreach ($image as $frame)
                {
                    $frame->scaleImage($file->file_data['pre_x'], $file->file_data['pre_y'], true);
                }

                $image->writeImages($preview_path . $file->file_data['preview_name'] . '.' . $file->file_data['preview_extension'], true);
            }
        }
        else
        {
            $image->thumbnailImage($file->file_data['pre_x'], $file->file_data['pre_y'], true);
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

            $image->writeImage($preview_path . $file->file_data['preview_name'] . '.' . $file->file_data['preview_extension']);
        }
    }

    public function imagemagickPreview($file, $preview_path)
    {
        $board_settings = nel_parameters_and_data()->boardSettings($this->board_id);

        if ($file->file_data['format'] === 'gif' && $iterations > 0 && $board_settings['animated_gif_preview'])
        {
            $file->file_data['preview_extension'] = 'gif';
            $cmd_resize = 'convert ' . escapeshellarg($file->file_data['location']) . ' -coalesce -thumbnail ' .
            $board_settings['max_width'] . 'x' . $board_settings['max_height'] .
            escapeshellarg($preview_path . $file->file_data['preview_name'] . '.' . $file->file_data['preview_extension']);
            exec($cmd_resize);
            chmod($preview_path . $file->file_data['preview_name'] . '.' . $file->file_data['preview_extension'], octdec(FILE_PERM));
        }
        else
        {
            if ($board_settings['use_png_thumb'])
            {
                $cmd_resize = 'convert ' . escapeshellarg($file->file_data['location']) . ' -resize ' . $board_settings['max_width'] . 'x' .
                $board_settings['max_height'] . '\> -quality 00 -sharpen 0x0.5 ' .
                escapeshellarg($preview_path . $file->file_data['preview_name'] . '.' . $file->file_data['preview_extension']);
            }
            else
            {
                $cmd_resize = 'convert ' . escapeshellarg($file->file_data['location']) . ' -resize ' . $board_settings['max_width'] . 'x' .
                $board_settings['max_height'] . '\> -quality ' . $board_settings['jpeg_quality'] . ' -sharpen 0x0.5 ' .
                escapeshellarg($preview_path . $file->file_data['preview_name'] . '.' . $file->file_data['preview_extension']);
            }

            exec($cmd_resize);
            chmod($preview_path . $file->file_data['preview_name'] . '.' . $file->file_data['preview_extension'], octdec(FILE_PERM));
        }
    }

    public function gdPreview($file, $preview_path)
    {
        $board_settings = nel_parameters_and_data()->boardSettings($this->board_id);
        $gd_test = gd_info(); // This shouldn't be needed. If your host actually doesn't have these, it sucks. Get a new one, srsly.

        if ($file->file_data['format'] === 'jpeg' && $gd_test["JPEG Support"])
        {
            $image = imagecreatefromjpeg($file->file_data['location']);
        }
        else if ($file->file_data['format'] === 'gif' && $gd_test["GIF Read Support"])
        {
            $image = imagecreatefromgif($file->file_data['location']);
        }
        else if ($file->file_data['format'] === 'png' && $gd_test["PNG Support"])
        {
            $image = imagecreatefrompng($file->file_data['location']);
        }
        else
        {
            return false;
        }

        $preview = imagecreatetruecolor($file->file_data['pre_x'], $file->file_data['pre_y']);

        if ($preview !== false)
        {
            imagecopyresampled($preview, $image, 0, 0, 0, 0, $file->file_data['pre_x'], $file->file_data['pre_y'], $file->file_data['im_x'], $file->file_data['im_y']);

            if ($board_settings['use_png_thumb'])
            {
                imagepng($preview, $preview_path . $file->file_data['preview_name'] . '.' . $file->file_data['preview_extension'], -1);
            }
            else
            {
                imagejpeg($preview, $preview_path . $file->file_data['preview_name'] . '.' . $file->file_data['preview_extension'], $board_settings['jpeg_quality']);
            }
        }
    }
}