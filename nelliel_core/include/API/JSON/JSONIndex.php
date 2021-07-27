<?php

declare(strict_types=1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use \Nelliel\Domains\Domain;
use \Nelliel\Utility\FileHandler;

class JSONIndex extends JSONOutput
{

    function __construct(Domain $domain, FileHandler $file_handler)
    {
        $this->domain = $domain;
        $this->file_handler = $file_handler;
        $this->data_array['index'] = array();
    }

    public function prepareData(array $data)
    {
        $index_array = array();
        $index_array['thread_count'] = nel_cast_to_datatype($data['thread_count'], 'integer');
        $index_array = nel_plugins()->processHook('nel-json-prepare-post', [$data], $index_array);
        return $index_array;
    }

    public function addThreadData(array $thread_data)
    {
        $this->data_array['thread-list'][] = $thread_data;
    }
}