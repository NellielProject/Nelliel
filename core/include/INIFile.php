<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use SplFileInfo;

class INIFile
{
    private $parsed = array();
    private $file_info;

    function __construct(SplFileInfo $file_info)
    {
        $this->file_info = $file_info;
        $this->parsed = parse_ini_file($file_info->getPathname(), true);
    }

    public function fileInfo(): SPLFileInfo {
        return $this->file_info;
    }

    public function parsed() : array {
        return $this->parsed;
    }

}
