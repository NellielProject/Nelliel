<?php

declare(strict_types=1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use \Nelliel\Domains\Domain;
use \Nelliel\Utility\FileHandler;

class JSONUpload extends JSONOutput
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
        $content_array['parent_thread'] = nel_cast_to_datatype($data['parent_thread'], 'integer');
        $content_array['post_ref'] = nel_cast_to_datatype($data['post_ref'], 'integer');
        $content_array['upload_order'] = nel_cast_to_datatype($data['upload_order'], 'integer');
        $content_array['type'] = nel_cast_to_datatype($data['type'], 'string');
        $content_array['format'] = nel_cast_to_datatype($data['format'], 'string');
        $content_array['mime'] = nel_cast_to_datatype($data['mime'], 'string');
        $content_array['filename'] = nel_cast_to_datatype($data['filename'], 'string');
        $content_array['extension'] = nel_cast_to_datatype($data['extension'], 'string');
        $content_array['display_width'] = nel_cast_to_datatype($data['display_width'], 'integer');
        $content_array['display_height'] = nel_cast_to_datatype($data['display_height'], 'integer');
        $content_array['preview_name'] = nel_cast_to_datatype($data['preview_name'], 'string');
        $content_array['preview_extension'] = nel_cast_to_datatype($data['preview_extension'], 'string');
        $content_array['preview_width'] = nel_cast_to_datatype($data['preview_width'], 'integer');
        $content_array['preview_height'] = nel_cast_to_datatype($data['preview_height'], 'integer');
        $content_array['filesize'] = nel_cast_to_datatype($data['filesize'], 'integer');
        $content_array['md5'] = (!is_null($data['md5'])) ? bin2hex($data['md5']) : null;
        $content_array['sha1'] = (!is_null($data['sha1'])) ? bin2hex($data['md5']) : null;
        $content_array['sha256'] = (!is_null($data['sha256'])) ? bin2hex($data['md5']) : null;
        $content_array['sha512'] = (!is_null($data['sha512'])) ? bin2hex($data['md5']) : null;
        $content_array['embed_url'] = nel_cast_to_datatype($data['embed_url'], 'string');
        $content_array['spoiler'] = nel_cast_to_datatype($data['spoiler'], 'boolean');
        $content_array['deleted'] = nel_cast_to_datatype($data['deleted'], 'boolean');
        $content_array['exif'] = nel_cast_to_datatype($data['exif'], 'string');
        $content_array = nel_plugins()->processHook('nel-json-prepare-content', [$data], $content_array);
        return $content_array;
    }
}
