<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class InfoJSON extends JSON
{

    function __construct()
    {}

    protected function generate(): void
    {
        $raw_data = array();
        $raw_data['api_compatibility'] = [$this->compatibility];
        $raw_data['api_version'] = $this->api_version;
        $raw_data = nel_plugins()->processHook('nel-in-during-info-json', [], $raw_data);
        $this->raw_data = $raw_data;
        $this->json = json_encode($raw_data);
        $this->needs_update = false;
    }
}