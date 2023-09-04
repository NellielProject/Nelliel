<?php
declare(strict_types = 1);

namespace Nelliel\Utility;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class FileHandler
{

    function __construct()
    {}

    public function writeFile(string $file, $output, bool $create_directories = false, string $chmod = NEL_FILES_PERM,
        string $dir_chmod = NEL_DIRECTORY_PERM): bool
    {
        $this->createDirectory(NEL_TEMP_FILES_BASE_PATH);
        $success = false;
        $temp_file = tempnam(NEL_TEMP_FILES_BASE_PATH, 'nel_');
        $success = file_put_contents($temp_file, $output) !== false;

        if ($success) {
            $success = $this->moveFile($temp_file, $file, $create_directories);
        }

        if ($success) {
            $success = chmod($file, octdec($chmod));
        } else {
            unlink($temp_file);
        }

        return $success;
    }

    public function writeInternalFile(string $file, $output, bool $use_header = true): bool
    {
        if ($use_header) {
            $output = NEL_INTERNAL_FILE_HEADER . $output;
        }

        return $this->writeFile($file, $output, true, NEL_FILES_PERM, NEL_DIRECTORY_PERM);
    }

    public function createDirectory(string $directory, $chmod = NEL_DIRECTORY_PERM, bool $recursive = true): bool
    {
        clearstatcache();

        if (file_exists($directory)) {
            return true;
        }

        $success = false;

        if (!$recursive) {
            $success = @mkdir($directory, 0777, false);

            if ($success) {
                $success = chmod($directory, octdec($chmod));
            }
        } else {
            $directories = explode('/', $directory);
            $current_path = '';

            foreach ($directories as $directory) {
                if (empty($directory)) {
                    continue;
                }

                $current_path = $current_path . '/' . $directory;

                if (file_exists($current_path)) {
                    continue;
                } else {
                    $success = @mkdir($current_path, 0777, false);

                    if ($success) {
                        $success = chmod($current_path, octdec($chmod));
                    }
                }
            }
        }

        return $success;
    }

    public function copyFile(string $file, string $destination, bool $create_directories = false,
        $dir_chmod = NEL_DIRECTORY_PERM): bool
    {
        clearstatcache();
        $success = file_exists($file);

        if ($success && !file_exists($destination) && $create_directories) {
            $success = $this->createDirectory(dirname($destination), $dir_chmod, true);
        }

        if ($success) {
            $success = copy($file, $destination);
        }

        return $success;
    }

    public function moveFile(string $file, string $destination, bool $create_directories = false,
        $dir_chmod = NEL_DIRECTORY_PERM): bool
    {
        clearstatcache();
        $success = file_exists($file);

        if ($success && !file_exists($destination) && $create_directories) {
            $success = $this->createDirectory(dirname($destination), $dir_chmod, true);
        }

        if ($success) {
            $success = rename($file, $destination);
        }

        return $success;
    }

    public function moveDirectory(string $directory, string $destination, bool $create_directories = false,
        $dir_chmod = NEL_DIRECTORY_PERM): bool
    {
        clearstatcache();

        if (!file_exists($directory) || !is_dir($directory)) {
            return false;
        }

        $success = false;

        if (!file_exists($destination) && $create_directories) {
            $success = $this->createDirectory(dirname($destination), $dir_chmod, true);
        }

        if ($success) {
            $success = rename($directory, $destination);
        }

        return $success;
    }

    public function eraserGun(string $path, string $filename = ''): bool
    {
        clearstatcache();
        $success = false;

        if ($filename !== '') {
            if (file_exists($this->pathJoin($path, $filename))) {
                $success = unlink($this->pathJoin($path, $filename));
            }
        } else {
            if (file_exists($path) && is_dir($path)) {
                $di = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
                $rii = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST,
                    RecursiveIteratorIterator::CATCH_GET_CHILD);

                foreach ($rii as $fileInfo) {
                    if ($fileInfo->isFile()) {
                        unlink($fileInfo->getRealPath());
                    } else if ($fileInfo->isDir()) {
                        @rmdir($fileInfo->getRealpath());
                    }
                }

                $success = @rmdir($path);
            }
        }

        return $success;
    }

    public function pathJoin(string $path, string $path2, bool $merge_separators = true): string
    {
        $separator = DIRECTORY_SEPARATOR;
        $path_has_separator = utf8_substr($path, -1) === DIRECTORY_SEPARATOR;
        $path2_has_separator = utf8_substr($path2, 0, 1) === DIRECTORY_SEPARATOR;

        if ($path_has_separator || $path2_has_separator) {
            $separator = '';
        }

        if ($merge_separators && $path_has_separator && $path2_has_separator) {
            $path2 = utf8_substr($path2, 1);
        }

        return $path . $separator . $path2;
    }

    public function recursiveFileList($path, int $recursion_depth = -1, bool $include_directories = false): array
    {
        $file_list = array();

        if (!file_exists($path)) {
            return $file_list;
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        $iterator->setMaxDepth($recursion_depth);

        foreach ($iterator as $file) {
            if ($file->isDir() && !$include_directories) {
                continue;
            }

            $file_list[] = $file;
        }

        return $file_list;
    }

    public function umaskOffset($perm)
    {
        return octdec($perm) + umask();
    }

    public function isCriticalPath(string $path): bool
    {
        $real_path = realpath($path);

        return $real_path === NEL_BASE_PATH && $real_path === NEL_CORE_PATH && $real_path === NEL_INCLUDE_PATH &&
            $real_path === NEL_LIBRARY_PATH && $real_path === NEL_PUBLIC_PATH && $real_path === NEL_ASSETS_FILES_PATH &&
            $real_path === NEL_CONFIG_FILES_PATH && $real_path === NEL_TEMPLATES_FILES_PATH &&
            $real_path === NEL_PLUGINS_FILES_PATH && $real_path === NEL_LANGUAGES_FILES_PATH &&
            $real_path === NEL_LOCALE_FILES_PATH && $real_path === NEL_STYLES_FILES_PATH &&
            $real_path === NEL_IMAGE_SETS_FILES_PATH && $real_path === NEL_BANNERS_FILES_PATH &&
            $real_path === NEL_WAT_FILES_PATH && $real_path === NEL_GENERAL_FILES_PATH &&
            $real_path === NEL_SCRIPTS_FILES_PATH;
    }
}