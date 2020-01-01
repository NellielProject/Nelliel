<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputMenu extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        if (!isset($parameters['menu']))
        {
            return;
        }

        switch ($parameters['menu'])
        {
            case 'boards':
                $output = $this->boards($parameters, $data_only);
                break;

            case 'styles':
                $output = $this->styles($parameters, $data_only);
                break;

            case 'site_navigation':
                $output = $this->siteNavigation($parameters, $data_only);
                break;
        }

        return $output;
    }

    private function boards(array $parameters, bool $data_only)
    {
        $render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $dotdot = ($parameters['dotdot']) ?? '';
        $board_data = $this->database->executeFetchAll('SELECT * FROM "' . BOARD_DATA_TABLE . '"', PDO::FETCH_ASSOC);
        $render_data['multiple_boards'] = count($board_data) > 1;

        foreach ($board_data as $data)
        {
            $board_info = array();
            $board_info['board_url'] = $dotdot . $data['board_id'];
            $board_info['name'] = $this->domain->setting('name');
            $board_info['board_id'] = $data['board_id'];
            $render_data['boards'][] = $board_info;
        }

        return $render_data;
    }

    private function styles(array $parameters, bool $data_only)
    {
        $render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $dotdot = ($parameters['dotdot']) ?? '';
        $styles = $this->database->executeFetchAll(
                'SELECT * FROM "' . ASSETS_TABLE . '" WHERE "type" = \'style\' ORDER BY "entry", "is_default" DESC',
                PDO::FETCH_ASSOC);

        foreach ($styles as $style)
        {
            $style_data = array();
            $info = json_decode($style['info'], true);
            $style_data['stylesheet'] = ($style['is_default']) ? 'stylesheet' : 'alternate stylesheet';
            $style_data['style_id'] = $style['id'];
            $style_data['stylesheet_url'] = $dotdot . STYLES_WEB_PATH . $info['directory'] . '/' . $info['main_file'];
            $style_data['style_name'] = $info['name'];
            $render_data[] = $style_data;
        }

        return $render_data;
    }

    private function siteNavigation(array $parameters, bool $data_only)
    {
        $render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $session = new \Nelliel\Session();
        $site_domain = new \Nelliel\DomainSite($this->database);
        $dotdot = ($parameters['dotdot']) ?? '';
        $ignore_session = $parameters['ignore_session'] ?? false;

        if ($session->isActive() && !$ignore_session)
        {
            $render_data['session_active'] = true;
            $render_data['manage_url'] = $dotdot . MAIN_SCRIPT . '?module=main-panel';
            $render_data['logout_url'] = $dotdot . MAIN_SCRIPT . '?module=logout';
        }
        else
        {
            $render_data['session_active'] = false;
            $render_data['manage_url'] = $dotdot . MAIN_SCRIPT . '?module=login';
        }

        $render_data['home_url'] = $site_domain->setting('home_page');
        $render_data['news_url'] = $dotdot . 'news.html';
        $render_data['about_nelliel_url'] = $dotdot . MAIN_SCRIPT . '?about_nelliel';
        return $render_data;
    }
}