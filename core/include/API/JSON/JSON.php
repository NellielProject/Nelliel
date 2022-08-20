<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

abstract class JSON
{
    protected $api_version = 0;
    protected $json = '';
    protected $raw_data = array();
    protected $needs_update = true;

    abstract protected function generate(): void;

    public function getJSON(): string
    {
        if ($this->needs_update) {
            $this->generate();
        }

        return $this->json;
    }

    public function getRawData(): array
    {
        if ($this->needs_update) {
            $this->generate();
        }

        return $this->raw_data;
    }
}