<?php

namespace Nelliel\Utility;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class CacheHandler
{
    private $hashes;
    private $default_header;

    function __construct(bool $no_hash_load = false)
    {
        $this->default_header = '<?php if(!defined("NELLIEL_VERSION")){die("NOPE.AVI");}';

        if(!$no_hash_load)
        {
            $this->loadHashes();
        }
    }

    public function loadArrayFromCache(string $filename, $array_variable)
    {
        if (USE_INTERNAL_CACHE)
        {
            if (file_exists(CACHE_FILE_PATH . $filename))
            {
                include CACHE_FILE_PATH . $filename;
                $array = $$array_variable;
                return $array;
            }
        }

        return array();
    }

    public function checkHash($id, $hash)
    {
        return isset($this->hashes[$id]) && hash_equals($this->hashes[$id], $hash);
    }

    public function loadHashes()
    {
        if (file_exists(CACHE_FILE_PATH . 'hashes.php'))
        {
            include CACHE_FILE_PATH . 'hashes.php';
            $this->hashes = $hashes;
        }
    }

    public function updateHash($id, $hash)
    {
        $this->hashes[$id] = $hash;
        $this->writeCacheFile(CACHE_FILE_PATH, 'hashes.php', '$hashes = ' . var_export($this->hashes, true) . ';');
    }

    public function writeCacheFile($path, string $filename, $content, string $header = '', string $footer = '', $file_perm = FILE_PERM)
    {
        $file_handler = new FileHandler();

        if (!is_writable(CACHE_FILE_PATH))
        {
            if(!file_exists(CACHE_FILE_PATH))
            {
                $file_handler->createDirectory(CACHE_FILE_PATH);
            }
            else
            {
                return; // TODO: Work out so this can be a proper error
            }
        }

        $header = (!empty($header)) ? $header : $this->default_header;

        $file_handler->writeFile($path . $filename, $header . $content . $footer, $file_perm, true);
    }
}