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

            case 'simple':
                $output = $this->simple($parameters);
                break;
        }

        return $output;
    }

    public function general(array $parameters)
    {
        $session = new \Nelliel\Session();
        $site_domain = new \Nelliel\DomainSite($this->database);
        $final_output = '';

        // Temp
        $this->render_instance = $this->domain->renderInstance();

        $template_loader = new \Mustache_Loader_FilesystemLoader($this->domain->templatePath(), ['extension' => '.html']);
        $render_instance = new \Mustache_Engine(['loader' => $template_loader]);
        $template_loader->load('header2');
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
        $render_input['favicon_url'] = $site_domain->setting('site_favicon');
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

        // Temp
        $this->domain->renderInstance()->appendHTML($render_instance->render('header2', $render_input));

        //return $render_instance->render('header2', $render_input);
    }

    public function board(array $parameters)
    {
        $session = new \Nelliel\Session();
        $site_domain = new \Nelliel\DomainSite($this->database);
        $this->prepare('header.html');
        $dotdot = ($parameters['dotdot']) ?? array();
        $treeline = ($parameters['treeline']) ?? array();
        $index_render = ($parameters['index_render']) ?? false;
        $head_element = $this->dom->getElementsByTagName('head')->item(0);
        $this->buildStyles($dotdot);
        $this->dom->getElementById('js-main-file')->extSetAttribute('src', $dotdot . SCRIPTS_WEB_PATH . 'nel.js');
        $this->dom->getElementById('js-onload')->setContent(
                'window.onload = function () {nelliel.setup.doImportantStuff(\'' . $this->domain->id() . '\', \'' .
                $session->inModmode($this->domain) . '\');};');
        $this->dom->getElementById('js-style-set')->setContent('setStyle(nelliel.core.getCookie("style-' . $this->domain->id() . '"));');

        if ($this->domain->setting('use_honeypot'))
        {
            $honeypot_css = '#form-user-info-1{display: none !important;}#form-user-info-2{display: none !important;}#form-user-info-3{position: absolute; top: 3px; left: -9001px;}';
            $style_element = $this->dom->createElement('style', $honeypot_css);
            $this->dom->getElementsByTagName('head')->item(0)->appendChild($style_element);
        }

        $title_element = $head_element->getElementsByTagName('title')->item(0);
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

        $title_element->setContent($title_content);
        $board_navigation = $this->dom->getElementById("board-navigation");
        $board_navigation->appendChild($this->dom->createTextNode('[ '));
        $board_data = $this->database->executeFetchAll('SELECT * FROM "' . BOARD_DATA_TABLE . '"', PDO::FETCH_ASSOC);
        $end = end($board_data);

        foreach ($board_data as $data)
        {
            $board_link = $this->dom->createElement('a');
            $board_link->extSetAttribute('class', 'board-navigation-link');
            $board_link->extSetAttribute('href', $dotdot . $data['board_id']);
            $board_link->extSetAttribute('title', $this->domain->setting('board_name'));
            $board_link->setContent($data['board_id']);
            $board_navigation->appendChild($board_link);

            if ($data !== $end)
            {
                $board_navigation->appendChild($this->dom->createTextNode(' / '));
            }
        }

        $board_navigation->appendChild($this->dom->createTextNode(' ]'));
        $board_banner = $this->dom->getElementById('top-board-banner');
        $favicon = $this->dom->getElementById('favicon-link');

        if ($this->domain->setting('show_board_favicon'))
        {
            $favicon->extSetAttribute('href', $this->domain->setting('board_favicon'));
        }
        else
        {
            $favicon->extSetAttribute('href', $site_domain->setting('site_favicon'));
        }

        $top_site_name = $this->dom->getElementById('top-site-name')->remove();
        $top_site_slogan = $this->dom->getElementById('top-site-slogan')->remove();
        $top_site_banner = $this->dom->getElementById('top-site-banner')->remove();

        if ($this->domain->setting('show_board_banner'))
        {
            $board_banner->extSetAttribute('src', $this->domain->setting('board_banner'));
        }
        else
        {
            $board_banner->remove();
        }

        $board_name = $this->dom->getElementById('top-board-name');

        if ($this->domain->setting('show_board_name'))
        {
            $board_name->setContent($this->domain->setting('board_name'));
        }
        else
        {
            $board_name->remove();
        }

        $board_slogan = $this->dom->getElementById('top-board-slogan');

        if ($this->domain->setting('show_board_slogan'))
        {
            $board_slogan->setContent($this->domain->setting('board_slogan'));
        }
        else
        {
            $board_slogan->remove();
        }

        $top_nav_menu = $this->dom->getElementById('top-nav-menu');
        $top_nav_menu_nodes = $top_nav_menu->getElementsByAttributeName('data-parse-id', true);
        $top_nav_menu_nodes['home']->extSetAttribute('href', $site_domain->setting('home_page'));
        $top_nav_menu_nodes['news']->extSetAttribute('href', $dotdot . 'news.html');

        if ($session->isActive() && !$this->domain->renderActive())
        {
            $top_nav_menu_nodes['manage']->extSetAttribute('href', $dotdot . MAIN_SCRIPT . '?module=main-panel');
        }
        else
        {
            $top_nav_menu_nodes['manage']->extSetAttribute('href', $dotdot . MAIN_SCRIPT . '?module=login');
        }

        $top_nav_menu_nodes['about-nelliel']->extSetAttribute('href', $dotdot . MAIN_SCRIPT . '?about_nelliel');

        $this->dom->getElementById('manage-board-header')->remove();
        $this->dom->getElementById('manage-sub-header')->remove();

        if ($session->inModmode($this->domain) && !$this->domain->renderActive())
        {
            $this->dom->getElementById('manage-header-text')->setContent(_gettext('Mod Mode'));
            $top_nav_menu_nodes['logout']->extSetAttribute('href', $dotdot . MAIN_SCRIPT . '?module=logout');
        }
        else
        {
            $top_nav_menu_nodes['logout']->parentNode->remove();
            $this->dom->getElementById('manage-header')->remove();
        }

        $this->domain->translator()->translateDom($this->dom, $this->domain->setting('language'));
        $this->domain->renderInstance()->appendHTMLFromDOM($this->dom);
    }

    public function simple(array $parameters)
    {
        $site_domain = new \Nelliel\DomainSite($this->database);
        $this->prepare('header.html');
        $dotdot = (!empty($dotdot)) ? $dotdot : '';
        $head_element = $this->dom->getElementsByTagName('head')->item(0);
        $this->dom->getElementById('js-main-file')->extSetAttribute('src', $dotdot . SCRIPTS_WEB_PATH . 'nel.js');
        $style_data = $this->database->executeFetch(
                'SELECT * FROM "' . ASSETS_TABLE . '" WHERE "type" = \'style\' AND "is_default" = 1', PDO::FETCH_ASSOC);
        $style_info = json_decode($style_data['info'], true);
        $style_link = $this->dom->createElement('link');
        $style_link->extSetAttribute('rel', 'stylesheet');
        $style_link->extSetAttribute('type', 'text/css');
        $style_link->extSetAttribute('href',
                $dotdot . STYLES_WEB_PATH . $style_info['directory'] . '/' . $style_info['main_file']);
        $head_element->appendChild($style_link);

        $favicon = $this->dom->getElementById('favicon-link');

        if ($site_domain->setting('show_site_favicon'))
        {
            $favicon->extSetAttribute('href', $site_domain->setting('site_favicon'));
        }
        else
        {
            $favicon->remove();
        }

        $this->domain->translator()->translateDom($this->dom, $this->domain->setting('language'));
        $this->domain->renderInstance()->appendHTMLFromDOM($this->dom);
    }

    public function buildStyles(string $dotdot)
    {
        $styles = $this->database->executeFetchAll(
                'SELECT * FROM "' . ASSETS_TABLE . '" WHERE "type" = \'style\' ORDER BY "entry", "is_default" DESC',
                PDO::FETCH_ASSOC);
        $style_data = array();

        foreach ($styles as $style)
        {
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