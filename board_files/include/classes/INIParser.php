<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class INIParser
{
    protected $file_handler;

    function __construct(FileHandler $file_handler)
    {
        $this->file_handler = $file_handler;
    }

    public function parseDirectories($path, string $filename = '', $recursion_depth = -1)
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