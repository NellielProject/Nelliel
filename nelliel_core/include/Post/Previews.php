<?php

namespace Nelliel\Post;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Previews
{
    private $domain;
    private $site_domain;

    function __construct($domain)
    {
        $this->domain = $domain;
        $this->site_domain = new \Nelliel\DomainSite(nel_database());
    }

    public function generate($files, $preview_path)
    {
        $file_handler = new \Nelliel\FileHandler();
        $i = 0;
        $files_count = count($files);

        while ($i < $files_count)
        {
            $files[$i]->content_data['preview_width'] = null;
            $files[$i]->content_data['preview_height'] = null;
            $files[$i]->content_data['preview_name'] = null;
            $files[$i]->content_data['preview_extension'] = null;

            if ($files[$i]->content_data['type'] === 'graphics')
            {
                $parameters = array();
                $ratio = min(($this->domain->setting('max_height') / $files[$i]->content_data['display_height']),
                        ($this->domain->setting('max_width') / $files[$i]->content_data['display_width']));
                $files[$i]->content_data['preview_width'] = ($ratio < 1) ? intval(
                        $ratio * $files[$i]->content_data['display_width']) : $files[$i]->content_data['display_width'];
                $files[$i]->content_data['preview_height'] = ($ratio < 1) ? intval(
                        $ratio * $files[$i]->content_data['display_height']) : $files[$i]->content_data['display_height'];
                $file_handler->createDirectory($preview_path, DIRECTORY_PERM, true);
                $files[$i]->content_data['preview_name'] = $files[$i]->content_data['filename'] . '-preview';

                if ($this->domain->setting('use_png_preview'))
                {
                    $parameters['compression'] = $this->domain->setting('png_compression');
                    $parameters['destination_format'] = 'png';
                    $files[$i]->content_data['preview_extension'] = 'png';
                }
                else
                {
                    $parameters['compression'] = $this->domain->setting('jpeg_quality');
                    $parameters['destination_format'] = 'jpeg';
                    $files[$i]->content_data['preview_extension'] = 'jpg';
                }

                $parameters['magicks'] = $this->magickAvailable();
                $graphics_handler = $this->site_domain->setting('graphics_handler');

                if ($graphics_handler === 'GraphicsMagick')
                {
                    if (in_array('graphicsmagick', $parameters['magicks']))
                    {
                        $this->graphicsMagick($files[$i], $preview_path, $parameters);
                    }
                    else if (in_array('gmagick', $parameters['magicks']))
                    {
                        $this->gmagick($files[$i], $preview_path, $parameters);
                    }
                    else
                    {
                        $this->gd($files[$i], $preview_path);
                    }
                }
                else if ($graphics_handler === 'ImageMagick')
                {
                    if (in_array('imagemagick', $parameters['magicks']))
                    {
                        $this->imageMagick($files[$i], $preview_path, $parameters);
                    }
                    else if (in_array('imagick', $parameters['magicks']))
                    {
                        $this->imagick($files[$i], $preview_path, $parameters);
                    }
                    else
                    {
                        $this->gd($files[$i], $preview_path);
                    }
                }
                else
                {
                    $this->gd($files[$i], $preview_path);
                }
            }

            clearstatcache();
            ++ $i;
        }

        return $files;
    }

    public function magickAvailable()
    {
        $magicks = array();

        if (extension_loaded('gmagick'))
        {
            $magicks[] = 'gmagick';
        }

        if (extension_loaded('imagick'))
        {
            $magicks[] = 'imagick';
        }

        if (function_exists('exec'))
        {
            exec("gm -version 2>&1", $out, $rescode);

            if ($rescode === 0)
            {
                $magicks[] = 'graphicsmagick';
            }

            exec("convert -version 2>&1", $out, $rescode);

            if ($rescode === 0)
            {
                $magicks[] = 'imagemagick';
            }
        }

        return $magicks;
    }

    public function graphicsMagick($file, $preview_path, $parameters)
    {
        $sharpen_sigma = 0.25;
        $resize_command = 'gm convert ' . escapeshellarg($file->content_data['location']) . ' ';

        if ($file->content_data['format'] === 'gif' && $this->domain->setting('animated_gif_preview'))
        {
            $file->content_data['preview_extension'] = 'gif';

            // GrahpicsMagick fucks up coalesce on some animated GIFs but Gmagick extension seems to avoid the problem
            if (in_array('gmagick', $parameters['magicks']))
            {
                $this->gmagick($file, $preview_path, $parameters);
                return;
            }

            if ($file->content_data['display_width'] > $this->domain->setting('max_width') ||
                    $file->content_data['display_height'] > $this->domain->setting('max_height'))
            {
                $resize_command .= '-coalesce ';
                $resize_command .= '-resize ' . $file->content_data['preview_width'] . 'x' .
                        $file->content_data['preview_height'] . ' ';
            }
        }
        else
        {
            $resize_command .= '-filter lanczos ';
            $resize_command .= '-resize ' . $file->content_data['preview_width'] . 'x' .
                    $file->content_data['preview_height'] . ' ';
            $resize_command .= '-sharpen 0x' . $sharpen_sigma . ' ';
            $resize_command .= '-quality ' . $parameters['compression'] . ' ';
        }

        $resize_command .= '-strip ';
        $resize_command .= escapeshellarg(
                $preview_path . $file->content_data['preview_name'] . '.' . $file->content_data['preview_extension']);
        exec($resize_command, $out, $code);
        chmod($preview_path . $file->content_data['preview_name'] . '.' . $file->content_data['preview_extension'],
                octdec(FILE_PERM));
    }

    public function gmagick($file, $preview_path, $parameters)
    {
        $sharpen_sigma = 0.25;
        $filter = \gmagick::FILTER_LANCZOS;
        $image = new \Gmagick($file->content_data['location']);
        $image->setCompressionQuality($parameters['compression']);
        $image_count = $image->getNumberImages();

        if ($file->content_data['format'] === 'gif' && $image_count > 1 && $this->domain->setting(
                'animated_gif_preview'))
        {
            $file->content_data['preview_extension'] = 'gif';

            if ($file->content_data['display_width'] > $file->content_data['preview_width'] ||
                    $file->content_data['display_height'] > $file->content_data['preview_height'])
            {
                $image = $image->coalesceImages();

                do
                {
                    $image->scaleImage($file->content_data['preview_width'], $file->content_data['preview_height']);
                }
                while ($image->nextImage());
            }

            $image->setFormat('gif');
        }
        else
        {
            $image->resizeImage($file->content_data['preview_width'], $file->content_data['preview_height'], $filter,
                    1.0);
            $image->sharpenImage(0, $sharpen_sigma);
            $image->setFormat($parameters['destination_format']);
        }

        $image->stripImage();
        $image->writeImage(
                $preview_path . $file->content_data['preview_name'] . '.' . $file->content_data['preview_extension'],
                true);
    }

    public function imageMagick($file, $preview_path, $parameters)
    {
        $sharpen_sigma = 0.25;
        $resize_command = 'convert ' . escapeshellarg($file->content_data['location']) . ' ';

        if ($file->content_data['format'] === 'gif' && $this->domain->setting('animated_gif_preview'))
        {
            $file->content_data['preview_extension'] = 'gif';

            if ($file->content_data['display_width'] > $this->domain->setting('max_width') ||
                    $file->content_data['display_height'] > $this->domain->setting('max_height'))
            {
                $resize_command .= '-coalesce ';
                $resize_command .= '-resize ' . $file->content_data['preview_width'] . 'x' .
                        $file->content_data['preview_height'] . ' ';
            }
        }
        else
        {
            $resize_command .= '-filter lanczos ';
            $resize_command .= '-resize ' . $file->content_data['preview_width'] . 'x' .
                    $file->content_data['preview_height'] . ' ';
            $resize_command .= '-sharpen 0x' . $sharpen_sigma . ' ';
            $resize_command .= '-quality ' . $parameters['compression'] . ' ';
        }

        $resize_command .= '-strip ';
        $resize_command .= escapeshellarg(
                $preview_path . $file->content_data['preview_name'] . '.' . $file->content_data['preview_extension']);
        exec($resize_command, $out, $code);
        chmod($preview_path . $file->content_data['preview_name'] . '.' . $file->content_data['preview_extension'],
                octdec(FILE_PERM));
    }

    public function imagick($file, $preview_path, $parameters)
    {
        $sharpen_sigma = 0.25;
        $filter = \imagick::FILTER_LANCZOS;
        $image = new \Imagick($file->content_data['location']);
        $image->setImageCompressionQuality($parameters['compression']);
        $image_count = $image->getNumberImages();

        if ($file->content_data['format'] === 'gif' && $image_count > 1 &&
                $this->domain->setting('animated_gif_preview'))
        {
            $file->content_data['preview_extension'] = 'gif';

            if ($file->content_data['display_width'] > $this->domain->setting('max_width') ||
                    $file->content_data['display_height'] > $this->domain->setting('max_height'))
            {
                $image = $image->coalesceImages();

                foreach ($image as $frame)
                {
                    $frame->scaleImage($file->content_data['preview_width'], $file->content_data['preview_height']);
                }
            }

            $image->setFormat('gif');
        }
        else
        {
            $image->resizeImage($file->content_data['preview_width'], $file->content_data['preview_height'], $filter,
                    1.0);
            $image->sharpenImage(0, $sharpen_sigma);
            $image->setFormat($parameters['destination_format']);
        }

        $image->stripImage();
        $image->writeImages(
                $preview_path . $file->content_data['preview_name'] . '.' . $file->content_data['preview_extension'],
                true);
    }

    public function gd($file, $preview_path)
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
            imagecolortransparent($preview, imagecolortransparent($image));
            imagealphablending($preview, false);
            imagesavealpha($preview, true);
            imagecopyresampled($preview, $image, 0, 0, 0, 0, $file->content_data['preview_width'],
                    $file->content_data['preview_height'], $file->content_data['display_width'],
                    $file->content_data['display_height']);

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