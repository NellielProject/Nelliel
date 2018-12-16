<?php

namespace Nelliel\API\JSON;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class JSONContent extends JSONBase
{

    function __construct($domain, $file_handler)
    {
        $this->domain = $domain;
        $this->file_handler = $file_handler;
    }

    public function prepareData($data, $store = false)
    {
        $content_array = array();
        $content_array['parent_thread'] = nel_cast_to_datatype($data['parent_thread'], 'integer');
        $content_array['post_ref'] = nel_cast_to_datatype($data['post_ref'], 'integer');
        $content_array['content_order'] = nel_cast_to_datatype($data['content_order'], 'integer');
        $content_array['type'] = nel_cast_to_datatype($data['type'], 'string');
        $content_array['format'] = nel_cast_to_datatype($data['format'], 'string');
        $this->addIfNotEmpty($content_array, 'mime', bin2hex($data['mime']), 'string');
        $this->addIfNotEmpty($content_array, 'filename', bin2hex($data['filename']), 'string');
        $this->addIfNotEmpty($content_array, 'extension', bin2hex($data['extension']), 'string');
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
        $this->addIfNotEmpty($content_array, 'source', bin2hex($data['source']), 'string');
        $this->addIfNotEmpty($content_array, 'license', bin2hex($data['license']), 'string');
        $this->addIfNotEmpty($content_array, 'alt_text', bin2hex($data['alt_text']), 'string');
        $this->addIfNotEmpty($content_array, 'url', bin2hex($data['url']), 'string');
        $this->addIfNotEmpty($content_array, 'exif', bin2hex($data['exif']), 'string');
        $this->addIfNotEmpty($content_array, 'meta', bin2hex($data['meta']), 'string');
        $content_array = nel_plugins()->processHook('nel-json-prepare-content', array($data), $content_array);

        if($store)
        {
            $this->data_array = $content_array;
        }
        else
        {
            return $content_array;
        }
    }

    public function storeData($data)
    {
        $this->data_array = $data;
    }

    public function getStoredData()
    {
        return $this->data_array;
    }

    public function writeStoredData($file_path, $file_name)
    {
        ;
    }
}