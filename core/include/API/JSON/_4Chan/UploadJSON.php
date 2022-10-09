<?php

namespace Nelliel\API\JSON\_4Chan;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\API\JSON\JSON;
use Nelliel\Content\Upload;
use SplFileInfo;

class UploadJSON extends JSON
{
    private $upload;

    function __construct(Upload $upload)
    {
        $this->upload = $upload;
    }

    protected function generate(): void
    {
        $post = $this->upload->getParent();
        $this->raw_data = array();
        $this->raw_data['tim'] = (int) ($post->data('post_time') .
            str_pad($post->data('post_time_milli'), 3, '0', STR_PAD_LEFT));
        $file_info = new SplFileInfo($this->upload->data('original_filename'));
        $this->raw_data['filename'] = $file_info->getBasename('.' . $file_info->getExtension());
        $this->raw_data['ext'] = $file_info->getExtension();
        $this->raw_data['fsize'] = $this->upload->data('filesize');
        $this->raw_data['md5'] = base64_encode(hex2bin($this->upload->data('md5'))); // Why didn't they just use the hex?
        $this->raw_data['w'] = $this->upload->data('display_width');
        $this->raw_data['h'] = $this->upload->data('display_height');
        $this->raw_data['tn_w'] = $this->upload->data('preview_width');
        $this->raw_data['tn_h'] = $this->upload->data('preview_height');

        if ($this->upload->data('deleted')) {
            $this->raw_data['filedeleted'] = 1;
        }

        if ($this->upload->data('spoiler')) {
            $this->raw_data['spoiler'] = 1;
        }

        $this->json = json_encode($this->raw_data);
        $this->needs_update = false;
    }
}