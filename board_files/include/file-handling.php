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
function nel_move_file($location, $destination)
{
    if (file_exists($location))
    {
        rename($location, $destination);
    }
}

//
// Delete files
//
function nel_eraser_gun($path, $is_directory = false, $filename = null)
{
    if ($is_directory && file_exists($path))
    {
        $files = glob($path . "/*.*");

        foreach ($files as $file)
        {
            unlink($file);
        }

        rmdir($path);
    }
    else if (file_exists($path . "/" . $filename))
    {
        unlink($path . "/" . $filename);
    }
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
    nel_eraser_gun(PAGE_PATH . $thread_id, true);
    nel_eraser_gun(SRC_PATH . $thread_id, true);
    nel_eraser_gun(THUMB_PATH . $thread_id, true);
}

function nel_remove_post_file($path, $filename, $preview_name = null)
{
    nel_eraser_gun($path, $filename);

    if (!nel_true_empty($preview_name))
    {
        nel_eraser_gun($path, $preview_name);
    }
}