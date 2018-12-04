<?php

namespace Nelliel\API\JSON;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class JSONThread extends JSONBase
{
    private $board_id;
    private $thread_id;

    function __construct($board_id, $file_handler, $thread_id)
    {
        $this->board_id = $board_id;
        $this->file_handler = $file_handler;
        $board_path = nel_parameters_and_data()->boardReferences($board_id, 'board_path');
        $page_path = nel_parameters_and_data()->boardReferences($board_id, 'page_path') . $thread_id . '/';
        $this->file_path = $page_path;
        $filename_format = nel_parameters_and_data()->siteSettings('thread_filename_format');
        $this->file_name = sprintf('thread-%d', $thread_id);
    }

    public function writeJSON()
    {
        $json_data = json_encode($this->data_array);
        $this->file_handler->writeFile($this->file_path . $this->file_name . JSON_EXT, $json_data);
    }

    public function addThreadData($thread_data)
    {
        $thread_array = array();
        $thread_array['thread_id'] = nel_cast_to_datatype($thread_data['thread_id'], 'integer');
        $thread_array['first_post'] = nel_cast_to_datatype($thread_data['first_post'], 'integer');
        $thread_array['last_post'] = nel_cast_to_datatype($thread_data['last_post'], 'integer');
        $thread_array['total_files'] = nel_cast_to_datatype($thread_data['total_files'], 'integer');
        $thread_array['last_update'] = nel_cast_to_datatype($thread_data['last_update'], 'integer');
        $thread_array['last_update_milli'] = nel_cast_to_datatype($thread_data['last_update_milli'], 'integer');
        $thread_array['post_count'] = nel_cast_to_datatype($thread_data['post_count'], 'integer');
        $thread_array['thread_sage'] = nel_cast_to_datatype($thread_data['thread_sage'], 'boolean');
        $thread_array['sticky'] = nel_cast_to_datatype($thread_data['sticky'], 'boolean');
        $thread_array['locked'] = nel_cast_to_datatype($thread_data['locked'], 'boolean');
        $this->data_array['thread'] = $thread_array;
    }

    public function addPostData($post_data)
    {
        $post_array = array();
        $post_array['post_number'] = nel_cast_to_datatype($post_data['post_number'], 'integer');
        $post_array['parent_thread'] = nel_cast_to_datatype($post_data['parent_thread'], 'integer');
        $this->addIfNotEmpty($post_array, 'poster_name', $post_data['poster_name'], 'string');
        $this->addIfNotEmpty($post_array, 'tripcode', $post_data['tripcode'], 'string');
        $this->addIfNotEmpty($post_array, 'secure_tripcode', $post_data['secure_tripcode'], 'string');
        $this->addIfNotEmpty($post_array, 'email', $post_data['email'], 'string');
        $this->addIfNotEmpty($post_array, 'subject', $post_data['subject'], 'string');
        $this->addIfNotEmpty($post_array, 'comment', $post_data['comment'], 'string');
        $post_array['post_time'] = nel_cast_to_datatype($post_data['post_time'], 'integer');
        $post_array['post_time_milli'] = nel_cast_to_datatype($post_data['post_time_milli'], 'integer');
        $post_array['has_file'] = nel_cast_to_datatype($post_data['has_file'], 'boolean');
        $post_array['file_count'] = nel_cast_to_datatype($post_data['file_count'], 'integer');
        $post_array['op'] = nel_cast_to_datatype($post_data['op'], 'boolean');
        $post_array['sage'] = nel_cast_to_datatype($post_data['sage'], 'boolean');
        $this->addIfNotEmpty($post_array, 'mod_comment', $post_data['mod_comment'], 'string');
        $this->data_array['posts'][$post_array['post_number']] = $post_array;
    }

    public function addContentData($content_data)
    {
        $content_array = array();
        $content_array['parent_thread'] = nel_cast_to_datatype($content_data['parent_thread'], 'integer');
        $content_array['post_ref'] = nel_cast_to_datatype($content_data['post_ref'], 'integer');
        $content_array['content_order'] = nel_cast_to_datatype($content_data['content_order'], 'integer');
        $content_array['type'] = nel_cast_to_datatype($content_data['type'], 'string');
        $content_array['format'] = nel_cast_to_datatype($content_data['format'], 'string');
        $this->addIfNotEmpty($content_array, 'mime', bin2hex($content_data['mime']), 'string');
        $this->addIfNotEmpty($content_array, 'filename', bin2hex($content_data['filename']), 'string');
        $this->addIfNotEmpty($content_array, 'extension', bin2hex($content_data['extension']), 'string');
        $this->addIfNotEmpty($content_array, 'display_width', $content_data['display_width'], 'integer');
        $this->addIfNotEmpty($content_array, 'display_height', $content_data['display_height'], 'integer');
        $this->addIfNotEmpty($content_array, 'preview_name', $content_data['preview_name'], 'string');
        $this->addIfNotEmpty($content_array, 'preview_extension', $content_data['preview_extension'], 'string');
        $this->addIfNotEmpty($content_array, 'preview_width', $content_data['preview_width'], 'integer');
        $this->addIfNotEmpty($content_array, 'preview_height', $content_data['preview_height'], 'integer');
        $this->addIfNotEmpty($content_array, 'filesize', $content_data['filesize'], 'integer');
        $this->addIfNotEmpty($content_array, 'md5', bin2hex($content_data['md5']), 'string');
        $this->addIfNotEmpty($content_array, 'sha1', bin2hex($content_data['sha1']), 'string');
        $this->addIfNotEmpty($content_array, 'sha256', bin2hex($content_data['sha256']), 'string');
        $this->addIfNotEmpty($content_array, 'sha512', bin2hex($content_data['sha512']), 'string');
        $this->addIfNotEmpty($content_array, 'source', bin2hex($content_data['source']), 'string');
        $this->addIfNotEmpty($content_array, 'license', bin2hex($content_data['license']), 'string');
        $this->addIfNotEmpty($content_array, 'alt_text', bin2hex($content_data['alt_text']), 'string');
        $this->addIfNotEmpty($content_array, 'url', bin2hex($content_data['url']), 'string');
        $this->addIfNotEmpty($content_array, 'exif', bin2hex($content_data['exif']), 'string');
        $this->addIfNotEmpty($content_array, 'meta', bin2hex($content_data['meta']), 'string');
        $this->data_array['posts'][$content_array['post_ref']]['content'][$content_array['content_order']] = $content_array;
    }
}