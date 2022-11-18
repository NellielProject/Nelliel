<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\API\JSON\BoardsJSON;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputBlotter;
use Nelliel\Output\OutputCatalog;
use Nelliel\Output\OutputGenericPage;
use Nelliel\Output\OutputHomePage;
use Nelliel\Output\OutputIndex;
use Nelliel\Output\OutputNews;
use Nelliel\Output\OutputOverboard;
use Nelliel\Output\OutputThread;
use Nelliel\Utility\FileHandler;
use PDO;

class Regen
{

    function __construct()
    {}

    public function threads(Domain $domain, bool $write, array $ids): void
    {
        $threads = count($ids);
        $i = 0;

        while ($i < $threads) {
            $output_thread = new OutputThread($domain, $write);
            $output_thread->render(['thread_id' => $ids[$i]], false);
            $i ++;
        }
    }

    public function page(Domain $domain, string $page_uri): void
    {
        $prepared = $domain->database()->prepare(
            'SELECT * FROM "' . NEL_PAGES_TABLE . '" WHERE "domain_id" = :domain_id AND "uri" = :uri');
        $prepared->bindValue(':domain_id', $domain->id());
        $prepared->bindValue(':uri', $page_uri);
        $page = $domain->database()->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC);

        if (!$page) {
            return;
        }

        $output_generic_page = new OutputGenericPage($domain, true);
        $file_handler = new FileHandler();
        $output = $output_generic_page->render($page['title'], $page['text'], false);
        $file_handler->writeFile($domain->reference('base_path') . $page['uri'] . '.html', $output);
    }

    public function news(Domain $domain): void
    {
        $output_news = new OutputNews($domain, true);
        $output_news->render(array(), false);
    }

    public function homePage(Domain $domain): void
    {
        if ($domain->setting('generate_home_page')) {
            $output_home_page = new OutputHomePage($domain, true);
            $output_home_page->render(array(), false);
        }
    }

    public function blotter(Domain $domain): void
    {
        $output_blotter = new OutputBlotter($domain, true);
        $output_blotter->render(array(), false);
    }

    public function index(Domain $domain): void
    {
        if ($domain->setting('enable_index')) {
            $output_index = new OutputIndex($domain, true);
            $output_index->render([], false);
        }

        if ($domain->setting('enable_catalog')) {
            $output_catalog = new OutputCatalog($domain, true);
            $output_catalog->render([], false);
        }
    }

    public function overboard(Domain $domain): void
    {
        $output_overboard = new OutputOverboard($domain, true);

        if ($domain->setting('overboard_active')) {
            $output_overboard->render([], false);
        }

        if ($domain->setting('sfw_overboard_active')) {
            $output_overboard->render(['sfw' => true], false);
        }
    }

    public function allBoards(bool $pages, bool $cache): void
    {
        $domain = nel_site_domain();
        $board_ids = $domain->database()->executeFetchAll('SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"',
            PDO::FETCH_COLUMN);

        foreach ($board_ids as $id) {
            $board_domain = Domain::getDomainFromID($id, $domain->database());

            if ($cache) {
                $board_domain->regenCache();
                $board_domain->reload();
            }

            if ($pages) {
                $this->allBoardPages($board_domain);
            }
        }
    }

    public function allSitePages(Domain $domain): void
    {
        set_time_limit(nel_site_domain()->setting('max_page_regen_time'));
        $this->blotter($domain);
        $this->news($domain);
        $this->homePage($domain);
        $prepared = $domain->database()->prepare(
            'SELECT "uri" FROM "' . NEL_PAGES_TABLE . '" WHERE "domain_id" = :domain_id');
        $prepared->bindValue(':domain_id', $domain->id());
        $pages = $domain->database()->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);

        foreach ($pages as $page) {
            $this->page($domain, $page['uri']);
        }

        if (NEL_ENABLE_JSON_API) {
            $boards_json = new BoardsJSON();
            $json_filename = 'boards' . NEL_JSON_EXT;
            nel_utilities()->fileHandler()->writeFile(NEL_PUBLIC_PATH . $json_filename,
                $boards_json->getJSON());
        }
    }

    public function allBoardPages(Domain $domain): void
    {
        set_time_limit(nel_site_domain()->setting('max_page_regen_time'));
        $result = $domain->database()->query(
            'SELECT "thread_id" FROM "' . $domain->reference('threads_table') . '" WHERE "old" = 0');
        $ids = $result->fetchAll(PDO::FETCH_COLUMN);
        $domain->database()->query('UPDATE "' . $domain->reference('posts_table') . '" SET regen_cache = 1');
        $this->threads($domain, true, $ids);
        $this->index($domain);
    }
}