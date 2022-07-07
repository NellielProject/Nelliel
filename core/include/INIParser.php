<?php
declare(strict_types = 1);

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

    public function parseDirectories(string $path, string $filename = '', bool $as_object = false,
        int $recursion_depth = -1): array
    {
        $files = $this->file_handler->recursiveFileList($path, $recursion_depth);
        $parsed = array();

        foreach ($files as $file) {
            if ($file->getExtension() !== 'ini') {
                continue;
            }

            if ($filename !== '' && $file->getFilename() !== $filename) {
                continue;
            }

            if ($as_object) {
                $parsed[] = new INIFile($file);
            } else {
                $parsed[] = parse_ini_file($file->getPathname(), true);
            }
        }

        return $parsed;
    }
}