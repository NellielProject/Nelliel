<?php
declare(strict_types = 1);

namespace Nelliel\Interfaces;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

interface MutableData
{
    /**
     * Gets data for the given key.
     * If key is optional and not provided then all data must be returned.
     */
    public function getData(string $key);

    /**
     * Changes data for the given key.
     * If key is nullable and not provided then all data must be replaced.
     */
    public function changeData(string $key, $new_data): void;
}