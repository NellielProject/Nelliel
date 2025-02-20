<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Interfaces\MutableData;
use Nelliel\Interfaces\SectionedMutableData;

class Moar implements MutableData, SectionedMutableData
{
    private $data = array();

    function __construct(string $json = null)
    {
        if (!is_null($json)) {
            $this->data = json_decode($json, true);
        }
    }

    public function getData(string $key = null)
    {
        if (is_null($key)) {
            return $this->data;
        }

        return $this->data[$key] ?? null;
    }

    public function changeData(string $key = null, $new_data): void
    {
        if (is_null($key)) {
            $this->data = $new_data;
        } else {
            $this->data[$key] = $new_data;
        }
    }

    public function getSectionData(string $section, string $key = null)
    {
        if (is_null($key)) {
            return $this->data[$section] ?? null;
        }

        return $this->data[$section][$key] ?? null;
    }

    public function changeSectionData(string $section, string $key = null, $new_data): void
    {
        if (is_null($key)) {
            $this->data[$section] = $new_data;
        } else {
            $this->data[$section][$key] = $new_data;
        }
    }
}
