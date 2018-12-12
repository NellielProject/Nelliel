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

    public function threads($domain, $write, $ids)
    {
        require_once INCLUDE_PATH . 'output/thread_generation.php';
        $threads = count($ids);
        $i = 0;

        while ($i < $threads)
        {
            nel_thread_generator($domain, $write, $ids[$i]);
            ++ $i;
        }
    }

    public function boardCache($domain)
    {
        $domain->regenCache();
        $filetypes = new FileTypes(nel_database());
        $filetypes->generateSettingsCache($domain->id());
    }

    public function siteCache()
    {
        nel_parameters_and_data()->siteSettings(null, true);
    }

    public function index($domain)
    {
        require_once INCLUDE_PATH . 'output/main_generation.php';
        nel_main_thread_generator($domain, 0, true);
    }

    public function allPages($domain)
    {
        $database = nel_database();
        $result = $database->query(
                'SELECT "thread_id" FROM "' . nel_parameters_and_data()->boardReferences($domain->id(), 'thread_table') .
                '" WHERE "archive_status" = 0');
        $ids = $result->fetchAll(PDO::FETCH_COLUMN);
        $this->threads($domain, true, $ids);
        $this->index($domain);
    }
}