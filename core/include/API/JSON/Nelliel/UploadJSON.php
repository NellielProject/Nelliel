<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON\Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\API\JSON\JSON;
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
        $this->raw_data = array();
        $this->raw_data['parent_thread'] = $this->upload->data('parent_thread') ?? 0;
        $this->raw_data['post_ref'] = $this->upload->data('post_ref') ?? 0;
        $this->raw_data['upload_order'] = $this->upload->data('upload_order');
        $this->raw_data['category'] = $this->upload->data('category');
        $this->raw_data['format'] = $this->upload->data('format');

        if ($is_file) {
            $this->raw_data['mime'] = $this->upload->data('mime');
            $this->raw_data['filename'] = $this->upload->data('filename');
            $this->raw_data['extension'] = $this->upload->data('extension');
            $this->raw_data['original_filename'] = $this->upload->data('original_filename');
            $this->raw_data['display_width'] = $this->upload->data('display_width');
            $this->raw_data['display_height'] = $this->upload->data('display_height');
        }

        $has_preview = !nel_true_empty($this->upload->data('static_preview_name')) ||
            !nel_true_empty($this->upload->data('animated_preview_name'));

        if ($has_preview) {
            if (!nel_true_empty($this->upload->data('static_preview_name'))) {
                $this->raw_data['static_preview_name'] = $this->upload->data('static_preview_name');
            }

            if (!nel_true_empty($this->upload->data('animated_preview_name'))) {
                $this->raw_data['animated_preview_name'] = $this->upload->data('animated_preview_name');
            }

            $this->raw_data['preview_width'] = $this->upload->data('preview_width') ?? 0;
            $this->raw_data['preview_height'] = $this->upload->data('preview_height') ?? 0;
        }

        if ($is_file) {
            $this->raw_data['filesize'] = $this->upload->data('filesize');
            $this->raw_data['md5'] = $this->upload->data('md5');
            $this->raw_data['sha1'] = $this->upload->data('sha1');
            $this->raw_data['sha256'] = $this->upload->data('sha256');
            $this->raw_data['sha512'] = $this->upload->data('sha512');
        }

        if (!nel_true_empty($this->upload->data('embed_url'))) {
            $this->raw_data['embed_url'] = $this->upload->data('embed_url');
        }

        $this->raw_data['spoiler'] = $this->upload->data('spoiler');
        $this->raw_data['deleted'] = $this->upload->data('deleted');
        $this->raw_data['exif'] = $this->upload->data('exif');

        $this->json = json_encode($this->raw_data);
        $this->needs_update = false;
    }
}
