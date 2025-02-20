<?php
declare(strict_types = 1);

namespace Nelliel\Interfaces;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

interface SectionedMutableData
{
    /**
     * Gets data for the given section and key.
     * If key is optional and not provided then the entire section must be returned.
     */
    public function getSectionData(string $section, string $key);

    /**
     * Changes data for the given section and key.
     * If key is nullable and not provided then the entire section must be replaced.
     */
    public function changeSectionData(string $section, string $key, $new_data): void;
}