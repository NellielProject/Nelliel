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

    function __construct(Domain $domain, $database)
    {
        $this->domain = $domain;
        $this->database = $database;
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
                $this->general();
                break;

            case 'board':
                $this->board($parameters);
                break;

            case 'simple':
                $this->simple();
                break;
        }
    }

    public function general()
    {

    }

    public function board(array $parameters)
    {
        $authorization = new \Nelliel\Auth\Authorization($this->database);
        $session = new \Nelliel\Session($authorization);
        $site_domain = new \Nelliel\DomainSite(new \Nelliel\CacheHandler(), $this->database, new \Nelliel\Language\Translator());
        $this->prepare('header.html');
        $dotdot = ($parameters['dotdot']) ?? array();
        $treeline = ($parameters['treeline']) ?? array();
        $index_render = ($parameters['index_render']) ?? false;
        $head_element = $this->dom->getElementsByTagName('head')->item(0);
        nel_build_header_styles($this->dom, $dotdot);
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

    public function simple()
    {

    }
}