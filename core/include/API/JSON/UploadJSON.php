<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\Upload;

class UploadJSON extends JSON
{
    private Upload $upload;

    function __construct(Upload $upload)
    {
        $this->upload = $upload;
    }

    protected function generate(): void
    {
        $raw_data = array();
        $is_file = nel_true_empty($this->upload->getData('embed_url'));
        $raw_data['parent_thread'] = $this->upload->getData('parent_thread') ?? 0;
        $raw_data['post_ref'] = $this->upload->getData('post_ref') ?? 0;
        $raw_data['upload_order'] = $this->upload->getData('upload_order');
        $raw_data['category'] = $this->upload->getData('category');
        $raw_data['format'] = $this->upload->getData('format');

        if ($is_file) {
            $raw_data['mime'] = $this->upload->getData('mime');
            $raw_data['filename'] = $this->upload->getData('filename');
            $raw_data['extension'] = $this->upload->getData('extension');

            if ($this->upload->domain()->setting('show_original_name')) {
                $raw_data['original_filename'] = $this->upload->getData('original_filename');
            }

            $raw_data['display_width'] = $this->upload->getData('display_width');
            $raw_data['display_height'] = $this->upload->getData('display_height');
        }

        $has_preview = !nel_true_empty($this->upload->getData('static_preview_name')) ||
            !nel_true_empty($this->upload->getData('animated_preview_name'));
        $preview_visible = $this->upload->getData('static_preview_name') || $this->upload->getData('animated_preview_name');

        if ($has_preview && $preview_visible) {
            if (!nel_true_empty($this->upload->getData('static_preview_name')) &&
                $this->upload->domain()->setting('show_static_preview')) {
                $raw_data['static_preview_name'] = $this->upload->getData('static_preview_name');
            }

            if (!nel_true_empty($this->upload->getData('animated_preview_name')) &&
                $this->upload->domain()->setting('show_animated_preview')) {
                $raw_data['animated_preview_name'] = $this->upload->getData('animated_preview_name');
            }

            $raw_data['preview_width'] = $this->upload->getData('preview_width') ?? 0;
            $raw_data['preview_height'] = $this->upload->getData('preview_height') ?? 0;
        }

        if ($is_file) {
            $raw_data['filesize'] = $this->upload->getData('filesize');
            $raw_data['file_hashes']['md5'] = $this->upload->getData('md5');
            $raw_data['file_hashes']['sha1'] = $this->upload->getData('sha1');
            $raw_data['file_hashes']['sha256'] = $this->upload->getData('sha256');
            $raw_data['file_hashes']['sha512'] = $this->upload->getData('sha512');
        }

        if (!nel_true_empty($this->upload->getData('embed_url'))) {
            $raw_data['embed_url'] = $this->upload->getData('embed_url');
        }

        $raw_data['spoiler'] = $this->upload->getData('spoiler');
        $raw_data['deleted'] = $this->upload->getData('deleted');
        $raw_data['exif'] = $this->upload->getData('exif');

        $raw_data = nel_plugins()->processHook('nel-in-after-upload-json', [$this->upload], $raw_data);
        $this->raw_data = $raw_data;
        $this->needs_update = false;
    }
}
