<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output_filter.php';
require_once INCLUDE_PATH . 'output/main_generation.php';
require_once INCLUDE_PATH . 'output/thread_generation.php';

function nel_regen_threads($dataforce, $board_id, $write, $ids)
{
    $threads = count($ids);
    $i = 0;

    while ($i < $threads)
    {
        nel_thread_generator($dataforce, $board_id, $write, $ids[$i]);
        ++ $i;
    }
}

function nel_regen_cache($board_id, $dataforce)
{
    nel_cache_filetype_settings($board_id);
    nel_cache_board_settings($board_id);
}

function nel_regen_index($dataforce, $board_id)
{
    $archive = nel_archive($board_id);
    $archive->updateAllArchiveStatus();

    if(nel_board_settings($board_id, 'old_threads') === 'ARCHIVE')
    {
        $archive->moveThreadsToArchive();
        $archive->moveThreadsFromArchive();
    }
    else if(nel_board_settings($board_id, 'old_threads') === 'PRUNE')
    {
        $archive->pruneThreads();
    }

    $dataforce['response_id'] = 0;
    nel_main_thread_generator($dataforce, $board_id, true);
}

function nel_regen_all_pages($dataforce, $board_id)
{
    $dbh = nel_database();
    $result =  $dbh->query('SELECT "thread_id" FROM "' . nel_board_references($board_id, 'thread_table') . '" WHERE "archive_status" = 0');
    $ids = $result->fetchAll(PDO::FETCH_COLUMN);
    nel_regen_threads($dataforce, $board_id, true, $ids);
    nel_regen_index($dataforce, $board_id);
}

