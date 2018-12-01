<?php

namespace Nelliel\API;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class JSONAPI
{
    private $file_handler;

    function __construct($file_handler)
    {
        $this->file_handler = $file_handler;
    }

    private function addIfNotEmpty(&$data, $key, $value, $type)
    {
        if ($value === null)
        {
            return;
        }

        if ($type === 'string' && $value !== '')
        {
            $data[$key] = nel_cast_to_datatype($value, $type);
        }

        if ($type === 'integer' && $value !== 0)
        {
            $data[$key] = nel_cast_to_datatype($value, $type);
        }
    }

    public function writeThreadData()
    {

    }

    public function assembleThreadData($thread_data)
    {
        $data_output = array();
        $data_output['thread_id'] = nel_cast_to_datatype($thread_data['thread_id'], 'integer');
        $data_output['first_post'] = nel_cast_to_datatype($thread_data['first_post'], 'integer');
        $data_output['last_post'] = nel_cast_to_datatype($thread_data['last_post'], 'integer');
        $data_output['total_files'] = nel_cast_to_datatype($thread_data['total_files'], 'integer');
        $data_output['last_update'] = nel_cast_to_datatype($thread_data['last_update'], 'integer');
        $data_output['last_update_milli'] = nel_cast_to_datatype($thread_data['last_update_milli'], 'integer');
        $data_output['post_count'] = nel_cast_to_datatype($thread_data['post_count'], 'integer');
        $data_output['thread_sage'] = nel_cast_to_datatype($thread_data['thread_sage'], 'boolean');
        $data_output['sticky'] = nel_cast_to_datatype($thread_data['sticky'], 'boolean');
        $data_output['locked'] = nel_cast_to_datatype($thread_data['locked'], 'boolean');
        return json_encode($data_output);
    }

    public function assemblePostData($post_data)
    {
        $data_output = array();
        $data_output['post_number'] = nel_cast_to_datatype($post_data['post_number'], 'integer');
        $data_output['parent_thread'] = nel_cast_to_datatype($post_data['parent_thread'], 'integer');
        $this->addIfNotEmpty($data_output, 'poster_name', $post_data['poster_name'], 'string');
        $this->addIfNotEmpty($data_output, 'tripcode', $post_data['tripcode'], 'string');
        $this->addIfNotEmpty($data_output, 'secure_tripcode', $post_data['secure_tripcode'], 'string');
        $this->addIfNotEmpty($data_output, 'email', $post_data['email'], 'string');
        $this->addIfNotEmpty($data_output, 'subject', $post_data['subject'], 'string');
        $this->addIfNotEmpty($data_output, 'comment', $post_data['comment'], 'string');
        $data_output['post_time'] = nel_cast_to_datatype($post_data['post_time'], 'integer');
        $data_output['post_time_milli'] = nel_cast_to_datatype($post_data['post_time_milli'], 'integer');
        $data_output['has_file'] = nel_cast_to_datatype($post_data['has_file'], 'boolean');

        if($data_output['has_file'])
        {
            $data_output['file_count'] = nel_cast_to_datatype($post_data['file_count'], 'integer');
        }

        $data_output['op'] = nel_cast_to_datatype($post_data['op'], 'boolean');
        $data_output['sage'] = nel_cast_to_datatype($post_data['sage'], 'boolean');
        $this->addIfNotEmpty($data_output, 'mod_comment', $post_data['mod_comment'], 'string');
        return json_encode($data_output);
    }

    public function assembleFileData($file_data)
    {
        $data_output = array();
        $data_output['parent_thread'] = nel_cast_to_datatype($post_data['parent_thread'], 'integer');
        $data_output['post_ref'] = nel_cast_to_datatype($post_data['post_ref'], 'integer');
        $data_output['content_order'] = nel_cast_to_datatype($post_data['content_order'], 'integer');
        $data_output['type'] = nel_cast_to_datatype($thread_data['type'], 'string');
        $data_output['format'] = nel_cast_to_datatype($thread_data['format'], 'string');
        $data_output['mime'] = nel_cast_to_datatype($thread_data['mime'], 'string');
        $data_output['filename'] = nel_cast_to_datatype($post_data['filename'], 'string');
        $data_output['extension'] = nel_cast_to_datatype($post_data['extension'], 'string');
        $this->addIfNotEmpty($data_output, 'display_width', $post_data['display_width'], 'integer');
        $this->addIfNotEmpty($data_output, 'display_height', $post_data['display_height'], 'integer');
        $this->addIfNotEmpty($data_output, 'preview_name', $post_data['preview_name'], 'string');
        $this->addIfNotEmpty($data_output, 'preview_extension', $post_data['preview_extension'], 'string');
        $this->addIfNotEmpty($data_output, 'preview_width', $post_data['preview_width'], 'integer');
        $this->addIfNotEmpty($data_output, 'preview_height', $post_data['preview_height'], 'integer');
        $data_output['filesize'] = nel_cast_to_datatype($post_data['filesize'], 'integer');
        $this->addIfNotEmpty($data_output, 'md5', bin2hex($post_data['md5']), 'string');
        $this->addIfNotEmpty($data_output, 'sha1', bin2hex($post_data['sha1']), 'string');
        $this->addIfNotEmpty($data_output, 'sha256', bin2hex($post_data['sha256']), 'string');
        $this->addIfNotEmpty($data_output, 'sha512', bin2hex($post_data['sha512']), 'string');
        $this->addIfNotEmpty($data_output, 'source', bin2hex($post_data['source']), 'string');
        $this->addIfNotEmpty($data_output, 'license', bin2hex($post_data['license']), 'string');
        $this->addIfNotEmpty($data_output, 'alt_text', bin2hex($post_data['alt_text']), 'string');
        $this->addIfNotEmpty($data_output, 'url', bin2hex($post_data['url']), 'string');
        $this->addIfNotEmpty($data_output, 'exif', bin2hex($post_data['exif']), 'string');
        $this->addIfNotEmpty($data_output, 'meta', bin2hex($post_data['meta']), 'string');
        return json_encode($data_output);
    }
}