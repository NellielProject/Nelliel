<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\API\JSON\BoardsJSON;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;
use Nelliel\Domains\DomainSite;
use Nelliel\Output\OutputBlotter;
use Nelliel\Output\OutputCatalog;
use Nelliel\Output\OutputGenericPage;
use Nelliel\Output\OutputHomePage;
use Nelliel\Output\OutputIndex;
use Nelliel\Output\OutputNews;
use Nelliel\Output\OutputOverboard;
use Nelliel\Output\OutputThread;
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
        $output = $output_generic_page->render($page['title'], $page['text'], false);
        nel_utilities()->fileHandler()->writeFile($domain->reference('base_path') . $page['uri'] . '.html', $output);
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

    public function overboard(DomainSite $site_domain): void
    {
        $output_overboard = new OutputOverboard(nel_global_domain(), true);

        if ($site_domain->setting('overboard_active')) {
            $output_overboard->index(
                ['uri' => $site_domain->setting('overboard_uri'), 'name' => $site_domain->setting('overboard_name')],
                false);
            $output_overboard->catalog(
                ['overboard_id' => 'all', 'uri' => $site_domain->setting('overboard_uri'),
                    'catalog' => $site_domain->setting('overboard_catalog'),
                    'name' => $site_domain->setting('overboard_name')], false);
        } else {
            $path = NEL_PUBLIC_PATH . $site_domain->setting('overboard_uri');

            if (file_exists($path)) {
                nel_utilities()->fileHandler()->eraserGun($path);
            }
        }

        if ($site_domain->setting('sfw_overboard_active')) {
            $output_overboard->index(
                ['uri' => $site_domain->setting('sfw_overboard_uri'),
                    'name' => $site_domain->setting('sfw_overboard_name'), 'sfw' => true], false);
            $output_overboard->catalog(
                ['overboard_id' => 'sfw', 'uri' => $site_domain->setting('sfw_overboard_uri'),
                    'catalog' => $site_domain->setting('sfw_overboard_catalog'),
                    'name' => $site_domain->setting('sfw_overboard_name')], false);
        } else {
            $path = NEL_PUBLIC_PATH . $site_domain->setting('sfw_overboard_uri');

            if (file_exists($path)) {
                nel_utilities()->fileHandler()->eraserGun($path);
            }
        }

        nel_plugins()->processHook('nel-in-after-regen-overboard', [$site_domain]);
    }

    public function allBoards(bool $pages, bool $cache): void
    {
        $site_domain = nel_site_domain();
        $board_ids = $site_domain->database()->executeFetchAll('SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"',
            PDO::FETCH_COLUMN);

        foreach ($board_ids as $id) {
            $board_domain = Domain::getDomainFromID($id, $site_domain->database());

            if ($cache) {
                $board_domain->regenCache();
                $board_domain->reload();
            }

            if ($pages) {
                $this->boardPages($board_domain);
            }
        }
    }

    public function sitePages(DomainSite $site_domain): void
    {
        set_time_limit($site_domain->setting('max_page_regen_time'));
        $this->blotter($site_domain);
        $this->news($site_domain);
        $this->homePage($site_domain);
        $prepared = $site_domain->database()->prepare(
            'SELECT "uri" FROM "' . NEL_PAGES_TABLE . '" WHERE "domain_id" = :domain_id');
        $prepared->bindValue(':domain_id', $site_domain->id());
        $pages = $site_domain->database()->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);

        foreach ($pages as $page) {
            $this->page($site_domain, $page['uri']);
        }

        if (NEL_ENABLE_JSON_API) {
            $boards_json = new BoardsJSON();
            $json_filename = 'boards' . NEL_JSON_EXT;
            nel_utilities()->fileHandler()->writeFile(NEL_PUBLIC_PATH . $json_filename, $boards_json->getJSON(true));
        }

        nel_plugins()->processHook('nel-in-after-regen-site-pages', [$site_domain]);
    }

    public function boardPages(DomainBoard $board_domain): void
    {
        set_time_limit(nel_site_domain()->setting('max_page_regen_time'));
        $result = $board_domain->database()->query(
            'SELECT "thread_id" FROM "' . $board_domain->reference('threads_table') . '" WHERE "old" = 0');
        $ids = $result->fetchAll(PDO::FETCH_COLUMN);
        $board_domain->database()->query('UPDATE "' . $board_domain->reference('posts_table') . '" SET regen_cache = 1');
        $this->threads($board_domain, true, $ids);
        $this->index($board_domain);
        nel_plugins()->processHook('nel-in-after-regen-board-pages', [$board_domain]);
    }
}