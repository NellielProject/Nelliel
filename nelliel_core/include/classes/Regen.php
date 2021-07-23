<?php

declare(strict_types=1);

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\API\JSON\JSONBoard;
use Nelliel\API\JSON\JSONBoardList;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;
use Nelliel\Output\OutputCatalog;
use Nelliel\Output\OutputIndex;
use Nelliel\Output\OutputNews;
use Nelliel\Output\OutputOverboard;
use Nelliel\Output\OutputThread;
use PDO;

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
            $output_thread = new OutputThread($domain, $write);
            $output_thread->render(['thread_id' => $ids[$i]], false);
            $i ++;
        }
    }

    public function news(Domain $domain)
    {
        $news = new OutputNews($domain, true);
        $news->render(array(), false);
    }

    public function index(Domain $domain)
    {
        $output_thread = new OutputIndex($domain, true);
        $output_thread->render(['thread_id' => 0], false);
        $output_catalog = new OutputCatalog($domain, true);
        $output_catalog->render([], false);
    }

    public function overboard(Domain $domain)
    {
        $output_overboard = new OutputOverboard($domain, true);

        if ($domain->setting('overboard_active'))
        {
            $output_overboard->render([], false);
        }

        if ($domain->setting('sfw_overboard_active'))
        {
            $output_overboard->render(['sfw' => true], false);
        }
    }

    public function boardList(Domain $domain)
    {
        $board_json = new JSONBoard($domain, nel_utilities()->fileHandler());
        $board_list_json = new JSONBoardList($domain, nel_utilities()->fileHandler());
        $board_ids = $domain->database()->executeFetchAll('SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"',
                PDO::FETCH_COLUMN);

        foreach ($board_ids as $id)
        {
            $board_domain = new DomainBoard($id, $domain->database());
            $board_config = $domain->database()->executeFetchAll(
                    'SELECT "setting_name", "setting_value" FROM "' . $board_domain->reference('config_table') . '"',
                    PDO::FETCH_ASSOC);
            $board_data = ['board_id' => $id];

            foreach ($board_config as $config)
            {
                $board_data[$config['setting_name']] = $config['setting_value'];
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
        $domain->database()->query(
                'UPDATE "' . $domain->reference('posts_table') . '" SET regen_cache = 1');
        $this->threads($domain, true, $ids);
        $this->index($domain);
    }

    public function postCache(Domain $domain)
    {
        $result = $domain->database()->query(
                'SELECT "thread_id" FROM "' . $domain->reference('threads_table') . '" WHERE "archive_status" = 0');
        $ids = $result->fetchAll(PDO::FETCH_COLUMN);
        $this->threads($domain, true, $ids);
        $this->index($domain);
    }
}