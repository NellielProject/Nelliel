<?php

namespace Nelliel\Post;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;

class Previews
{
    private $domain;
    private $site_domain;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->site_domain = new \Nelliel\Domains\DomainSite(nel_database());
    }

    public function generate($files, $preview_path)
    {
        $file_handler = nel_utilities()->fileHandler();
        $i = 0;
        $files_count = count($files);

        for($i  = 0; $i < $files_count; $i++)
        {
            if (!$files[$i]->data('display_width') > 0 || !$files[$i]->data('display_height') > 0)
            {
                continue;
            }

            if ($files[$i]->data('type') === 'graphics')
            {
                $parameters = array();
                $ratio = min(($this->domain->setting('max_height') / $files[$i]->data('display_height')),
                        ($this->domain->setting('max_width') / $files[$i]->data('display_width')));
                $files[$i]->changeData('preview_width',
                        ($ratio < 1) ? intval($ratio * $files[$i]->data('display_width')) : $files[$i]->data(
                                'display_width'));
                $files[$i]->changeData('preview_height',
                        ($ratio < 1) ? intval($ratio * $files[$i]->data('display_height')) : $files[$i]->data(
                                'display_height'));
                $file_handler->createDirectory($preview_path, NEL_DIRECTORY_PERM, true);
                $files[$i]->changeData('preview_name', $files[$i]->data('filename') . '-preview');

                if ($this->domain->setting('use_png_preview'))
                {
                    $parameters['compression'] = $this->domain->setting('png_compression');
                    $parameters['destination_format'] = 'png';
                    $files[$i]->changeData('preview_extension', 'png');
                }
                else
                {
                    $parameters['compression'] = $this->domain->setting('jpeg_quality');
                    $parameters['destination_format'] = 'jpeg';
                    $files[$i]->changeData('preview_extension', 'jpg');
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
                    $preview_made = $this->gd($files[$i], $preview_path);

                    if(!$preview_made)
                    {
                        $files[$i]->changeData('preview_name', null);
                        $files[$i]->changeData('preview_extension', null);
                    }
                }
            }

            clearstatcache();
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
        $resize_command = 'gm convert ' . escapeshellarg($file->data('location')) . ' ';

        if ($file->data('format') === 'gif' && $this->domain->setting('animated_gif_preview'))
        {
            $file->changeData('preview_extension', 'gif');

            // GrahpicsMagick fucks up coalesce on some animated GIFs but Gmagick extension seems to avoid the problem
            if (in_array('gmagick', $parameters['magicks']))
            {
                $this->gmagick($file, $preview_path, $parameters);
                return;
            }

            if ($file->data('display_width') > $this->domain->setting('max_width') ||
                    $file->data('display_height') > $this->domain->setting('max_height'))
            {
                $resize_command .= '-coalesce ';
                $resize_command .= '-resize ' . $file->data('preview_width') . 'x' . $file->data('preview_height') . ' ';
            }
        }
        else
        {
            $resize_command .= '-filter lanczos ';
            $resize_command .= '-resize ' . $file->data('preview_width') . 'x' . $file->data('preview_height') . ' ';
            $resize_command .= '-sharpen 0x' . $sharpen_sigma . ' ';
            $resize_command .= '-quality ' . $parameters['compression'] . ' ';
        }

        $resize_command .= '-strip ';
        $resize_command .= escapeshellarg(
                $preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension'));
        exec($resize_command, $out, $code);
        chmod($preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension'),
                octdec(NEL_FILES_PERM));
    }

    public function gmagick($file, $preview_path, $parameters)
    {
        $sharpen_sigma = 0.25;
        $filter = \gmagick::FILTER_LANCZOS;
        $image = new \Gmagick($file->data('location'));
        $image->setCompressionQuality($parameters['compression']);
        $image_count = $image->getNumberImages();

        if ($file->data('format') === 'gif' && $image_count > 1 && $this->domain->setting('animated_gif_preview'))
        {
            $file->changeData('preview_extension', 'gif');

            if ($file->data('display_width') > $file->data('preview_width') ||
                    $file->data('display_height') > $file->data('preview_height'))
            {
                $image = $image->coalesceImages();

                do
                {
                    $image->resizeImage($file->data('preview_width'), $file->data('preview_height'), $filter, 1.0);
                }
                while ($image->nextImage());
            }

            $image->setFormat('gif');
        }
        else
        {
            $image->resizeImage($file->data('preview_width'), $file->data('preview_height'), $filter, 1.0);
            $image->sharpenImage(0, $sharpen_sigma);
            $image->setFormat($parameters['destination_format']);
        }

        $image->stripImage();
        $image->writeImage($preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension'), true);
    }

    public function imageMagick($file, $preview_path, $parameters)
    {
        $sharpen_sigma = 0.25;
        $resize_command = 'convert ' . escapeshellarg($file->data('location')) . ' ';

        if ($file->data('format') === 'gif' && $this->domain->setting('animated_gif_preview'))
        {
            $file->changeData('preview_extension', 'gif');

            if ($file->data('display_width') > $this->domain->setting('max_width') ||
                    $file->data('display_height') > $this->domain->setting('max_height'))
            {
                $resize_command .= '-coalesce ';
                $resize_command .= '-resize ' . $file->data('preview_width') . 'x' . $file->data('preview_height') . ' ';
            }
        }
        else
        {
            $resize_command .= '-filter lanczos ';
            $resize_command .= '-resize ' . $file->data('preview_width') . 'x' . $file->data('preview_height') . ' ';
            $resize_command .= '-sharpen 0x' . $sharpen_sigma . ' ';
            $resize_command .= '-quality ' . $parameters['compression'] . ' ';
        }

        $resize_command .= '-strip ';
        $resize_command .= escapeshellarg(
                $preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension'));
        exec($resize_command, $out, $code);
        chmod($preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension'),
                octdec(NEL_FILES_PERM));
    }

    public function imagick($file, $preview_path, $parameters)
    {
        $sharpen_sigma = 0.25;
        $filter = \imagick::FILTER_LANCZOS;
        $image = new \Imagick($file->data('location'));
        $image->setImageCompressionQuality($parameters['compression']);
        $image_count = $image->getNumberImages();

        if ($file->data('format') === 'gif' && $image_count > 1 && $this->domain->setting('animated_gif_preview'))
        {
            $file->changeData('preview_extension', 'gif');

            if ($file->data('display_width') > $this->domain->setting('max_width') ||
                    $file->data('display_height') > $this->domain->setting('max_height'))
            {
                $image = $image->coalesceImages();

                foreach ($image as $frame)
                {
                    $frame->resizeImage($file->data('preview_width'), $file->data('preview_height'), $filter, 1.0);
                }
            }

            $image->setFormat('gif');
        }
        else
        {
            $image->resizeImage($file->data('preview_width'), $file->data('preview_height'), $filter, 1.0);
            $image->sharpenImage(0, $sharpen_sigma);
            $image->setFormat($parameters['destination_format']);
        }

        $image->stripImage();
        $image->writeImages($preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension'), true);
    }

    public function gd($file, $preview_path)
    {
        $gd_test = gd_info();

        if ($file->data('format') === 'jpeg' && $gd_test['JPEG Support'])
        {
            $image = imagecreatefromjpeg($file->data('location'));
        }
        else if ($file->data('format') === 'gif' && $gd_test['GIF Read Support'])
        {
            $image = imagecreatefromgif($file->data('location'));
        }
        else if ($file->data('format') === 'png' && $gd_test['PNG Support'])
        {
            $image = imagecreatefrompng($file->data('location'));
        }
        else if ($file->data('format') === 'webp' && $gd_test['WebP Support'])
        {
            $image = imagecreatefromwebp($file->data('location'));
        }
        else if ($file->data('format') === 'bmp' && $gd_test['BMP Support'])
        {
            $image = imagecreatefrombmp($file->data('location'));
        }
        else if ($file->data('format') === 'wbmp' && $gd_test['WBMP Support'])
        {
            $image = imagecreatefromwbmp($file->data('location'));
        }
        else if ($file->data('format') === 'xbm' && $gd_test['XBM Support'])
        {
            $image = imagecreatefromxbm($file->data('location'));
        }
        else if ($file->data('format') === 'xpm' && $gd_test['XPM Support'])
        {
            $image = imagecreatefromxpm($file->data('location'));
        }
        else
        {
            return false;
        }

        $preview = imagecreatetruecolor($file->data('preview_width'), $file->data('preview_height'));

        if ($preview !== false)
        {
            imagecolortransparent($preview, imagecolortransparent($image));
            imagealphablending($preview, false);
            imagesavealpha($preview, true);
            imagecopyresampled($preview, $image, 0, 0, 0, 0, $file->data('preview_width'), $file->data('preview_height'),
                    $file->data('display_width'), $file->data('display_height'));

            if ($this->domain->setting('use_png_preview'))
            {
                imagepng($preview, $preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension'),
                        $this->domain->setting('png_compression'));
            }
            else
            {
                imagejpeg($preview, $preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension'),
                        $this->domain->setting('jpeg_quality'));
            }

            return true;
        }

        return false;
    }
}