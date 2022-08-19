<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\Upload;

class UploadJSON extends JSON
{

    function __construct(Upload $upload = null)
    {
        if (!is_null($upload)) {
            $this->generateFromContent($upload);
        }
    }

    public function generateFromContent(Upload $upload): void
    {
        $this->raw_data = array();
        $this->raw_data['parent_thread'] = $upload->data('parent_thread');
        $this->raw_data['post_ref'] = $upload->data('post_ref');
        $this->raw_data['upload_order'] = $upload->data('upload_order');
        $this->raw_data['category'] = $upload->data('category');
        $this->raw_data['format'] = $upload->data('format');
        $this->raw_data['mime'] = $upload->data('mime');
        $this->raw_data['filename'] = $upload->data('filename');
        $this->raw_data['extension'] = $upload->data('extension');
        $this->raw_data['display_width'] = $upload->data('display_width');
        $this->raw_data['display_height'] = $upload->data('display_height');
        $this->raw_data['static_preview_name'] = $upload->data('static_preview_name');
        $this->raw_data['animated_preview_name'] = $upload->data('animated_preview_name');
        $this->raw_data['preview_width'] = $upload->data('preview_width');
        $this->raw_data['preview_height'] = $upload->data('preview_height');
        $this->raw_data['filesize'] = $upload->data('filesize');
        $this->raw_data['md5'] = $upload->data('md5');
        $this->raw_data['sha1'] = $upload->data('sha1');
        $this->raw_data['sha256'] = $upload->data('sha256');
        $this->raw_data['sha512'] = $upload->data('sha512');
        $this->raw_data['embed_url'] = $upload->data('embed_url');
        $this->raw_data['spoiler'] = $upload->data('spoiler');
        $this->raw_data['deleted'] = $upload->data('deleted');
        $this->raw_data['exif'] = $upload->data('exif');
        $this->generate();
    }

    protected function generate(): void
    {
        $this->json = json_encode($this->raw_data);
        $this->json_needs_update = false;
    }
}
