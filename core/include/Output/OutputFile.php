<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Lamansky\Fraction\Fraction;
use Nelliel\Content\Post;
use Nelliel\Content\Upload;
use Nelliel\Domains\Domain;

class OutputFile extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(Upload $file, Post $post, array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $catalog = $parameters['catalog'] ?? false;
        $first = $parameters['first'] ?? false;
        $multiple = $parameters['multiple'] ?? false;
        $template = ($multiple) ? 'thread/multiple_content' : 'thread/single_content';
        $this->render_data['is_file'] = true;
        $this->render_data['file_container_id'] = 'upload-container-' . $file->contentID()->getIDString();
        $this->render_data['file_content_id'] = $file->contentID()->getIDString();
        $this->render_data['in_modmode'] = $this->session->inModmode($this->domain) && !$this->write_mode;

        if ($this->session->inModmode($this->domain)) {
            $output_modmode_headers = new OutputModmodeHeaders($this->domain, $this->write_mode);
            $this->render_data['file_modmode_options'] = $output_modmode_headers->upload($file);
        }

        $units = $this->domain->setting('scale_upload_filesize_units') ? null : $this->domain->setting(
            'filesize_unit_prefix');
        $this->render_data['display_filesize'] = nel_size_format((int) $file->getData('filesize'),
            $this->domain->setting('display_iec_filesize_units'), $this->domain->setting('binary_filesize_conversion'),
            $this->domain->setting('filesize_precision'), $units);
        $has_dimensions = $file->getData('display_width') > 0 && $file->getData('display_height') > 0;

        if ($has_dimensions) {
            $this->render_data['display_image_dimensions'] = $file->getData('display_width') . ' x ' .
                $file->getData('display_height');

            if ($this->domain->setting('show_display_ratio')) {
                $fraction = new Fraction($file->getData('display_width'), $file->getData('display_height'));
                $this->render_data['display_ratio'] = $fraction->getNumerator() . ':' . $fraction->getDenominator();
            }
        }

        $this->render_data['file_url'] = $file->getURL(false);
        $this->render_data['show_download_link'] = $this->domain->setting('show_download_link');

        if ($this->domain->setting('download_original_name') && !nel_true_empty($file->getData('original_filename'))) {
            $this->render_data['download_filename'] = $file->getData('original_filename');
        } else {
            $this->render_data['download_filename'] = $file->getData('filename') . '.' . $file->getData('extension');
        }

        if ($this->domain->setting('show_original_name') && !nel_true_empty($file->getData('original_filename'))) {
            $display_filename = $file->getData('original_filename');
        } else {
            $display_filename = $file->getData('filename') . '.' . $file->getData('extension');
        }

        if (utf8_strlen($display_filename) > $this->domain->setting('filename_display_length')) {
            $display_filename = utf8_substr($display_filename, 0, $this->domain->setting('filename_display_length')) .
                '...';
        }

        $this->render_data['display_filename'] = $display_filename;

        if (!empty($file->getData('md5'))) {
            $md5_data['metadata'] = 'MD5: ' . $file->getData('md5');
            $this->render_data['file_metadata'][] = $md5_data;
        }

        if (!empty($file->getData('sha1'))) {
            $sha1_data['metadata'] = 'SHA1: ' . $file->getData('sha1');
            $this->render_data['file_metadata'][] = $sha1_data;
        }

        if (!empty($file->getData('sha256'))) {
            $sha256_data['metadata'] = 'SHA256: ' . $file->getData('sha256');
            $this->render_data['file_metadata'][] = $sha256_data;
        }

        if (!empty($file->getData('sha512'))) {
            $sha512_data['metadata'] = 'SHA512: ' . $file->getData('sha512');
            $this->render_data['file_metadata'][] = $sha512_data;
        }

        if ($catalog) {
            $first_full_size = $first && $this->domain->setting('catalog_first_preview_full_size');
            $max_width = ($multiple && !$first_full_size) ? $this->domain->setting(
                'catalog_max_multi_preview_display_width') : $this->domain->setting('catalog_max_preview_display_width');
            $max_height = ($multiple && !$first_full_size) ? $this->domain->setting(
                'catalog_max_multi_preview_display_height') : $this->domain->setting(
                'catalog_max_preview_display_height');
        } else {
            if ($post->getData('op')) {
                $max_width = ($multiple) ? $this->domain->setting('max_op_multi_display_width') : $this->domain->setting(
                    'max_op_preview_display_width');
                $max_height = ($multiple) ? $this->domain->setting('max_op_multi_display_height') : $this->domain->setting(
                    'max_op_preview_display_height');
            } else {
                $max_width = ($multiple) ? $this->domain->setting('max_reply_multi_display_width') : $this->domain->setting(
                    'max_reply_preview_display_width');
                $max_height = ($multiple) ? $this->domain->setting('max_reply_multi_display_height') : $this->domain->setting(
                    'max_reply_preview_display_height');
            }
        }

        $this->render_data['max_width'] = $max_width;
        $this->render_data['max_height'] = $max_height;
        $preview_size_not_zero = $file->getData('preview_width') > 0 && $file->getData('preview_height') > 0;
        $has_static_preview = !nel_true_empty($file->getData('static_preview_name')) && $preview_size_not_zero;
        $has_animated_preview = !nel_true_empty($file->getData('animated_preview_name')) && $preview_size_not_zero;
        $preview_type = null;

        $this->render_data['content_links_hide_file']['content_id'] = $file->contentID()->getIDString();
        $this->render_data['file_options'][] = $this->render_data['content_links_hide_file'];
        $this->render_data['content_links_show_upload_meta']['content_id'] = $file->contentID()->getIDString();
        $this->render_data['content_links_show_upload_meta']['query_class'] = 'js-hide-file';
        $this->render_data['file_options'][] = $this->render_data['content_links_show_upload_meta'];

        if ($file->getData('deleted') && $this->domain->setting('display_deleted_placeholder')) {
            if (nel_is_absolute_url($this->domain->setting('image_deleted_embed'))) {
                $this->render_data['deleted_url'] = $this->domain->setting('image_deleted_file');
            } else {
                $this->render_data['deleted_url'] = NEL_ASSETS_WEB_PATH . $this->domain->setting('image_deleted_file');
            }

            $preview_type = 'image';
        }

        if (is_null($preview_type) && $file->getData('spoiler')) {
            if (nel_is_absolute_url($this->domain->setting('image_deleted_embed'))) {
                $this->render_data['preview_url'] = $this->domain->setting('image_spoiler_cover');
            } else {
                $this->render_data['preview_url'] = NEL_ASSETS_WEB_PATH . $this->domain->setting('image_spoiler_cover');
            }

            if (!nel_true_empty($this->domain->setting('spoiler_display_name'))) {
                $this->render_data['display_filename'] = $this->domain->setting('spoiler_display_name');
            }

            $preview_type = 'image';
        }

        if ($file->getData('category') === 'video') {
            $this->render_data['alt_tag'] = "video";

            if (is_null($preview_type) &&
                (!$this->domain->setting('show_video_preview') || (!$has_static_preview && !$has_animated_preview))) {
                if ($this->domain->setting('embed_video_files') &&
                    ($file->getData('format') == 'webm' || $file->getData('format') == 'mpeg4')) {
                    $this->render_data['video_width'] = $max_width;
                    $this->render_data['video_height'] = $max_height;
                    $this->render_data['mime_type'] = $file->getData('mime');
                    $this->render_data['video_url'] = $this->render_data['file_url'];
                    $this->render_data['video_preview'] = true;
                    $preview_type = 'video';
                }
            }
        }

        if ($file->getData('category') === 'graphics') {
            $this->render_data['alt_tag'] = "img";
        }

        if (is_null($preview_type)) {
            if ($this->domain->setting('show_static_preview') && $has_static_preview) {
                $preview_name = $file->getData('static_preview_name');
            }

            if ($this->domain->setting('show_animated_preview') && $has_animated_preview) {
                $preview_name = $file->getData('animated_preview_name');
            }

            if (!empty($preview_name)) {
                if ($this->domain->setting('use_original_as_preview')) {
                    $preview_width = $file->getData('display_width');
                    $preview_height = $file->getData('display_height');
                    $this->render_data['preview_url'] = $this->render_data['file_url'];
                } else {
                    $preview_width = $file->getData('preview_width');
                    $preview_height = $file->getData('preview_height');
                    $this->render_data['preview_url'] = $file->previewWebPath() . rawurlencode($preview_name);
                }

                $this->render_data['preview_alt_text'] = sprintf(__('Preview of %s'),
                    $this->render_data['display_filename']);
                $preview_type = 'image';
            } else if ($this->domain->setting('use_file_image')) {
                $image_set = $this->domain->frontEndData()->getImageSet($this->domain->setting('filetype_image_set'));
                $type = utf8_strtolower($file->getData('category'));
                $format = utf8_strtolower($file->getData('format'));
                $web_path = $image_set->getWebPath('filetype', $format, true);

                if ($web_path === '') {
                    $web_path = $image_set->getWebPath('filetype', 'generic-' . $type, true);

                    if ($web_path === '') {
                        $web_path = $image_set->getWebPath('filetype', 'generic', true);
                    }
                }

                $this->render_data['preview_url'] = $web_path;
                $this->render_data['preview_alt_text'] = sprintf(__('%s filetype image'), $format);
                $preview_type = 'unsized_image';
            }

            if (!is_null($preview_type) && $preview_type === 'image') {
                $ratio = min(($max_height / $preview_height), ($max_width / $preview_width));
                $this->render_data['preview_width'] = ($ratio < 1) ? intval($ratio * $preview_width) : $file->getData(
                    'preview_width');
                $this->render_data['preview_height'] = ($ratio < 1) ? intval($ratio * $preview_height) : $file->getData(
                    'preview_height');
            }
        }

        if (!is_null($preview_type)) {
            $this->render_data['other_dims'] = 'w' . $file->getData('display_width') . 'h' .
                $file->getData('display_height');
            $this->render_data['other_loc'] = $this->render_data['file_url'];
            $this->render_data['image_preview'] = $preview_type === 'image';
            $this->render_data['video_preview'] = $preview_type === 'video';
            $this->render_data['unsized_image_preview'] = $preview_type === 'unsized_image';
        }

        $all_content_ops = $this->domain->frontEndData()->getAllContentOps(true);
        $enabled_content_ops = json_decode($this->domain->setting('enabled_content_ops') ?? '', true);

        foreach ($all_content_ops as $content_op) {
            if (!in_array($content_op->id(), $enabled_content_ops)) {
                continue;
            }

            if ($content_op->getData('images_only') && $file->getData('category') !== 'graphics') {
                continue;
            }

            $displayed_op = array();
            $displayed_op['url'] = $content_op->getData('url') . NEL_URL_BASE . $this->render_data['file_url'];
            $displayed_op['text'] = $content_op->getData('label');
            $this->render_data['content_ops'][] = $displayed_op;
        }

        $output = $this->output($template, $data_only, true, $this->render_data);
        return $output;
    }
}