<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputNavigation extends OutputCore
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function boardLinks(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $board_data = $this->database->executeFetchAll('SELECT * FROM "' . NEL_BOARD_DATA_TABLE . '"', PDO::FETCH_ASSOC);
        $board_count = count($board_data);
        $end = $board_count - 1;

        for ($i = 0; $i < $board_count; ++ $i)
        {
            $board_info = array();
            $board_info['board_url'] = NEL_BASE_WEB_PATH . $board_data[$i]['board_id'] . '/';
            $board_info['name'] = ''; // TODO: Get and use actual name
            $board_info['board_id'] = $board_data[$i]['board_id'];
            $board_info['end'] = $i === $end;
            $render_data['boards'][] = $board_info;
        }

        return $render_data;
    }

    public function siteLinks(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $session = new \Nelliel\Account\Session();
        $site_domain = new \Nelliel\DomainSite($this->database);
        $render_data['session_active'] = $session->isActive() && !$this->write_mode;
        $render_data['logout_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=account&section=logout';
        $render_data['main_panel_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=site-main-panel';
        $render_data['home_url'] = $site_domain->setting('home_page');
        $render_data['news_url'] = NEL_BASE_WEB_PATH . 'news.html';
        $render_data['account_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=account';
        $render_data['overboard_active'] = $this->site_domain->setting('overboard_active');
        $render_data['overboard_url'] = NEL_BASE_WEB_PATH . $this->site_domain->setting('overboard_uri') . '/';
        $render_data['sfw_overboard_active'] = $this->site_domain->setting('sfw_overboard_active');
        $render_data['sfw_overboard_url'] = NEL_BASE_WEB_PATH . $this->site_domain->setting('sfw_overboard_uri') . '/';
        $render_data['about_nelliel_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'about_nelliel';
        return $render_data;
    }
}