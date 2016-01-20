<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Write files
//
function nel_write_file($filename, $output, $chmod)
{
    $fp = fopen($filename, "w");

    if (!$fp)
    {
        echo 'Failed to open file for writing. Check permissions.';
        return FALSE;
    }

    set_file_buffer($fp, 0);
    rewind($fp);
    fputs($fp, $output);
    fclose($fp);
    chmod($filename, $chmod);
    return TRUE;
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
function nel_eraser_gun($path, $filename, $multi)
{
    if ($multi && file_exists($path))
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
    mkdir(SRC_PATH . $thread_id, 0777);
    chmod(SRC_PATH . $thread_id, 0777);
    mkdir(THUMB_PATH . $thread_id, 0777);
    chmod(THUMB_PATH . $thread_id, 0777);
    mkdir(PAGE_PATH . $thread_id, 0777);
    chmod(PAGE_PATH . $thread_id, 0777);
}
?>