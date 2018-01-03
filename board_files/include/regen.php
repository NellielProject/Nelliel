<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output-filter.php';
require_once INCLUDE_PATH . 'output/main-generation.php';
require_once INCLUDE_PATH . 'output/thread-generation.php';

function nel_regen_threads($dataforce, $write, $ids)
{
    $threads = count($ids);
    $i = 0;

    while ($i < $threads)
    {
        nel_thread_generator($dataforce, $write, $ids[$i]);
        ++ $i;
    }
}

function nel_regen_cache($dataforce)
{
    nel_cache_filetype_settings();
    nel_cache_board_settings();
    nel_cache_board_settings_new();
}

function nel_regen_index($dataforce)
{
    $archive = nel_archive();
    $archive->updateAllArchiveStatus();

    if(nel_board_settings('old_threads') === 'ARCHIVE')
    {
        $archive->moveThreadsToArchive();
        $archive->moveThreadsFromArchive();
    }
    else if(nel_board_settings('old_threads') === 'PRUNE')
    {
        $archive->pruneThreads();
    }

    $dataforce['response_id'] = 0;
    nel_main_thread_generator($dataforce, true);
}

function nel_regen_all_pages($dataforce)
{
    $dbh = nel_database();
    $result =  $dbh->query('SELECT "thread_id" FROM "' . THREAD_TABLE . '" WHERE "archive_status" = 0');
    $ids = $result->fetchAll(PDO::FETCH_COLUMN);
    nel_regen_threads($dataforce, true, $ids);
    nel_regen_index($dataforce);
}

