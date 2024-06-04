<?php
declare(strict_types = 1);

namespace Nelliel\Utility;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class CacheHandler
{
    private static $hashes = array();

    function __construct(bool $hash_load = true)
    {
        if (empty(self::$hashes) && $hash_load) {
            $this->loadHashes();
        }
    }

    public function loadArrayFromFile(string $array_variable, string $filename, string $relative_path = ''): array
    {
        $file_handler = new FileHandler();
        $array = array();

        $file_path = $file_handler->pathJoin(NEL_CACHE_FILES_PATH, $file_handler->pathJoin($relative_path, $filename));

        if (file_exists($file_path)) {
            include $file_path;
            $array = $$array_variable;
        }

        return $array;
    }

    public function writeArrayToFile(string $array_variable, array $array, string $filename, string $relative_path = ''): bool
    {
        $file_handler = new FileHandler();
        $file_path = $file_handler->pathJoin(NEL_CACHE_FILES_PATH, $file_handler->pathJoin($relative_path, $filename));

        if (!is_writable(NEL_CACHE_FILES_PATH)) {
            return false;
        }

        $exported_array = "\n$" . $array_variable . " = " . var_export($array, true) . ";\n";
        $file_handler->writeFile($file_path, NEL_INTERNAL_FILE_HEADER . $exported_array, true);
        // Make certain further cache loads use the new cache
        clearstatcache(true, $file_path);
        opcache_invalidate($file_path);
        return true;
    }

    public function checkHash(string $id, string $hash): bool
    {
        return isset(self::$hashes[$id]) && hash_equals(self::$hashes[$id], $hash);
    }

    public function loadHashes(): void
    {
        if (file_exists(NEL_CACHE_FILES_PATH . 'hashes.php')) {
            $hashes = array();
            include NEL_CACHE_FILES_PATH . 'hashes.php';
            self::$hashes = $hashes;
        }
    }

    public function updateHash(string $id, string $hash): void
    {
        self::$hashes[$id] = $hash;
        $this->writeArrayToFile('hashes', self::$hashes, 'hashes.php');
    }
}