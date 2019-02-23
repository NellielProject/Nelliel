<?php

namespace Nelliel\API\JSON;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use \Nelliel\Domain;
use \Nelliel\FileHandler;

class JSONBoard extends JSONOutput
{

    function __construct(Domain $domain, FileHandler $file_handler)
    {
        $this->domain = $domain;
        $this->file_handler = $file_handler;
        $this->setVersion();
        $this->data_array['boards'] = array();
    }

    public function prepareData(array $data)
    {
        $boards_array = array();
        $boards_array['board_id'] = nel_cast_to_datatype($data['board_id'], 'string');
        $boards_array['name'] = nel_cast_to_datatype($data['board_name'], 'string');
        $boards_array['slogan'] = nel_cast_to_datatype($data['board_slogan'], 'string');
        $boards_array['description'] = nel_cast_to_datatype($data['board_description'], 'string');
        $boards_array['language'] = nel_cast_to_datatype($data['language'], 'string');
        $boards_array['forced_anonymous'] = nel_cast_to_datatype($data['force_anonymous'], 'boolean');
        $boards_array['threads_per_page'] = nel_cast_to_datatype($data['threads_per_page'], 'integer');
        $boards_array['page_limit'] = nel_cast_to_datatype($data['page_limit'], 'integer');
        $boards_array['max_bumps'] = nel_cast_to_datatype($data['max_bumps'], 'integer');
        $boards_array['max_posts'] = nel_cast_to_datatype($data['max_posts'], 'integer');
        $boards_array['max_filesize'] = nel_cast_to_datatype($data['max_filesize'], 'integer');
        $boards_array['require_content_start'] = nel_cast_to_datatype($data['require_content_start'], 'boolean');
        $boards_array['require_content_always'] = nel_cast_to_datatype($data['require_content_always'], 'boolean');
        $boards_array['allow_tripkeys'] = nel_cast_to_datatype($data['allow_tripkeys'], 'boolean');
        $boards_array['threads_per_page'] = nel_cast_to_datatype($data['threads_per_page'], 'integer');
        $boards_array['cooldowns']['threads'] = nel_cast_to_datatype($data['thread_cooldown'], 'integer');
        $boards_array['cooldowns']['replies'] = nel_cast_to_datatype($data['reply_cooldown'], 'integer');
        $boards_array = nel_plugins()->processHook('nel-json-prepare-board', [$data], $boards_array);
        return $boards_array;
    }
}