<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/posting_form.php';
require_once INCLUDE_PATH . 'output/header.php';
require_once INCLUDE_PATH . 'output/post.php';
require_once INCLUDE_PATH . 'output/footer.php';
require_once INCLUDE_PATH . 'output/main_generation.php';
require_once INCLUDE_PATH . 'output/thread_generation.php';

function nel_regen_threads($board_id, $write, $ids)
{
    $threads = count($ids);
    $i = 0;

    while ($i < $threads)
    {
        nel_thread_generator($board_id, $write, $ids[$i]);
        ++ $i;
    }
}

function nel_regen_board_cache($board_id)
{
    nel_parameters_and_data()->boardSettings($board_id, null, true);
    nel_parameters_and_data()->filetypeSettings($board_id, null, true);
}

function nel_regen_site_cache()
{
    nel_parameters_and_data()->siteSettings(null, true);
}

function nel_regen_index($board_id)
{
    $archive = new \Nelliel\ArchiveAndPrune($board_id);
    $archive->updateAllArchiveStatus();

    if (nel_parameters_and_data()->boardSettings($board_id, 'old_threads') === 'ARCHIVE')
    {
        $archive->moveThreadsToArchive();
        $archive->moveThreadsFromArchive();
    }
    else if (nel_parameters_and_data()->boardSettings($board_id, 'old_threads') === 'PRUNE')
    {
        $archive->pruneThreads();
    }

    nel_main_thread_generator($board_id, 0, true);
}

function nel_regen_all_pages($board_id)
{
    $dbh = nel_database();
    $result = $dbh->query('SELECT "thread_id" FROM "' . nel_parameters_and_data()->boardReferences($board_id, 'thread_table') .
         '" WHERE "archive_status" = 0');
    $ids = $result->fetchAll(PDO::FETCH_COLUMN);
    nel_regen_threads($board_id, true, $ids);
    nel_regen_index($board_id);
}

