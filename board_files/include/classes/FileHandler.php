<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class FileHandler
{

    function __construct()
    {
    }

    public function writeFile($file, $output, $chmod = FILE_PERM, $create_directories = false, $dir_chmod = DIRECTORY_PERM)
    {
        if ($create_directories)
        {
            $this->createDirectory(dirname($file), $dir_chmod, true);
        }

        $fp = fopen($file, "w");

        if (!$fp)
        {
            echo 'Failed to open file for writing. Check permissions.';
            return false;
        }

        set_file_buffer($fp, 0);
        rewind($fp);
        fputs($fp, $output);
        fclose($fp);
        chmod($file, octdec($chmod));
        return true;
    }

    public function createDirectory($directory, $dir_chmod = DIRECTORY_PERM, $recursive = false)
    {
        clearstatcache();

        if (is_dir($directory))
        {
            return false;
        }

        return @mkdir($directory, octdec($dir_chmod), $recursive);
    }

    public function moveFile($file, $destination, $create_directories = false, $dir_chmod = DIRECTORY_PERM)
    {
        clearstatcache();

        if ($create_directories)
        {
            $this->createDirectory(dirname($destination), $dir_chmod, true);
        }

        if (file_exists($file))
        {
            rename($file, $destination);
        }
    }

    public function eraserGun($path, $filename = null, $is_directory = false)
    {
        clearstatcache();

        if ($is_directory && file_exists($path))
        {
            $files = glob($this->pathFileJoin($path, '*.*'));

            foreach ($files as $file)
            {
                unlink($file);
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

        // https://msdn.microsoft.com/en-us/library/aa365247(VS.85).aspx
        $filtered = preg_replace('#[<>:"\/\\|?*]#u', '', $filtered); // Reserved characters for Windows
        $filtered = preg_replace('#(com[1-9]|lpt[1-9]|con|prn|aux|nul)\.?[a-zA-Z0-9]*#ui', '', $filtered); // Reserved names for Windows

        $filtered = preg_replace('#^[ -.]|\'#', '', $filtered); // Other potentially troublesome characters
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
            nel_derp(111, nel_stext('ERROR_111'));
        }

        return $filtered;
    }
}