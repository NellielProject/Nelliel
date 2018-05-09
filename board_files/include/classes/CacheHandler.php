<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class CacheHandler
{

    function __construct()
    {
    }

    public function writeCacheFile($path, $filename, $content, $header = '', $footer = '', $file_perm = FILE_PERM)
    {
        $file_handler = new \Nelliel\FileHandler();
        $file_handler->writeFile($path . $filename, $header . $content . $footer, $file_perm, true);
    }
}