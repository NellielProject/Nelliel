<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class CacheHandler
{
    private $hashes;
    private $default_header;

    function __construct($no_hash_load = false)
    {
        $this->default_header = '<?php if(!defined("NELLIEL_VERSION")){die("NOPE.AVI");}';

        if(!$no_hash_load)
        {
            $this->loadHashes();
        }
    }

    public function checkHash($id, $hash)
    {
        return isset($this->hashes[$id]) && hash_equals($this->hashes[$id], $hash);
    }

    public function loadHashes()
    {
        if (file_exists(CACHE_PATH . 'hashes.php'))
        {
            include CACHE_PATH . 'hashes.php';
            $this->hashes = $hashes;
        }
    }

    public function updateHash($id, $hash)
    {
        $this->hashes[$id] = $hash;
        $this->writeCacheFile(CACHE_PATH, 'hashes.php', '$hashes = ' . var_export($this->hashes, true) . ';');
    }

    public function writeCacheFile($path, $filename, $content, $header = '', $footer = '', $file_perm = FILE_PERM)
    {
        $header = (!empty($header)) ? $headeer : $this->default_header;
        $file_handler = new \Nelliel\FileHandler();
        $file_handler->writeFile($path . $filename, $header . $content . $footer, $file_perm, true);
    }
}