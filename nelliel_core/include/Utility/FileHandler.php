<?php

namespace Nelliel\Utility;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class FileHandler
{

    function __construct()
    {
    }

    public function writeFile($file, $output, $chmod = NEL_FILES_PERM, bool $create_directories = false,
            $dir_chmod = NEL_DIRECTORY_PERM, bool $temp_move = true)
    {
        $success = false;
        $file_directory = dirname($file);

        if ($create_directories)
        {
            $success = $this->createDirectory($file_directory, $dir_chmod, true);
        }

        if ($temp_move)
        {
            $temp_file = $file_directory . '/' . uniqid();
        }
        else
        {
            $temp_file = $file;
        }

        $success = file_put_contents($temp_file, $output, LOCK_EX);

        if ($success !== false)
        {
            if ($temp_move)
            {
                $success = $this->moveFile($temp_file, $file);
            }

            if ($success)
            {
                $success = chmod($file, octdec($chmod));
            }
            else
            {
                unlink($temp_file);
            }
        }

        return $success;
    }

    public function writeInternalFile($file, $output, bool $use_header = true, bool $temp_move = true)
    {
        if ($use_header)
        {
            $output = '<?php if(!defined("NELLIEL_VERSION")){die("NOPE.AVI");} ' . $output;
        }

        return $this->writeFile($file, $output, NEL_FILES_PERM, true, NEL_DIRECTORY_PERM, $temp_move);
    }

    public function createDirectory($directory, $chmod = NEL_DIRECTORY_PERM, bool $recursive = false)
    {
        clearstatcache();

        if (file_exists($directory))
        {
            return false;
        }

        $success = false;

        if (!$recursive)
        {
            $success = @mkdir($directory, null, false);
            $success = chmod($directory, octdec($chmod));
        }
        else
        {
            $directories = explode('/', $directory);
            $current_path = '';

            foreach ($directories as $directory)
            {
                if (empty($directory))
                {
                    continue;
                }

                $current_path = $current_path . '/' . $directory;

                if (file_exists($current_path))
                {
                    continue;
                }
                else
                {
                    $success = @mkdir($current_path, null, false);
                    $success = chmod($current_path, octdec($chmod));
                }
            }
        }

        return $success;
    }

    public function moveFile($file, $destination, bool $create_directories = false, $chmod = NEL_DIRECTORY_PERM)
    {
        clearstatcache();
        $success = false;

        if ($create_directories)
        {
            $success = $this->createDirectory(dirname($destination), $chmod, true);

            if (!$success)
            {
                return false;
            }
        }

        if (!file_exists($file))
        {
            return false;
        }

        $success = rename($file, $destination);
        return $success;
    }

    public function moveDirectory($directory, $destination, bool $create_directories = false,
            $dir_chmod = NEL_DIRECTORY_PERM)
    {
        clearstatcache();

        if (!file_exists($directory) || !is_dir($directory))
        {
            return false;
        }

        rename($directory, $destination);

        $files = glob($this->pathFileJoin($directory, '*'));

        foreach ($files as $file)
        {
            if (is_dir($file))
            {
                $this->moveDirectory($file, null, true);
            }
            else
            {
                rename($this->pathFileJoin($directory, $file), $this->pathFileJoin($destination, $file));
            }
        }
    }

    public function eraserGun($path, $filename = null)
    {
        clearstatcache();

        if (is_null($filename) && file_exists($path) && is_dir($path))
        {

            $files = glob($this->pathFileJoin($path, '*'));

            foreach ($files as $file)
            {
                if (is_dir($file))
                {
                    $this->eraserGun($file);
                }
                else
                {
                    unlink($file);
                }
            }

            @rmdir($path);
        }
        else if (file_exists($this->pathFileJoin($path, $filename)))
        {
            unlink($this->pathFileJoin($path, $filename));
        }
    }

    public function pathJoin($path, $path2)
    {
        $separator = DIRECTORY_SEPARATOR;

        if (substr($path, -1) == DIRECTORY_SEPARATOR)
        {
            $separator = '';
        }
        return $path . $separator . $path2;
    }

    public function pathFileJoin($path, $filename)
    {
        $separator = DIRECTORY_SEPARATOR;

        if (substr($path, -1) == DIRECTORY_SEPARATOR)
        {
            $separator = '';
        }
        return $path . $separator . $filename;
    }

    public function filterFilename($filename)
    {
        $filtered = preg_replace('#[[:cntrl:]]#u', '', $filename); // Filter out the ASCII control characters
        $filtered = preg_replace('#[^\PC\s\p{Cn}]#u', '', $filename); // Filter out invisible Unicode characters

        // https://msdn.microsoft.com/en_US/library/aa365247(VS.85).aspx
        $filtered = preg_replace('#[<>:"\/\\|\?\*]#u', '_', $filtered); // Reserved characters for Windows
        $filtered = preg_replace('#(com[1-9]|lpt[1-9]|con|prn|aux|nul)\.?[a-zA-Z0-9]*#ui', '', $filtered); // Reserved names for Windows
        $cleared = false;

        while (!$cleared)
        {
            if (preg_match('#.php#ui', $filtered) > 0)
            {
                $filtered = preg_replace('#.php#ui', '', $filtered);
            }
            else
            {
                $cleared = true;
            }
        }

        if ($filtered === '')
        {
            nel_derp(60, _gettext('Filename was empty or was purged by filter.'));
        }

        return $filtered;
    }

    public function recursiveFileList($path, int $recursion_depth = -1, bool $include_directories = false,
            bool $file_object = true)
    {
        $file_list = array();
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        $iterator->setMaxDepth($recursion_depth);

        foreach ($iterator as $file)
        {
            if ($file->isDir() && !$include_directories)
            {
                continue;
            }

            if ($file_object)
            {
                $file_list[] = $file;
            }
            else
            {
                $file_list[] = $file->getRealPath();
            }
        }

        return $file_list;
    }

    public function umaskOffset($perm)
    {
        return octdec($perm) + umask();
    }
}