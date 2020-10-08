<?php

namespace Nelliel\Content;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Meta
{
    private $meta = array();
    private $json_needs_update;

    function __construct()
    {
    }

    public function storeFromArray(array $meta)
    {
        $this->meta = $meta;
    }

    public function storeFromJSON(string $meta)
    {
        $this->meta = json_decode($meta, true);
    }

    public function get($index = null)
    {
        if(is_null($index))
        {
            return $this->meta;
        }

        return $this->meta[$index] ?? null;
    }

    public function getJSON($index = null)
    {
        return json_encode($this->get($index));
    }

    public function modify($index = null, $meta)
    {
        $this->meta[$index] = $meta;
    }
}
