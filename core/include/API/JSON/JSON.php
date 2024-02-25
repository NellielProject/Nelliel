<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

abstract class JSON
{
    protected $compatibility = ['nelliel'];
    protected $api_version = 0;
    protected $raw_data = array();
    protected $needs_update = true;

    abstract protected function generate(): void;

    public function getJSON(bool $api_header = false): string
    {
        if ($this->needs_update) {
            $this->generate();
        }

        $raw_data = array();

        if ($api_header) {
            $raw_data['api_info'] = $this->getAPIInfo();
        }

        $raw_data = $raw_data + $this->raw_data;
        return json_encode($raw_data);
    }

    public function getRawData(): array
    {
        if ($this->needs_update) {
            $this->generate();
        }

        return $this->raw_data;
    }

    public function getAPIInfo(): array
    {
        $info = array();
        $info['compatibility'] = $this->compatibility;
        $info['version'] = $this->api_version;
        $info = nel_plugins()->processHook('nel-in-after-info-json', [], $info);
        return $info;
    }
}