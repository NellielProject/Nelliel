<?php

namespace Nelliel\API\JSON;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use \Nelliel\Domain;
use \Nelliel\FileHandler;

class JSONContent extends JSONOutput
{

    function __construct(Domain $domain, FileHandler $file_handler)
    {
        $this->domain = $domain;
        $this->file_handler = $file_handler;
        $this->data_array['content'] = array();
    }

    public function prepareData(array $data)
    {
        $content_array = array();
        $this->addIfNotEmpty($content_array, 'parent_thread', $data['parent_thread'], 'integer');
        $content_array['post_ref'] = nel_cast_to_datatype($data['post_ref'], 'integer');
        $content_array['content_order'] = nel_cast_to_datatype($data['content_order'], 'integer');
        $content_array['type'] = nel_cast_to_datatype($data['type'], 'string');
        $content_array['format'] = nel_cast_to_datatype($data['format'], 'string');
        $this->addIfNotEmpty($content_array, 'mime', $data['mime'], 'string');
        $this->addIfNotEmpty($content_array, 'filename', $data['filename'], 'string');
        $this->addIfNotEmpty($content_array, 'extension', $data['extension'], 'string');
        $this->addIfNotEmpty($content_array, 'display_width', $data['display_width'], 'integer');
        $this->addIfNotEmpty($content_array, 'display_height', $data['display_height'], 'integer');
        $this->addIfNotEmpty($content_array, 'preview_name', $data['preview_name'], 'string');
        $this->addIfNotEmpty($content_array, 'preview_extension', $data['preview_extension'], 'string');
        $this->addIfNotEmpty($content_array, 'preview_width', $data['preview_width'], 'integer');
        $this->addIfNotEmpty($content_array, 'preview_height', $data['preview_height'], 'integer');
        $this->addIfNotEmpty($content_array, 'filesize', $data['filesize'], 'integer');
        $this->addIfNotEmpty($content_array, 'md5', bin2hex($data['md5']), 'string');
        $this->addIfNotEmpty($content_array, 'sha1', bin2hex($data['sha1']), 'string');
        $this->addIfNotEmpty($content_array, 'sha256', bin2hex($data['sha256']), 'string');
        $this->addIfNotEmpty($content_array, 'sha512', bin2hex($data['sha512']), 'string');
        $this->addIfNotEmpty($content_array, 'embed_url', $data['embed_url'], 'string');
        $content_array['spoiler'] = nel_cast_to_datatype($data['spoiler'], 'boolean');
        $this->addIfNotEmpty($content_array, 'exif', $data['exif'], 'string');
        $this->addIfNotEmpty($content_array, 'meta', $data['meta'], 'string');
        $content_array = nel_plugins()->processHook('nel-json-prepare-content', [$data], $content_array);
        return $content_array;
    }
}