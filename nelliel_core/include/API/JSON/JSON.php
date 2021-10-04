<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

abstract class JSON
{
    protected $api_version = 0;
    protected $file_handler;
    protected $json = '';
    protected $raw_data = array();
    protected $source;
    protected $generated = false;

    public abstract function generate(): void;

    public abstract function write(): void;

    public function getSource()
    {
        return $this->source;
    }

    public function changeSource($source): void
    {
        $this->source = $source;
        $this->generated = false;
    }

    public function getJSON(): string
    {
        if (!$this->generated)
        {
            $this->generate();
        }

        return $this->json;
    }

    public function getRawData(): array
    {
        if (!$this->generated)
        {
            $this->generate();
        }

        return $this->raw_data;
    }
}