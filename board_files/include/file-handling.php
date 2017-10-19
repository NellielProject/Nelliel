<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Write files
//
function nel_write_file($file, $output, $chmod = FILE_PERM, $create_directories = false, $dir_chmod = DIRECTORY_PERM)
{
    if ($create_directories)
    {
        nel_create_directory(dirname($file), $dir_chmod, true);
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

function nel_create_directory($directory, $dir_chmod = DIRECTORY_PERM, $recursive = false)
{
    if (file_exists($directory))
    {
        return false;
    }

    return mkdir($directory, $dir_chmod, $recursive);
}

//
// Move files
//
function nel_move_file($file, $destination)
{
    if (file_exists($file))
    {
        rename($file, $destination);
    }
}

//
// Delete files
//
function nel_eraser_gun($path, $filename = null, $is_directory = false)
{
    if ($is_directory && file_exists($path))
    {
        $files = glob(nel_path_file_join($path, '*.*'));

        foreach ($files as $file)
        {
            unlink($file);
        }

        rmdir($path);
    }
    else if (file_exists(nel_path_file_join($path, $filename)))
    {
        unlink(nel_path_file_join($path, $filename));
    }
}

function nel_path_file_join($path, $filename)
{
    $separator = DIRECTORY_SEPARATOR;

    if(substr($path,-1) == DIRECTORY_SEPARATOR)
    {
        $separator = '';
    }
    return $path . $separator . $filename;
}

function nel_path_join($path, $path2)
{
    $separator = DIRECTORY_SEPARATOR;

    if(substr($path,-1) == DIRECTORY_SEPARATOR)
    {
        $separator = '';
    }
    return $path . $separator . $path2;
}

//
// Create default directories for a thread
//
function nel_create_thread_directories($thread_id)
{
    mkdir(SRC_PATH . $thread_id, octdec(DIRECTORY_PERM));
    mkdir(THUMB_PATH . $thread_id, octdec(DIRECTORY_PERM));
    mkdir(PAGE_PATH . $thread_id, octdec(DIRECTORY_PERM));
}

function nel_delete_thread_directories($thread_id)
{
    nel_eraser_gun(nel_path_join(PAGE_PATH, $thread_id), null, true);
    nel_eraser_gun(nel_path_join(SRC_PATH, $thread_id), null, true);
    nel_eraser_gun(nel_path_join(THUMB_PATH, $thread_id), null,true);
}
