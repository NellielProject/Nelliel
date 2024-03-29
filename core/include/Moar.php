<?php

declare(strict_types=1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class Moar
{
    private $moar = array();
    private $json_needs_update;

    function __construct()
    {
    }

    public function storeFromArray(array $moar)
    {
        $this->moar = $moar;
    }

    public function storeFromJSON(string $moar)
    {
        $this->moar = json_decode($moar, true);
    }

    public function get($index = null)
    {
        if (is_null($index))
        {
            return $this->moar;
        }

        return $this->moar[$index] ?? null;
    }

    public function getJSON($index = null)
    {
        return json_encode($this->get($index));
    }

    public function modify($index, $moar)
    {
        $this->moar[$index] = $moar;
    }
}
