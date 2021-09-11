<?php
declare(strict_types = 1);

namespace Nelliel\NewPost;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\Upload;
use Nelliel\Domains\Domain;

class Previews
{
    private $domain;
    private $site_domain;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->site_domain = nel_site_domain();
    }

    public function generate($files, $preview_path)
    {
        $file_handler = nel_utilities()->fileHandler();
        $i = 0;
        $files_count = count($files);

        for ($i = 0; $i < $files_count; $i ++)
        {
            if ($files[$i]->data('display_width') <= 0 && $files[$i]->data('display_height') <= 0)
            {
                continue;
            }

            $file_handler->createDirectory($preview_path, NEL_DIRECTORY_PERM, true);
            $magicks = nel_magick_available();
            $graphics_handler = $this->site_domain->setting('graphics_handler');
            $preview_made = false;

            // We favor command line here as it tends to work better with more flexibility than the extensions
            if ($graphics_handler === 'GraphicsMagick')
            {
                if (in_array('graphicsmagick', $magicks))
                {
                    $preview_made = $this->graphicsMagick($files[$i], $preview_path);
                }
                else if (in_array('gmagick', $magicks))
                {
                    $preview_made = $this->gmagick($files[$i], $preview_path);
                }
            }

            if ($graphics_handler === 'ImageMagick')
            {
                if (in_array('imagemagick', $magicks))
                {
                    $preview_made = $this->imageMagick($files[$i], $preview_path);
                }
                else if (in_array('imagick', $magicks))
                {
                    $preview_made = $this->imagick($files[$i], $preview_path);
                }
            }

            if ($graphics_handler === 'GD' || !$preview_made)
            {
                $preview_made = $this->gd($files[$i], $preview_path);
            }

            if (!$preview_made)
            {
                $this->nullPreview($files[$i]);
            }

            clearstatcache();
        }

        return $files;
    }

    public function imageMagick($file, $preview_path): bool
    {
        $frame_count = 1;
        $results = nel_exec('identify ' . escapeshellarg($file->data('location')));

        if ($results['result_code'] === 0)
        {
            $frame_count = count($results['output']);
        }

        $this->setPreviewDimensions($file);
        $this->setPreviewName($file);

        if ($this->domain->setting('animated_preview') && $frame_count > 1)
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
                            $file->data('preview_height'), $this->compressionValue(),
                            escapeshellarg(
                                    $preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension')));
        }

        $results = nel_exec($resize_command);

        if ($results['result_code'] !== 0)
        {
            return false;
        }

        chmod($preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension'),
                octdec(NEL_FILES_PERM));
        return true;
    }

    public function imagick($file, $preview_path): bool
    {
        $image = new \Imagick($file->data('location'));
        $frame_count = $image->getnumberimages();
        $this->setPreviewDimensions($file);
        $this->setPreviewName($file);

        if ($this->domain->setting('animated_preview') && $frame_count > 1)
        {
            $file->changeData('preview_extension', 'gif');
            $image = $image->coalesceimages();

            // Straight thumbnail works for simple animations but not complex ones so we process frames individually
            foreach ($image as $frame)
            {
                $frame->thumbnailimage($file->data('preview_width'), $file->data('preview_height'));
            }

            $image->setformat('gif');
            $image->writeimages($preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension'),
                    true);
        }
        else
        {
            $image->thumbnailimage($file->data('preview_width'), $file->data('preview_height'));
            $image->setimagecompressionquality($this->compressionValue());
            $image->setformat($this->outputFormat());
            $image->writeimage($preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension'));
        }

        return true;
    }

    public function graphicsMagick($file, $preview_path): bool
    {
        $frame_count = 1;
        $results = nel_exec('gm identify ' . escapeshellarg($file->data('location')));

        if ($results['result_code'] === 0)
        {
            $frame_count = count($results['output']);
        }

        $this->setPreviewDimensions($file);
        $this->setPreviewName($file);

        if ($this->domain->setting('animated_preview') && $frame_count > 1)
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
                            $file->data('preview_height'), $this->compressionValue(),
                            escapeshellarg(
                                    $preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension')));
        }

        $results = nel_exec($resize_command); // TODO: Proper error
        chmod($preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension'),
                octdec(NEL_FILES_PERM));
        return true;
    }

    public function gmagick($file, $preview_path): bool
    {
        $image = new \Gmagick($file->data('location'));
        $frame_count = $image->getNumberImages();

        $this->setPreviewDimensions($file);
        $this->setPreviewName($file);

        if ($this->domain->setting('animated_preview') && $frame_count > 1)
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
            $image->setCompressionQuality($this->compressionValue());
            $image->setFormat($this->outputFormat());
            $image->writeImage($preview_path . $file->data('preview_name') . '.' . $file->data('preview_extension'),
                    false);
        }

        return true;
    }

    public function gd($file, $preview_path): bool
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

        $this->setPreviewDimensions($file);
        $preview = imagecreatetruecolor($file->data('preview_width'), $file->data('preview_height'));

        if ($preview === false)
        {
            return false;
        }

        $this->setPreviewName($file);
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

    private function setPreviewDimensions(Upload $file): void
    {
        if (empty($file->data('display_width')) || empty($file->data('display_height')))
        {
            return;
        }

        $ratio = min(($this->domain->setting('max_preview_height') / $file->data('display_height')),
                ($this->domain->setting('max_preview_width') / $file->data('display_width')));
        $file->changeData('preview_width',
                ($ratio < 1) ? intval($ratio * $file->data('display_width')) : $file->data('display_width'));
        $file->changeData('preview_height',
                ($ratio < 1) ? intval($ratio * $file->data('display_height')) : $file->data('display_height'));
    }

    private function setPreviewName(Upload $file): void
    {
        $filename_suffix = '_preview';
        $filename_maxlength = 255 - strlen($file->data('extension')) - 1 - strlen($filename_suffix);
        $trimmed_filename = substr($file->data('filename'), 0, $filename_maxlength);
        $file->changeData('preview_name', $trimmed_filename . $filename_suffix);
        $file->changeData('preview_extension', $this->destinationExtension());
    }

    private function compressionValue(): int
    {
        if ($this->domain->setting('use_png_preview'))
        {
            return intval($this->domain->setting('png_compression'));
        }

        return intval($this->domain->setting('jpeg_quality'));
    }

    private function destinationExtension(): string
    {
        if ($this->domain->setting('use_png_preview'))
        {
            return 'png';
        }

        return 'jpg';
    }

    private function outputFormat(): string
    {
        if ($this->domain->setting('use_png_preview'))
        {
            return 'png';
        }

        return 'jpeg';
    }

    private function nullPreview(Upload $file): void
    {
        $file->changeData('preview_name', null);
        $file->changeData('preview_extension', null);
        $file->changeData('preview_width', null);
        $file->changeData('preview_height', null);
    }
}