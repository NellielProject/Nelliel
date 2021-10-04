<?php

declare(strict_types=1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Utility\FileHandler;

class INIParser
{
    protected $file_handler;

    function __construct(FileHandler $file_handler)
    {
        $this->file_handler = $file_handler;
    }

    public function parseDirectories(string $path, string $filename = '', int $recursion_depth = -1)
    {
        $ini_files = $this->file_handler->recursiveFileList($path, $recursion_depth);
        $parsed_ini = array();

        foreach ($ini_files as $file)
        {
            if ($file->getExtension() !== 'ini')
            {
                continue;
            }

            if ($filename !== '' && $file->getFilename() !== $filename)
            {
                continue;
            }

            $parsed_ini[] = parse_ini_file($file->getPathname(), true);
        }

        return $parsed_ini;
    }
}