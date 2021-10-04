<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\Upload;
use Nelliel\Utility\FileHandler;

class UploadJSON extends JSON
{

    function __construct(Upload $upload, FileHandler $file_handler)
    {
        $this->file_handler = $file_handler;
        $this->source = $upload;
    }

    public function generate(): void
    {
        $this->raw_data = array();
        $this->raw_data['parent_thread'] = $this->source->data('parent_thread');
        $this->raw_data['post_ref'] = $this->source->data('post_ref');
        $this->raw_data['upload_order'] = $this->source->data('upload_order');
        $this->raw_data['category'] = $this->source->data('category');
        $this->raw_data['format'] = $this->source->data('format');
        $this->raw_data['mime'] = $this->source->data('mime');
        $this->raw_data['filename'] = $this->source->data('filename');
        $this->raw_data['extension'] = $this->source->data('extension');
        $this->raw_data['display_width'] = $this->source->data('display_width');
        $this->raw_data['display_height'] = $this->source->data('display_height');
        $this->raw_data['static_preview_name'] = $this->source->data('static_preview_name');
        $this->raw_data['animated_preview_name'] = $this->source->data('animated_preview_name');
        $this->raw_data['preview_width'] = $this->source->data('preview_width');
        $this->raw_data['preview_height'] = $this->source->data('preview_height');
        $this->raw_data['filesize'] = $this->source->data('filesize');
        $this->raw_data['md5'] = $this->source->data('md5');
        $this->raw_data['sha1'] = $this->source->data('sha1');
        $this->raw_data['sha256'] = $this->source->data('sha256');
        $this->raw_data['sha512'] = $this->source->data('sha512');
        $this->raw_data['embed_url'] = $this->source->data('embed_url');
        $this->raw_data['spoiler'] = $this->source->data('spoiler');
        $this->raw_data['deleted'] = $this->source->data('deleted');
        $this->raw_data['exif'] = $this->source->data('exif');
        $this->json = json_encode($this->raw_data);
        $this->generated = true;
    }

    public function write(): void
    {
        ;
    }
}
