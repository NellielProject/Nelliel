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

    public function threads(Domain $domain, bool $write, array $ids)
    {
        $threads = count($ids);
        $i = 0;

        while ($i < $threads)
        {
            $output_thread = new \Nelliel\Output\OutputThread($domain);
            $output_thread->render(['write' => $write, 'thread_id' => $ids[$i]], false);
            ++ $i;
        }
    }

    public function boardCache(Domain $domain)
    {
        $domain->regenCache();
        $filetypes = new FileTypes($domain->database());
        $filetypes->generateSettingsCache($domain->id());
    }

    public function siteCache(Domain $domain)
    {
        $domain->regenCache();
    }

    public function news(Domain $domain)
    {
        $news = new \Nelliel\Output\OutputNews($domain);
        $news->render(array(), false);
    }

    public function index(Domain $domain)
    {
        $output_thread = new \Nelliel\Output\OutputIndex($domain);
        $output_thread->render(['write' => true, 'thread_id' => 0], false);
        $output_catalog = new \Nelliel\Output\OutputCatalog($domain);
        $output_catalog->render(['write' => true], false);
    }

    public function boardList(Domain $domain)
    {
        $board_json = new \Nelliel\API\JSON\JSONBoard($domain, new \Nelliel\Utility\FileHandler());
        $board_list_json = new \Nelliel\API\JSON\JSONBoardList($domain, new \Nelliel\Utility\FileHandler());
        $board_ids = $domain->database()->executeFetchAll('SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"', PDO::FETCH_COLUMN);

        foreach($board_ids as $id)
        {
            $board_domain = new DomainBoard($id, $domain->database());
            $board_config = $domain->database()->executeFetchAll('SELECT "config_name", "setting" FROM "' . $board_domain->reference('config_table') . '"', PDO::FETCH_ASSOC);
            $board_data = ['board_id' => $id];

            foreach($board_config as $config)
            {
                $board_data[$config['config_name']] = $config['setting'];
            }

            $board_list_json->addBoardData($board_json->prepareData($board_data));
        }

        $board_list_json->writeStoredData(NEL_BASE_PATH, 'boards');
    }

    public function allSitePages(Domain $domain)
    {
        $this->boardList($domain);
    }

    public function allBoardPages(Domain $domain)
    {
        $result = $domain->database()->query(
                'SELECT "thread_id" FROM "' . $domain->reference('threads_table') . '" WHERE "archive_status" = 0');
        $ids = $result->fetchAll(PDO::FETCH_COLUMN);
        $this->threads($domain, true, $ids);
        $this->index($domain);
    }
}