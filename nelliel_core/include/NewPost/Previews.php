<?php
declare(strict_types = 1);

namespace Nelliel\NewPost;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;

class Previews
{
    private $domain;
    private $site_domain;
    private $shell_path = '';

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->site_domain = nel_site_domain();
        $this->shell_path = $this->site_domain->setting('shell_path');
    }

    public function generate($files, $preview_path)
    {
        $filename_suffix = '_preview';
        $file_handler = nel_utilities()->fileHandler();
        $i = 0;
        $files_count = count($files);

        for ($i = 0; $i < $files_count; $i ++)
        {
            if (!$files[$i]->data('display_width') > 0 || !$files[$i]->data('display_height') > 0)
            {
                continue;
            }

            if ($files[$i]->data('category') === 'graphics')
            {
                $parameters = array();
                $ratio = min(($this->domain->setting('max_preview_height') / $files[$i]->data('display_height')),
                        ($this->domain->setting('max_preview_width') / $files[$i]->data('display_width')));
                $files[$i]->changeData('preview_width',
                        ($ratio < 1) ? intval($ratio * $files[$i]->data('display_width')) : $files[$i]->data(
                                'display_width'));
                $files[$i]->changeData('preview_height',
                        ($ratio < 1) ? intval($ratio * $files[$i]->data('display_height')) : $files[$i]->data(
                                'display_height'));
                $file_handler->createDirectory($preview_path, NEL_DIRECTORY_PERM, true);

                $filename_maxlength = 255 - strlen($files[$i]->data('extension')) - 1 - strlen($filename_suffix);
                $trimmed_filename = substr($files[$i]->data('filename'), 0, $filename_maxlength);
                $files[$i]->changeData('preview_name', $trimmed_filename . $filename_suffix);

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

                // We favor command line here as it tends to work better with more flexibility than the extensions
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

                    if (!$preview_made)
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

        $results = nel_exec($this->shell_path, 'gm -version');

        if (!empty($results) && $results['result_code'] === 0)
        {
            $magicks[] = 'graphicsmagick';
        }

        $results = nel_exec($this->shell_path, 'convert -version');

        if (!empty($results) && $results['result_code'] === 0)
        {
            $magicks[] = 'imagemagick';
        }

        return $magicks;
    }

    public function graphicsMagick($file, $preview_path, $parameters)
    {
        if ($file->data('format') === 'gif' && $this->domain->setting('animated_preview'))
        {
            $file->changeData('preview_extension', 'gif');
            $resize_command = 'gm convert ' .
                    sprintf($this->site_domain->setting('graphicsmagick_animated_args'),
                            escapeshellarg($file->data('location')), $file->data('preview_width'),
                            $file->data('preview_height'),
                            escapeshellarg(
                                    $preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension')));
        }
        else
        {
            $resize_command = 'gm convert ' .
                    sprintf($this->site_domain->setting('graphicsmagick_args'),
                            escapeshellarg($file->data('location') . '[0]'), $file->data('preview_width'),
                            $file->data('preview_height'), $parameters['compression'],
                            escapeshellarg(
                                    $preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension')));
        }

        $results = nel_exec($this->shell_path, $resize_command); // TODO: Proper error
        chmod($preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension'),
                octdec(NEL_FILES_PERM));
    }

    public function gmagick($file, $preview_path, $parameters)
    {
        $image = new \Gmagick($file->data('location'));
        $image_count = $image->getNumberImages();

        if ($file->data('format') === 'gif' && $image_count > 1 && $this->domain->setting('animated_preview'))
        {
            $file->changeData('preview_extension', 'gif');
            $image = $image->coalesceImages();

            // Straight thumbnail works for simple animations but not complex ones so we process frames individually
            // Note: For some reason Gmagick thumbnail output can be a bit different than gm convert
            foreach ($image as $frame)
            {
                $frame->thumbnailimage($file->data('preview_width'), $file->data('preview_height'));
            }

            $image->setFormat('gif');
            $image->writeImage($preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension'),
                    true);
        }
        else
        {
            $image->thumbnailimage($file->data('preview_width'), $file->data('preview_height'));
            $image->setCompressionQuality($parameters['compression']);
            $image->setFormat($parameters['destination_format']);
            $image->writeImage($preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension'),
                    false);
        }
    }

    public function imageMagick($file, $preview_path, $parameters)
    {
        if ($file->data('format') === 'gif' && $this->domain->setting('animated_preview'))
        {
            $file->changeData('preview_extension', 'gif');
            $resize_command = 'convert ' .
                    sprintf($this->site_domain->setting('imagemagick_animated_args'),
                            escapeshellarg($file->data('location')), $file->data('preview_width'),
                            $file->data('preview_height'),
                            escapeshellarg(
                                    $preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension')));
        }
        else
        {
            $resize_command = 'convert ' .
                    sprintf($this->site_domain->setting('imagemagick_args'),
                            escapeshellarg($file->data('location') . '[0]'), $file->data('preview_width'),
                            $file->data('preview_height'), $parameters['compression'],
                            escapeshellarg(
                                    $preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension')));
        }

        $results = nel_exec($this->shell_path, $resize_command); // TODO: Proper error
        chmod($preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension'),
                octdec(NEL_FILES_PERM));
    }

    public function imagick($file, $preview_path, $parameters)
    {
        $image = new \Imagick($file->data('location'));
        $image_count = $image->getNumberImages();

        if ($file->data('format') === 'gif' && $image_count > 1 && $this->domain->setting('animated_preview'))
        {
            $file->changeData('preview_extension', 'gif');
            $image = $image->coalesceImages();

            // Straight thumbnail works for simple animations but not complex ones so we process frames individually
            foreach ($image as $frame)
            {
                $frame->thumbnailimage($file->data('preview_width'), $file->data('preview_height'));
            }

            $image->setFormat('gif');
            $image->writeImages($preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension'),
                    true);
        }
        else
        {
            $image->thumbnailimage($file->data('preview_width'), $file->data('preview_height'));
            $image->setImageCompressionQuality($parameters['compression']);
            $image->setFormat($parameters['destination_format']);
            $image->writeImage($preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension'));
        }
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