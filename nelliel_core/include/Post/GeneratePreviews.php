<?php

namespace Nelliel\Post;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class GeneratePreviews
{
    private $domain;

    function __construct($domain)
    {
        $this->domain = $domain;
    }

    public function generate($files, $preview_path)
    {
        $file_handler = new \Nelliel\FileHandler();
        $i = 0;
        $files_count = count($files);

        while ($i < $files_count)
        {
            $files[$i]->content_data['display_width'] = null;
            $files[$i]->content_data['display_height'] = null;
            $files[$i]->content_data['preview_width'] = null;
            $files[$i]->content_data['preview_height'] = null;
            $files[$i]->content_data['preview_name'] = null;
            $files[$i]->content_data['preview_extension'] = null;

            if ($files[$i]->content_data['format'] === 'swf' || ($files[$i]->content_data['type'] === 'graphics'))
            {
                $dim = getimagesize($files[$i]->content_data['location']);
                $files[$i]->content_data['display_width'] = $dim[0];
                $files[$i]->content_data['display_height'] = $dim[1];
                $ratio = min(($this->domain->setting('max_height') / $files[$i]->content_data['display_height']),
                        ($this->domain->setting('max_width') / $files[$i]->content_data['display_width']));
                $files[$i]->content_data['preview_width'] = ($ratio < 1) ? intval(
                        $ratio * $files[$i]->content_data['display_width']) : $files[$i]->content_data['display_width'];
                $files[$i]->content_data['preview_height'] = ($ratio < 1) ? intval(
                        $ratio * $files[$i]->content_data['display_height']) : $files[$i]->content_data['display_height'];
            }

            if ($this->domain->setting('use_preview') && $files[$i]->content_data['type'] === 'graphics')
            {
                $file_handler->createDirectory($preview_path, DIRECTORY_PERM, true);
                $magick_available = $this->magickAvailable();
                $files[$i]->content_data['preview_name'] = $files[$i]->content_data['filename'] . '-preview';

                if ($this->domain->setting('use_png_preview'))
                {
                    $files[$i]->content_data['preview_extension'] = 'png';
                }
                else
                {
                    $files[$i]->content_data['preview_extension'] = 'jpg';
                }

                if ($this->domain->setting('use_magick') && $magick_available !== false)
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
            exec("/usr/local/bin/convert -version 2>&1", $out, $rescode);

            if ($rescode === 0)
            {
                return 'imagemagick';
            }
        }

        return false;
    }

    public function imagickPreview($file, $preview_path)
    {
        $image = new \Imagick($file->content_data['location']);
        $iterations = $image->getNumberImages();

        if ($file->content_data['format'] === 'gif' && $iterations > 1 && $this->domain->setting('animated_gif_preview'))
        {
            $file->content_data['preview_extension'] = 'gif';
            $image = $image->coalesceimages();

            if ($file->content_data['display_width'] <= $this->domain->setting('max_width') &&
                    $file->content_data['display_height'] <= $this->domain->setting('max_height'))
            {
                copy($file->content_data['location'],
                        $preview_path . $file->content_data['preview_name'] . '.' .
                        $file->content_data['preview_extension']);
            }
            else
            {
                foreach ($image as $frame)
                {
                    $frame->scaleImage($file->content_data['preview_width'], $file->content_data['preview_height'], true);
                }

                $image->writeImages(
                        $preview_path . $file->content_data['preview_name'] . '.' .
                        $file->content_data['preview_extension'], true);
            }
        }
        else
        {
            $image->thumbnailImage($file->content_data['preview_width'], $file->content_data['preview_height'], true);
            $image->sharpenImage(0, 0.5);

            if ($this->domain->setting('use_png_preview'))
            {
                $image->setImageFormat('png');
                $image->setImageCompressionQuality($this->domain->setting('png_compression'));
            }
            else
            {
                $image->setImageFormat('jpeg');
                $image->setImageCompressionQuality($this->domain->setting('jpeg_quality'));
            }

            $image->writeImage(
                    $preview_path . $file->content_data['preview_name'] . '.' . $file->content_data['preview_extension']);
        }
    }

    public function imagemagickPreview($file, $preview_path)
    {
        if ($file->content_data['format'] === 'gif' && $this->domain->setting('animated_gif_preview'))
        {
            if ($file->content_data['display_width'] <= $this->domain->setting('max_width') &&
                    $file->content_data['display_height'] <= $this->domain->setting('max_height'))
            {
                copy($file->content_data['location'],
                        $preview_path . $file->content_data['preview_name'] . '.' .
                        $file->content_data['preview_extension']);
            }
            else
            {
                $file->content_data['preview_extension'] = 'gif';
                $cmd_resize = 'convert ' . escapeshellarg($file->content_data['location']) . ' -coalesce -resize ' .
                        $this->domain->setting('max_width') . 'x' . $this->domain->setting('max_height') . ' ' . escapeshellarg(
                                $preview_path . $file->content_data['preview_name'] . '.' .
                                $file->content_data['preview_extension']);
                exec($cmd_resize);
                chmod(
                        $preview_path . $file->content_data['preview_name'] . '.' .
                        $file->content_data['preview_extension'], octdec(FILE_PERM));
            }
        }
        else
        {
            if ($this->domain->setting('use_png_preview'))
            {
                $cmd_resize = 'convert ' . escapeshellarg($file->content_data['location']) . ' -resize ' .
                        $this->domain->setting('max_width') . 'x' . $this->domain->setting('max_height') . '\> -quality ' .
                        $this->domain->setting('png_compression') . ' -sharpen 0x0.5 ' . escapeshellarg(
                                $preview_path . $file->content_data['preview_name'] . '.' .
                                $file->content_data['preview_extension']);
            }
            else
            {
                $cmd_resize = 'convert ' . escapeshellarg($file->content_data['location']) . ' -resize ' .
                        $this->domain->setting('max_width') . 'x' . $this->domain->setting('max_height') . '\> -quality ' .
                        $this->domain->setting('jpeg_quality') . ' -sharpen 0x0.5 ' . escapeshellarg(
                                $preview_path . $file->content_data['preview_name'] . '.' .
                                $file->content_data['preview_extension']);
            }

            exec($cmd_resize);
            chmod($preview_path . $file->content_data['preview_name'] . '.' . $file->content_data['preview_extension'],
                    octdec(FILE_PERM));
        }
    }

    public function gdPreview($file, $preview_path)
    {
        $gd_test = gd_info(); // This shouldn't be needed. If your host actually doesn't have these, it sucks. Get a new one, srsly.

        if ($file->content_data['format'] === 'jpeg' && $gd_test["JPEG Support"])
        {
            $image = imagecreatefromjpeg($file->content_data['location']);
        }
        else if ($file->content_data['format'] === 'gif' && $gd_test["GIF Read Support"])
        {
            $image = imagecreatefromgif($file->content_data['location']);
        }
        else if ($file->content_data['format'] === 'png' && $gd_test["PNG Support"])
        {
            $image = imagecreatefrompng($file->content_data['location']);
        }
        else
        {
            return false;
        }

        $preview = imagecreatetruecolor($file->content_data['preview_width'], $file->content_data['preview_height']);

        if ($preview !== false)
        {
            $sharpen_matrix = [[0.0, -0.25, 0.0], [-0.25, 5.0, -0.25], [0.0, -0.25, 0.0]];
            $divisor = array_sum(array_map('array_sum', $sharpen_matrix));
            imagecopyresampled($preview, $image, 0, 0, 0, 0, $file->content_data['preview_width'],
                    $file->content_data['preview_height'], $file->content_data['display_width'],
                    $file->content_data['display_height']);
            imageconvolution($preview, $sharpen_matrix, $divisor, 0);

            if ($this->domain->setting('use_png_preview'))
            {
                imagepng($preview,
                        $preview_path . $file->content_data['preview_name'] . '.' .
                        $file->content_data['preview_extension'], $this->domain->setting('png_compression'));
            }
            else
            {
                imagejpeg($preview,
                        $preview_path . $file->content_data['preview_name'] . '.' .
                        $file->content_data['preview_extension'], $this->domain->setting('jpeg_quality'));
            }
        }
    }
}