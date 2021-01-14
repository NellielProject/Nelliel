<?php

namespace Nelliel\Utility;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class CacheHandler
{
    private static $hashes;
    private $header = "<?php if(!defined('NELLIEL_VERSION')){die('NOPE.AVI');}\n";

    function __construct(bool $hash_load = true)
    {
        if (empty(self::$hashes) && $hash_load)
        {
            $this->loadHashes();
        }
    }

    public function loadArrayFromFile(string $array_variable, string $filename, string $sub_directory = '')
    {
        $array = array();

        if (NEL_USE_FILE_CACHE)
        {
            $file_path = NEL_CACHE_FILES_PATH . $sub_directory . '/' . $filename;

            if (file_exists($file_path))
            {
                include $file_path;
                $array = $$array_variable;
            }
        }

        return $array;
    }

    public function writeArrayToFile(string $array_variable, array $array, string $filename, string $sub_directory = '')
    {
        $file_handler = new FileHandler();
        $file_path = NEL_CACHE_FILES_PATH . $sub_directory . '/' . $filename;

        if (NEL_USE_FILE_CACHE)
        {
            if (!is_writable(NEL_CACHE_FILES_PATH))
            {
                if (!file_exists(NEL_CACHE_FILES_PATH))
                {
                    $file_handler->createDirectory(NEL_CACHE_FILES_PATH);
                }
                else
                {
                    return; // TODO: Work out so this can be a proper error
                }
            }

            $exported_array = "\n$" . $array_variable . " = " . var_export($array, true) . ";\n";
            $file_handler->writeFile($file_path, $this->header . $exported_array, NEL_FILES_PERM, true);
        }
    }

    public function checkHash($id, $hash)
    {
        return isset(self::$hashes[$id]) && hash_equals(self::$hashes[$id], $hash);
    }

    public function loadHashes()
    {
        if (file_exists(NEL_CACHE_FILES_PATH . 'hashes.php'))
        {
            $hashes = array();
            include NEL_CACHE_FILES_PATH . 'hashes.php';
            self::$hashes = $hashes;
        }
    }

    public function updateHash($id, $hash)
    {
        self::$hashes[$id] = $hash;
        $this->writeArrayToFile('hashes', self::$hashes, 'hashes.php');
    }
}