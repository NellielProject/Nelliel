<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputHeader extends OutputCore
{
    private $database;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        if(!isset($parameters['header_type']))
        {
            return;
        }

        switch ($parameters['header_type'])
        {
            case 'general':
                $output = $this->general($parameters);
                break;

            case 'board':
                $output = $this->board($parameters);
                break;
        }

        return $output;
    }

    public function general(array $parameters)
    {
        $session = new \Nelliel\Session();
        $site_domain = new \Nelliel\DomainSite($this->database);

        $dotdot = ($parameters['dotdot']) ?? array();
        $extra_data = ($parameters['extra_data']) ?? array();
        $render_input = array();
        $render_input['main_js_file'] = $dotdot . SCRIPTS_WEB_PATH . 'nel.js';
        $render_input['js_onload'] = 'window.onload = function () {nelliel.setup.doImportantStuff(\'' . $this->domain->id() . '\', \'' .
                $session->inModmode($this->domain) . '\');};';
        $render_input['js_set_style'] = 'setStyle(nelliel.core.getCookie("style-' . $this->domain->id() . '"));';

        if(isset($extra_data['use_site_titles']) && $extra_data['use_site_titles'])
        {
            $render_input['is_site_header'] = true;
            $render_input['site_name'] = $site_domain->setting('site_name');
            $render_input['site_slogan'] = $site_domain->setting('site_slogan');
            $render_input['site_banner_url'] = $site_domain->setting('site_banner');
        }
        else
        {
            $render_input['is_site_header'] = false;
        }

        $render_input['is_board_header'] = false;

        if ($site_domain->setting('show_site_favicon'))
        {
            $render_input['favicon_url'] = $site_domain->setting('site_favicon');
        }

        $render_input['page_title'] = 'Nelliel Imageboard';

        if (($session->isActive() || $session->inModmode($this->domain)) && !$this->domain->renderActive())
        {
            $render_input['session_active'] = true;

            if (isset($extra_data['header']))
            {
                $render_input['manage_header'] = $extra_data['header'];
            }

            if ($this->domain->id() !== '')
            {
                $render_input['manage_board_header'] = _gettext('Current Board:') . ' ' . $this->domain->id();
            }

            if (isset($extra_data['sub_header']))
            {
                $render_input['manage_sub_header'] = $extra_data['sub_header'];
            }

            $render_input['logout_url'] = $dotdot . MAIN_SCRIPT . '?module=logout';
        }
        else
        {
            $render_input['session_active'] = false;
        }

        if ($session->isActive() && !$this->domain->renderActive())
        {
            $render_input['manage_url'] = $dotdot . MAIN_SCRIPT . '?module=main-panel';
        }
        else
        {
            $render_input['manage_url'] = $dotdot . MAIN_SCRIPT . '?module=login';
        }

        $render_input['home_url'] = $site_domain->setting('home_page');
        $render_input['news_url'] = $dotdot . 'news.html';
        $render_input['about_nelliel_url'] = $dotdot . MAIN_SCRIPT . '?about_nelliel';
        $render_input['styles'] = $this->buildStyles($dotdot);

        $this->render_core->appendToOutput($this->render_core->renderFromTemplateFile('header', $render_input));
        return $this->render_core->getOutput();
    }

    public function board(array $parameters)
    {
        $session = new \Nelliel\Session();
        $site_domain = new \Nelliel\DomainSite($this->database);

        $dotdot = ($parameters['dotdot']) ?? array();
        $treeline = ($parameters['treeline']) ?? array();
        $index_render = ($parameters['index_render']) ?? false;
        $render_input = array();
        $render_input['main_js_file'] = $dotdot . SCRIPTS_WEB_PATH . 'nel.js';
        $render_input['js_onload'] = 'window.onload = function () {nelliel.setup.doImportantStuff(\'' .
                $this->domain->id() . '\', \'' . $session->inModmode($this->domain) . '\');};';
        $render_input['js_set_style'] = 'setStyle(nelliel.core.getCookie("style-' . $this->domain->id() . '"));';

        if ($this->domain->setting('use_honeypot'))
        {
            $render_input['honeypot_css'] = '#form-user-info-1{display: none !important;}#form-user-info-2{display: none !important;}#form-user-info-3{position: absolute; top: 3px; left: -9001px;}';
            $render_input['use_honeypot'] = true;
        }

        $title_content = $this->domain->setting('board_name');

        if(!$index_render && !empty($treeline))
        {
            if (!isset($treeline[0]['subject']) || nel_true_empty($treeline[0]['subject']))
            {
                $title_content = $this->domain->setting('board_name') . ' > Thread #' . $treeline[0]['post_number'];
            }
            else
            {
                $title_content = $this->domain->setting('board_name') . ' > ' . $treeline[0]['subject'];
            }
        }

        $render_input['page_title'] = $title_content;

        $board_data = $this->database->executeFetchAll('SELECT * FROM "' . BOARD_DATA_TABLE . '"', PDO::FETCH_ASSOC);
        $render_input['multiple_boards'] = count($board_data) > 1;

        foreach ($board_data as $data)
        {
            $board_info = array();
            $board_info['board_url'] = $dotdot . $data['board_id'];
            $board_info['board_title'] = $this->domain->setting('board_name');
            $board_info['board_id'] = $data['board_id'];
            $render_input['board_navigation'][] = $board_info;
        }

        $render_input['is_site_header'] = false;
        $render_input['is_board_header'] = true;
        $render_input['favicon_url'] = $site_domain->setting('site_favicon');
        $render_input['page_title'] = 'Nelliel Imageboard';

        if (($session->isActive() || $session->inModmode($this->domain)) && !$this->domain->renderActive())
        {
            $render_input['session_active'] = true;

            if (isset($extra_data['header']))
            {
                $render_input['manage_header'] = $extra_data['header'];
            }

            if ($session->inModmode($this->domain) && !$this->domain->renderActive())
            {
                $render_input['manage_header'] = _gettext('Mod Mode');
            }

            if ($this->domain->id() !== '')
            {
                $render_input['manage_board_header'] = _gettext('Current Board:') . ' ' . $this->domain->id();
            }

            if (isset($extra_data['sub_header']))
            {
                $render_input['manage_sub_header'] = $extra_data['sub_header'];
            }

            $render_input['logout_url'] = $dotdot . MAIN_SCRIPT . '?module=logout';
        }
        else
        {
            $render_input['session_active'] = false;
        }

        if ($session->isActive() && !$this->domain->renderActive())
        {
            $render_input['manage_url'] = $dotdot . MAIN_SCRIPT . '?module=main-panel';
        }
        else
        {
            $render_input['manage_url'] = $dotdot . MAIN_SCRIPT . '?module=login';
        }

        if ($this->domain->setting('show_board_favicon'))
        {
            $render_input['favicon_url'] = $this->domain->setting('board_favicon');
        }
        else
        {
            $render_input['favicon_url'] = $site_domain->setting('site_favicon');
        }

        if ($this->domain->setting('show_board_banner'))
        {
            $render_input['board_banner'] = $this->domain->setting('board_banner');
        }

        if ($this->domain->setting('show_board_name'))
        {
            $render_input['board_name'] = $this->domain->setting('board_name');
        }

        if ($this->domain->setting('show_board_slogan'))
        {
            $render_input['board_slogan'] = $this->domain->setting('board_slogan');
        }

        $render_input['home_url'] = $site_domain->setting('home_page');
        $render_input['news_url'] = $dotdot . 'news.html';
        $render_input['about_nelliel_url'] = $dotdot . MAIN_SCRIPT . '?about_nelliel';
        $render_input['styles'] = $this->buildStyles($dotdot);

        $this->render_core->appendToOutput($this->render_core->renderFromTemplateFile('header', $render_input));
        return $this->render_core->getOutput();
    }

    public function buildStyles(string $dotdot)
    {
        $styles = $this->database->executeFetchAll(
                'SELECT * FROM "' . ASSETS_TABLE . '" WHERE "type" = \'style\' ORDER BY "entry", "is_default" DESC',
                PDO::FETCH_ASSOC);
        $style_set = array();

        foreach ($styles as $style)
        {
            $style_data = array();
            $info = json_decode($style['info'], true);
            $style_data['stylesheet'] = ($style['is_default']) ? 'stylesheet' : 'alternate stylesheet';
            $style_data['style_id'] = $style['id'];
            $style_data['stylesheet_url'] = $dotdot . STYLES_WEB_PATH . $info['directory'] . '/' . $info['main_file'];
            $style_data['style_name'] = $info['name'];
            $style_set[] = $style_data;
        }

        return $style_set;
    }
}