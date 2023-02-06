<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\Upload;

class UploadJSON extends JSON
{
    private $upload;

    function __construct(Upload $upload)
    {
        $this->upload = $upload;
    }

    protected function generate(): void
    {
        $is_file = nel_true_empty($this->upload->data('embed_url'));
        $raw_data = array();
        $raw_data['parent_thread'] = $this->upload->data('parent_thread') ?? 0;
        $raw_data['post_ref'] = $this->upload->data('post_ref') ?? 0;
        $raw_data['upload_order'] = $this->upload->data('upload_order');
        $raw_data['category'] = $this->upload->data('category');
        $raw_data['format'] = $this->upload->data('format');

        if ($is_file) {
            $raw_data['mime'] = $this->upload->data('mime');
            $raw_data['filename'] = $this->upload->data('filename');
            $raw_data['extension'] = $this->upload->data('extension');
            $raw_data['original_filename'] = $this->upload->data('original_filename');
            $raw_data['display_width'] = $this->upload->data('display_width');
            $raw_data['display_height'] = $this->upload->data('display_height');
        }

        $has_preview = !nel_true_empty($this->upload->data('static_preview_name')) ||
            !nel_true_empty($this->upload->data('animated_preview_name'));

        if ($has_preview) {
            if (!nel_true_empty($this->upload->data('static_preview_name'))) {
                $raw_data['static_preview_name'] = $this->upload->data('static_preview_name');
            }

            if (!nel_true_empty($this->upload->data('animated_preview_name'))) {
                $raw_data['animated_preview_name'] = $this->upload->data('animated_preview_name');
            }

            $raw_data['preview_width'] = $this->upload->data('preview_width') ?? 0;
            $raw_data['preview_height'] = $this->upload->data('preview_height') ?? 0;
        }

        if ($is_file) {
            $raw_data['filesize'] = $this->upload->data('filesize');
            $raw_data['file_hashes']['md5'] = $this->upload->data('md5');
            $raw_data['file_hashes']['sha1'] = $this->upload->data('sha1');
            $raw_data['file_hashes']['sha256'] = $this->upload->data('sha256');
            $raw_data['file_hashes']['sha512'] = $this->upload->data('sha512');
        }

        if (!nel_true_empty($this->upload->data('embed_url'))) {
            $raw_data['embed_url'] = $this->upload->data('embed_url');
        }

        $raw_data['spoiler'] = $this->upload->data('spoiler');
        $raw_data['deleted'] = $this->upload->data('deleted');
        $raw_data['exif'] = $this->upload->data('exif');

        $raw_data = nel_plugins()->processHook('nel-in-after-upload-json', [$this->upload], $raw_data);
        $this->raw_data = $raw_data;
        $this->needs_update = false;
    }
}
