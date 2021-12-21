<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

abstract class JSON
{
    protected $api_version = 0;
    protected $json = '';
    protected $raw_data = array();

    public function getJSON(): string
    {
        return $this->json;
    }

    public function getRawData(): array
    {
        return $this->raw_data;
    }

    public function generateFromRawData(array $raw_data): void
    {
        $this->json = json_encode($raw_data);
    }
}