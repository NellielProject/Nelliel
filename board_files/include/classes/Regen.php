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

    private function getTemporaryDomain($domain_id)
    {
        $domain = new \Nelliel\Domain($domain_id, new \Nelliel\CacheHandler(), nel_database());
        $domain->renderInstance(new \NellielTemplates\RenderCore());
        return $domain;
    }

    public function threads($domain, $write, $ids)
    {
        require_once INCLUDE_PATH . 'output/thread_generation.php';
        $threads = count($ids);
        $i = 0;

        while ($i < $threads)
        {
            nel_thread_generator($this->getTemporaryDomain($domain->id()), $write, $ids[$i]);
            ++ $i;
        }
    }


    public function boardCache($domain)
    {
        $domain->regenCache();
        $filetypes = new FileTypes(nel_database());
        $filetypes->generateSettingsCache($domain->id());
    }

    public function siteCache($domain)
    {
        $domain->regenCache();
    }

    public function index($domain)
    {
        require_once INCLUDE_PATH . 'output/main_generation.php';
        nel_main_thread_generator($this->getTemporaryDomain($domain->id()), 0, true);
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