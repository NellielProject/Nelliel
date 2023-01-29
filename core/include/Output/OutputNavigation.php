<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputNavigation extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function boardLinks(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $board_ids = $this->database->executeFetchAll('SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"',
            PDO::FETCH_COLUMN);
        $board_count = count($board_ids);
        $end = $board_count - 1;
        $render_data = array();

        for ($i = 0; $i < $board_count; $i ++) {
            $board = Domain::getDomainFromID($board_ids[$i], $this->database);
            $board_info = array();
            $board_info['board_url'] = $board->reference('board_web_path');
            $board_info['name'] = ''; // TODO: Get and use actual name
            $board_info['board_uri'] = $board->reference('board_uri');
            $board_info['end'] = $i === $end;
            $render_data['boards'][] = $board_info;
        }

        return $render_data;
    }

    public function siteLinks(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $render_data['session_active'] = $this->session->isActive() && !$this->write_mode;
        $render_data['board_area'] = $this->domain->id() !== Domain::SITE && $this->domain->id() !== Domain::GLOBAL;
        $render_data['login_url'] = nel_build_router_url([Domain::SITE, 'account', 'login']);
        $render_data['logout_url'] = nel_build_router_url([Domain::SITE, 'account', 'logout']);
        $render_data['site_panel_url'] = nel_build_router_url([Domain::SITE, 'main-panel']);
        $render_data['global_panel_url'] = nel_build_router_url([Domain::GLOBAL, 'main-panel']);
        $render_data['board_panel_url'] = nel_build_router_url([$this->domain->id(), 'main-panel']);
        $render_data['home_url'] = $this->site_domain->reference('home_page');
        $render_data['news_url'] = NEL_BASE_WEB_PATH . 'news.html';
        $render_data['faq_url'] = NEL_BASE_WEB_PATH . 'faq.html';
        $render_data['account_url'] = nel_build_router_url([Domain::SITE, 'account']);
        $render_data['boardlist_url'] = nel_build_router_url([Domain::SITE, 'boardlist']);
        $render_data['overboard_active'] = $this->site_domain->setting('overboard_active');
        $render_data['overboard_url'] = NEL_BASE_WEB_PATH . $this->site_domain->setting('overboard_uri') . '/';
        $render_data['sfw_overboard_active'] = $this->site_domain->setting('sfw_overboard_active');
        $render_data['sfw_overboard_url'] = NEL_BASE_WEB_PATH . $this->site_domain->setting('sfw_overboard_uri') . '/';
        $render_data['about_nelliel_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'about_nelliel';
        $render_data['blank_page_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'blank';
        return $render_data;
    }

    public function boardPages(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $in_modmode = $parameters['in_modmode'] ?? false;
        $render_data = array();

        if (!$this->write_mode) {
            $render_data['catalog_url'] = nel_build_router_url([$this->domain->id(), 'catalog'], true);
            $render_data['index_url'] = nel_build_router_url([$this->domain->id(), 'index'], true);
        }

        if ($in_modmode) {
            $render_data['form_action'] = nel_build_router_url([$this->domain->id(), 'threads'], false, 'modmode');
            $render_data['catalog_url'] = nel_build_router_url([$this->domain->id(), 'catalog'], true, 'modmode');
            $render_data['index_url'] = nel_build_router_url([$this->domain->id()], true, 'modmode');
        } else {
            $render_data['form_action'] = nel_build_router_url([$this->domain->id(), 'threads']);
            $render_data['catalog_url'] = 'catalog.html';
            $render_data['index_url'] = 'index.html';
        }

        $render_data['show_catalog_link'] = $this->domain->setting('enable_catalog') &&
            $this->domain->setting('show_catalog_link');
        $render_data['show_index_link'] = $this->domain->setting('enable_index') &&
            $this->domain->setting('show_index_link');

        return $render_data;
    }
}