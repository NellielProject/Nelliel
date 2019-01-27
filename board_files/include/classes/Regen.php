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

    private function getTemporaryDomainBoard(string $domain_id)
    {
        $domain = new DomainBoard($domain_id, new CacheHandler(), nel_database());
        $domain->renderInstance(new RenderCore());
        return $domain;
    }

    public function threads(Domain $domain, bool $write, array $ids)
    {
        require_once INCLUDE_PATH . 'output/thread_generation.php';
        $threads = count($ids);
        $i = 0;

        while ($i < $threads)
        {
            $temp_domain = $this->getTemporaryDomainBoard($domain->id());
            $temp_domain->renderActive(true);
            nel_thread_generator($temp_domain, $write, $ids[$i]);
            ++ $i;
        }
    }

    public function boardCache(Domain $domain)
    {
        $domain->regenCache();
        $filetypes = new FileTypes(nel_database());
        $filetypes->generateSettingsCache($domain->id());
    }

    public function siteCache(Domain $domain)
    {
        $domain->regenCache();
    }

    public function index(Domain $domain)
    {
        require_once INCLUDE_PATH . 'output/main_generation.php';
        $temp_domain = $this->getTemporaryDomainBoard($domain->id());
        $temp_domain->renderActive(true);
        nel_main_thread_generator($temp_domain, 0, true);
    }

    public function allPages(Domain $domain)
    {
        $database = nel_database();
        $result = $database->query(
                'SELECT "thread_id" FROM "' . $domain->reference('threads_table') . '" WHERE "archive_status" = 0');
        $ids = $result->fetchAll(PDO::FETCH_COLUMN);
        $this->threads($domain, true, $ids);
        $this->index($domain);
    }
}