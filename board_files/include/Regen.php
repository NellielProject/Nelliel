<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Regen
{

    function __construct()
    {
    }

    public function threads($board_id, $write, $ids)
    {
        require_once INCLUDE_PATH . 'output/thread_generation.php';
        $threads = count($ids);
        $i = 0;

        while ($i < $threads)
        {
            nel_thread_generator($board_id, $write, $ids[$i]);
            ++ $i;
        }
    }

    public function boardCache($board_id)
    {
        nel_parameters_and_data()->boardSettings($board_id, null, true);
        nel_parameters_and_data()->filetypeSettings($board_id, null, true);
    }

    public function siteCache()
    {
        nel_parameters_and_data()->siteSettings(null, true);
    }

    public function index($board_id)
    {
        require_once INCLUDE_PATH . 'output/main_generation.php';
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

    public function allPages($board_id)
    {
        $dbh = nel_database();
        $result = $dbh->query(
                'SELECT "thread_id" FROM "' . nel_parameters_and_data()->boardReferences($board_id, 'thread_table') .
                '" WHERE "archive_status" = 0');
        $ids = $result->fetchAll(PDO::FETCH_COLUMN);
        $this->threads($board_id, true, $ids);
        $this->index($board_id);
    }
}