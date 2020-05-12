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
        $this->domain = $domain;
        $this->write_mode = $write_mode;
        $this->database = $domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        if (!isset($parameters['navigation']))
        {
            return;
        }

        switch ($parameters['navigation'])
        {
            case 'board_links':
                $output = $this->boardLinks($parameters, $data_only);
                break;

            case 'site_links':
                $output = $this->siteLinks($parameters, $data_only);
                break;
        }

        return $output;
    }

    private function boardLinks(array $parameters, bool $data_only)
    {
        $render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $dotdot = $parameters['dotdot'] ?? '';
        $board_data = $this->database->executeFetchAll('SELECT * FROM "' . NEL_BOARD_DATA_TABLE . '"', PDO::FETCH_ASSOC);
        $render_data['multiple_boards'] = count($board_data) > 1;

        foreach ($board_data as $data)
        {
            $board_info = array();
            $board_info['board_url'] = $dotdot . $data['board_uri'];
            $board_info['name'] = $this->domain->setting('name');
            $board_info['board_id'] = $data['board_id'];
            $render_data['boards'][] = $board_info;
        }

        return $render_data;
    }

    private function siteLinks(array $parameters, bool $data_only)
    {
        $render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $session = new \Nelliel\Account\Session();
        $site_domain = new \Nelliel\DomainSite($this->database);
        $dotdot = $parameters['dotdot'] ?? '';
        $render_data['session_active'] = $session->isActive() && !$this->write_mode;
        $render_data['logout_url'] = $dotdot . NEL_MAIN_SCRIPT . '?module=account&action=logout';
        $render_data['main_panel_url'] = $dotdot . NEL_MAIN_SCRIPT . '?module=main-panel';
        $render_data['home_url'] = $site_domain->setting('home_page');
        $render_data['news_url'] = $dotdot . 'news.html';
        $render_data['account_url'] = $dotdot . NEL_MAIN_SCRIPT . '?module=account';
        $render_data['about_nelliel_url'] = $dotdot . NEL_MAIN_SCRIPT . '?about_nelliel';
        return $render_data;
    }
}