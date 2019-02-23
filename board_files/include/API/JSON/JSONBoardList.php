<?php

namespace Nelliel\API\JSON;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use \Nelliel\Domain;
use \Nelliel\FileHandler;

class JSONBoardList extends JSONOutput
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
        $board_list_array = $data;
        $board_list_array = nel_plugins()->processHook('nel-json-prepare-board-list', [$data], $board_list_array);
        return $board_list_array;
    }

    public function addBoardData(array $board_data)
    {
        $this->data_array['boards'][] = $board_data;
    }
}