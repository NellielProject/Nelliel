<?php
declare(strict_types = 1);

namespace Nelliel\NewPost;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\Upload;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainSite;

class Previews
{
    private Domain $domain;
    private DomainSite $site_domain;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->site_domain = nel_get_cached_domain(Domain::SITE);
    }

    public function generate($files, $preview_path)
    {
        $file_handler = nel_utilities()->fileHandler();
        $i = 0;
        $files_count = count($files);

        for ($i = 0; $i < $files_count; $i ++) {
            if ($files[$i]->getData('display_width') <= 0 || $files[$i]->getData('display_height') <= 0) {
                continue;
            }

            clearstatcache();
            $file_handler->createDirectory($preview_path);
            $preview_made = false;

            if ($this->domain->setting('use_copy_for_small_preview') &&
                $files[$i]->getData('display_width') <= $this->domain->setting('max_preview_width') &&
                $files[$i]->getData('display_height') <= $this->domain->setting('max_preview_height')) {
                $preview_made = $this->useOriginal($files[$i], $preview_path);
            }

            $magicks = nel_magick_available();
            $graphics_handler = $this->site_domain->setting('graphics_handler');

            // We favor command line if available as it tends to work better with more flexibility
            if (!$preview_made && $graphics_handler === 'GraphicsMagick') {
                if (in_array('graphicsmagick', $magicks)) {
                    $preview_made = $this->graphicsMagick($files[$i], $preview_path);
                } else if (in_array('gmagick', $magicks)) {
                    $preview_made = $this->gmagick($files[$i], $preview_path);
                }
            }

            if (!$preview_made && $graphics_handler === 'ImageMagick') {
                if (in_array('imagemagick', $magicks)) {
                    $preview_made = $this->imageMagick($files[$i], $preview_path);
                } else if (in_array('imagick', $magicks)) {
                    $preview_made = $this->imagick($files[$i], $preview_path);
                }
            }

            if (!$preview_made && $graphics_handler === 'GD') {
                $preview_made = $this->gd($files[$i], $preview_path);
            }

            if (!$preview_made) {
                $this->nullPreview($files[$i]);
            }
        }

        return $files;
    }

    public function useOriginal(Upload $file, $preview_path): bool
    {
        $this->setPreviewDimensions($file);

        if ($this->generateStatic($file) &&
            ($file->getData('format') === 'jpeg' || $file->getData('format') === 'png' ||
            $file->getData('format') === 'webp')) {
            $static_preview_name = $this->staticPreviewName($file);
            $has_static = $this->deduplicate($file, $static_preview_name);

            if (!$has_static) {
                $has_static = copy($file->getData('location'), $preview_path . $static_preview_name);
            }

            if ($has_static) {
                $file->changeData('static_preview_name', $static_preview_name);
                return true;
            }
        }

        if ($this->generateAnimated($file) && $file->getData('format') === 'gif') {
            $animated_preview_name = $this->animatedPreviewName($file);
            $has_animated = $this->deduplicate($file, $animated_preview_name);

            if (!$has_animated) {
                $has_animated = copy($file->getData('location'), $preview_path . $animated_preview_name);
            }

            if ($has_animated) {
                $file->changeData('animated_preview_name', $animated_preview_name);
                return true;
            }
        }

        return false;
    }

    public function imageMagick(Upload $file, $preview_path): bool
    {
        $frame_count = 1;
        $results = nel_exec('identify ' . escapeshellarg($file->getData('location')));
        $static_preview_name = $this->staticPreviewName($file);
        $animated_preview_name = $this->animatedPreviewName($file);
        $has_static = $this->deduplicate($file, $static_preview_name);
        $has_animated = $this->deduplicate($file, $animated_preview_name);

        if ($results['result_code'] === 0) {
            $frame_count = count($results['output']);
        }

        $this->setPreviewDimensions($file);

        if ($this->generateStatic($file) && !$has_static) {
            $resize_command = 'convert ' .
                sprintf($this->site_domain->setting('imagemagick_args'),
                    escapeshellarg($file->getData('location') . '[0]'), $file->getData('preview_width'),
                    $file->getData('preview_height'), $this->compressionValue(),
                    escapeshellarg($preview_path . $static_preview_name));
            $results = nel_exec($resize_command);
            $has_static = $results['result_code'] === 0;
        }

        if ($this->generateAnimated($file) && !$has_animated && $frame_count > 1) {
            $limit = '[0-' . $this->frameLimit() . ']';
            $resize_command = 'convert ' .
                sprintf($this->site_domain->setting('imagemagick_animated_args'),
                    escapeshellarg($file->getData('location') . $limit), $file->getData('preview_width'),
                    $file->getData('preview_height'), escapeshellarg($preview_path . $animated_preview_name));
            $results = nel_exec($resize_command);
            $has_animated = $results['result_code'] === 0;
        }

        if ($has_static) {
            $file->changeData('static_preview_name', $static_preview_name);
            chmod($preview_path . $file->getData('static_preview_name'), octdec(NEL_FILES_PERM));
        }

        if ($has_animated) {
            $file->changeData('animated_preview_name', $animated_preview_name);
            chmod($preview_path . $file->getData('animated_preview_name'), octdec(NEL_FILES_PERM));
        }

        return $has_static || $has_animated;
    }

    public function imagick(Upload $file, string $preview_path): bool
    {
        $image = new \Imagick($file->getData('location'));
        $frame_count = $image->getnumberimages();
        $static_preview_name = $this->staticPreviewName($file);
        $animated_preview_name = $this->animatedPreviewName($file);
        $has_static = $this->deduplicate($file, $static_preview_name);
        $has_animated = $this->deduplicate($file, $animated_preview_name);

        $exif = $file->getData('temp_exif');

        if (is_array($exif)) {
            $correct = $this->correctEXIFOrientation($exif);

            if ($correct['flip_horizontal']) {
                $image->transverseimage();
            }

            $image->rotateimage('#000000', $correct['magick_rotate']);
        }

        $file->changeData('display_width', $image->getimagewidth());
        $file->changeData('display_height', $image->getimageheight());
        $this->setPreviewDimensions($file);

        if ($this->generateStatic($file) && !$has_static) {
            $image->thumbnailimage($file->getData('preview_width'), $file->getData('preview_height'));
            $image->setimagecompressionquality($this->compressionValue());
            $image->setformat($this->domain->setting('static_preview_format'));
            $has_static = $image->writeimage($preview_path . $static_preview_name);
        }

        if ($this->generateAnimated($file) && !$has_animated && $frame_count > 1) {
            $image = $image->coalesceimages();
            $limit = $this->frameLimit();

            // Straight thumbnail works for simple animations but not complex ones so we process frames individually
            for ($i = 0; $i < $limit; $i ++) {
                $image[$i]->thumbnailimage($file->getData('preview_width'), $file->getData('preview_height'));
            }

            $image->setformat($this->domain->setting('animated_preview_format'));
            $has_animated = $image->writeimages($preview_path . $animated_preview_name, true);
        }

        if ($has_static) {
            $file->changeData('static_preview_name', $static_preview_name);
        }

        if ($has_animated) {
            $file->changeData('animated_preview_name', $animated_preview_name);
        }

        return $has_static || $has_animated;
    }

    public function graphicsMagick(Upload $file, string $preview_path): bool
    {
        $frame_count = 1;
        $results = nel_exec('gm identify ' . escapeshellarg($file->getData('location')));
        $static_preview_name = $this->staticPreviewName($file);
        $animated_preview_name = $this->animatedPreviewName($file);
        $has_static = $this->deduplicate($file, $static_preview_name);
        $has_animated = $this->deduplicate($file, $animated_preview_name);

        if ($results['result_code'] === 0) {
            $frame_count = count($results['output']);
        }

        $this->setPreviewDimensions($file);

        if ($this->generateStatic($file) && !$has_static) {
            $resize_command = 'gm convert ' .
                sprintf($this->site_domain->setting('graphicsmagick_args'),
                    escapeshellarg($file->getData('location') . '[0]'), $file->getData('preview_width'),
                    $file->getData('preview_height'), $this->compressionValue(),
                    escapeshellarg($preview_path . $static_preview_name));
            $results = nel_exec($resize_command);
            $has_static = $results['result_code'] === 0;
        }

        if ($this->generateAnimated($file) && !$has_animated && $frame_count > 1) {
            $limit = '[0-' . $this->frameLimit() . ']';
            $resize_command = 'gm convert ' .
                sprintf($this->site_domain->setting('graphicsmagick_animated_args'),
                    escapeshellarg($file->getData('location') . $limit), $file->getData('preview_width'),
                    $file->getData('preview_height'), escapeshellarg($preview_path . $animated_preview_name));
            $results = nel_exec($resize_command);
            $has_animated = $results['result_code'] === 0;
        }

        if ($has_static) {
            $file->changeData('static_preview_name', $static_preview_name);
            chmod($preview_path . $file->getData('static_preview_name'), octdec(NEL_FILES_PERM));
        }

        if ($has_animated) {
            $file->changeData('animated_preview_name', $animated_preview_name);
            chmod($preview_path . $file->getData('animated_preview_name'), octdec(NEL_FILES_PERM));
        }

        return $has_static || $has_animated;
    }

    public function gmagick(Upload $file, string $preview_path): bool
    {
        $image = new \Gmagick($file->getData('location'));
        $frame_count = $image->getNumberImages();
        $static_preview_name = $this->staticPreviewName($file);
        $animated_preview_name = $this->animatedPreviewName($file);
        $has_static = $this->deduplicate($file, $static_preview_name);
        $has_animated = $this->deduplicate($file, $animated_preview_name);

        $exif = $file->getData('temp_exif');

        if (is_array($exif)) {
            $correct = $this->correctEXIFOrientation($exif);

            if ($correct['flip_horizontal']) {
                $image->flopimage();
            }

            $image->rotateimage('#000000', $correct['magick_rotate']);
        }

        $file->changeData('display_width', $image->getimagewidth());
        $file->changeData('display_height', $image->getimageheight());
        $this->setPreviewDimensions($file);

        if ($this->generateStatic($file) && !$has_static) {
            $image->thumbnailimage($file->getData('preview_width'), $file->getData('preview_height'));
            $image->setCompressionQuality($this->compressionValue());
            $image->setFormat($this->domain->setting('static_preview_format'));
            $has_static = $image->writeImage($preview_path . $static_preview_name, false);
        }

        if ($this->generateAnimated($file) && !$has_animated && $frame_count > 1) {
            $image = $image->coalesceImages();
            $limit = $this->frameLimit();

            // Straight thumbnail works for simple animations but not complex ones so we process frames individually
            for ($i = 0; $i < $limit; $i ++) {
                $image[$i]->thumbnailimage($file->getData('preview_width'), $file->getData('preview_height'));
            }

            $image->setFormat($this->domain->setting('animated_preview_format'));
            $has_animated = $image->writeImage($preview_path . $animated_preview_name, true);
        }

        if ($has_static) {
            $file->changeData('static_preview_name', $static_preview_name);
        }

        if ($has_animated) {
            $file->changeData('animated_preview_name', $animated_preview_name);
        }

        return $has_static || $has_animated;
    }

    public function gd(Upload $file, string $preview_path): bool
    {
        if (!$this->generateStatic($file)) {
            return false;
        }

        $has_static = false;
        $gd_test = gd_info();

        if ($file->getData('format') === 'jpeg' && $gd_test['JPEG Support']) {
            $image = imagecreatefromjpeg($file->getData('location'));
        } else if ($file->getData('format') === 'gif' && $gd_test['GIF Read Support']) {
            $image = imagecreatefromgif($file->getData('location'));
        } else if ($file->getData('format') === 'png' && $gd_test['PNG Support']) {
            $image = imagecreatefrompng($file->getData('location'));
        } else if ($file->getData('format') === 'webp' && $gd_test['WebP Support']) {
            $image = imagecreatefromwebp($file->getData('location'));
        } else if ($file->getData('format') === 'bmp' && $gd_test['BMP Support']) {
            $image = imagecreatefrombmp($file->getData('location'));
        } else if ($file->getData('format') === 'wbmp' && $gd_test['WBMP Support']) {
            $image = imagecreatefromwbmp($file->getData('location'));
        } else if ($file->getData('format') === 'xbm' && $gd_test['XBM Support']) {
            $image = imagecreatefromxbm($file->getData('location'));
        } else if ($file->getData('format') === 'xpm' && $gd_test['XPM Support']) {
            $image = imagecreatefromxpm($file->getData('location'));
        } else if ($file->getData('format') === 'tga' && $gd_test['TGA Read Support']) {
            $image = imagecreatefromtga($file->getData('location'));
        } else if ($file->getData('format') === 'avif' && $gd_test['AVIF Support']) {
            $image = imagecreatefromtga($file->getData('location'));
        } else {
            return false;
        }

        $exif = $file->getData('temp_exif');

        if (is_array($exif)) {
            $correct = $this->correctEXIFOrientation($exif);

            if ($correct['flip_horizontal']) {
                $image = imageflip($image, IMG_FLIP_HORIZONTAL);
            }

            $image = imagerotate($image, $correct['gd_rotate'], 0);
        }

        $file->changeData('display_width', imagesx($image));
        $file->changeData('display_height', imagesy($image));
        $this->setPreviewDimensions($file);
        $preview = imagecreatetruecolor($file->getData('preview_width'), $file->getData('preview_height'));

        if ($preview === false) {
            return false;
        }

        $static_preview_name = $this->staticPreviewName($file);
        $has_static = $this->deduplicate($file, $static_preview_name);
        imagecolortransparent($preview, imagecolortransparent($image));
        imagealphablending($preview, false);
        imagesavealpha($preview, true);
        imagecopyresampled($preview, $image, 0, 0, 0, 0, $file->getData('preview_width'),
            $file->getData('preview_height'), $file->getData('display_width'), $file->getData('display_height'));

        if (!$has_static) {
            switch ($this->domain->setting('static_preview_format')) {
                case 'jpg':
                    $has_static = imagejpeg($preview, $preview_path . $static_preview_name, $this->compressionValue());
                    break;

                case 'png':
                    $has_static = imagepng($preview, $preview_path . $static_preview_name);
                    break;

                case 'gif':
                    $has_static = imagegif($preview, $preview_path . $static_preview_name);
                    break;

                case 'webp':
                    $has_static = imagewebp($preview, $preview_path . $static_preview_name, $this->compressionValue());
                    break;
            }
        }

        if ($has_static) {
            $file->changeData('static_preview_name', $static_preview_name);
        }

        return $has_static;
    }

    private function correctEXIFOrientation(array $exif): array
    {
        $correct['flip_horizontal'] = false;
        $correct['gd_rotate'] = 0;
        $correct['magick_rotate'] = 0;

        if (!empty($exif['IFD0']['Orientation'])) {
            switch ($exif['IFD0']['Orientation']) {
                case 2:
                    $correct['flip_horizontal'] = true;
                    break;

                case 4:
                    $correct['flip_horizontal'] = true;

                case 3:
                    $correct['gd_rotate'] = 180;
                    $correct['magick_rotate'] = 180;
                    break;

                case 5:
                    $correct['flip_horizontal'] = true;

                case 6:
                    $correct['gd_rotate'] = 270;
                    $correct['magick_rotate'] = 90;
                    break;

                case 7:
                    $correct['flip_horizontal'] = true;

                case 8:
                    $correct['gd_rotate'] = 90;
                    $correct['magick_rotate'] = 270;
                    break;
            }
        }

        return $correct;
    }

    private function setPreviewDimensions(Upload $file): void
    {
        if (empty($file->getData('display_width')) || empty($file->getData('display_height'))) {
            return;
        }

        $ratio = min(($this->domain->setting('max_preview_height') / $file->getData('display_height')),
            ($this->domain->setting('max_preview_width') / $file->getData('display_width')));
        $preview_width = ($ratio < 1) ? intval($ratio * $file->getData('display_width')) : $file->getData(
            'display_width');
        $preview_height = ($ratio < 1) ? intval($ratio * $file->getData('display_height')) : $file->getData(
            'display_height');
        $file->changeData('preview_width', $preview_width);
        $file->changeData('preview_height', $preview_height);
    }

    private function staticPreviewName(Upload $file): string
    {
        $suffix = '_' . $file->getData('extension') . '_spreview';
        $filename_maxlength = 255 - utf8_strlen($this->domain->setting('static_preview_format')) - utf8_strlen($suffix) -
            utf8_strlen($file->getData('extension')) - 2;
        $preview_name = utf8_substr($file->getData('filename'), 0, $filename_maxlength) . $suffix . '.' .
            $this->domain->setting('static_preview_format');
        return $preview_name;
    }

    private function animatedPreviewName(Upload $file): string
    {
        $suffix = '_' . $file->getData('extension') . '_apreview';
        $filename_maxlength = 255 - utf8_strlen($this->domain->setting('static_preview_format')) - utf8_strlen($suffix) -
            utf8_strlen($file->getData('extension')) - 2;
        $preview_name = utf8_substr($file->getData('filename'), 0, $filename_maxlength) . $suffix . '.' .
            $this->domain->setting('animated_preview_format');
        return $preview_name;
    }

    private function compressionValue(): int
    {
        switch ($this->domain->setting('static_preview_format')) {
            case 'jpg':
                $value = $this->domain->setting('jpeg_quality');
                break;

            case 'webp':
                $value = $this->domain->setting('webp_quality');
                break;

            case 'png':
            case 'gif':
                $value = 0; // Should trigger the default value with magick
        }

        return intval($value);
    }

    private function nullPreview(Upload $file): void
    {
        $file->changeData('static_preview_name', null);
        $file->changeData('animated_preview_name', null);
        $file->changeData('preview_width', null);
        $file->changeData('preview_height', null);
    }

    private function generateStatic(Upload $file): bool
    {
        if (!$this->domain->setting('create_static_preview')) {
            return false;
        }

        if ($this->domain->setting('static_preview_images_only') && $file->getData('category') !== 'graphics') {
            return false;
        }

        return true;
    }

    private function generateAnimated(Upload $file): bool
    {
        if (!$this->domain->setting('create_animated_preview')) {
            return false;
        }

        if ($this->domain->setting('animated_preview_images_only') && $file->getData('category') !== 'graphics') {
            return false;
        }

        return true;
    }

    private function frameLimit(): int
    {
        $limit = intval($this->domain->setting('animated_preview_max_frames')) - 1; // Accounting for zero index

        if ($limit < 1) {
            $limit = 1;
        }

        return $limit;
    }

    private function deduplicate(Upload $file, string $preview_name): bool
    {
        if ($this->domain->setting('file_deduplication')) {
            return false;
        }

        return file_exists($file->previewFilePath() . $preview_name);
    }
}