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

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters = array(), bool $data_only = false)
    {
        if (!isset($parameters['header_type']))
        {
            return;
        }

        switch ($parameters['header_type'])
        {
            case 'general':
                $output = $this->general($parameters, $data_only);
                break;

            case 'board':
                $output = $this->board($parameters, $data_only);
                break;
        }

        return $output;
    }

    private function general(array $parameters, bool $data_only)
    {
        $render_data = array();
        $session = new \Nelliel\Session();
        $site_domain = new \Nelliel\DomainSite($this->database);
        $dotdot = $parameters['dotdot'] ?? '';
        $extra_data = $parameters['extra_data'] ?? array();
        $render_data['show_styles'] = $parameters['show_styles'] ?? true;
        $render_data['session_active'] = $session->isActive();
        $output_head = new OutputHead($this->domain);
        $render_data['head'] = $output_head->render(['dotdot' => $dotdot]);
        $output_menu = new OutputMenu($this->domain);
        $render_data['show_manage_headers'] = $session->inModmode($this->domain);

        if ($render_data['show_styles'])
        {
            $render_data['styles'] = $output_menu->render(['menu' => 'styles', 'dotdot' => $dotdot]);
        }

        $render_data['site_navigation'] = $output_menu->render(['menu' => 'site_navigation', 'dotdot' => $dotdot]);

        if (isset($extra_data['use_site_titles']) && $extra_data['use_site_titles'])
        {
            $render_data['is_site_header'] = true;
            $render_data['site_name'] = $site_domain->setting('site_name');
            $render_data['site_slogan'] = $site_domain->setting('site_slogan');
            $render_data['site_banner_url'] = $site_domain->setting('site_banner');
        }
        else
        {
            $render_data['is_site_header'] = false;
        }

        $render_data['is_board_header'] = false;

        if ($site_domain->setting('show_site_favicon'))
        {
            $render_data['favicon_url'] = $site_domain->setting('site_favicon');
        }

        $render_data['page_title'] = $site_domain->setting('board_name');

        if ($render_data['show_manage_headers'])
        {
            $render_data['manage_header'] = $extra_data['header'] ?? '';
            $render_data['manage_sub_header'] = $extra_data['sub_header'] ?? '';

            if ($this->domain->id() !== '')
            {
                $render_data['manage_board_header'] = _gettext('Current Board:') . ' ' . $this->domain->id();
            }
        }

        $output = $this->output($render_data, 'header', false, $data_only);
        return $output;
    }

    private function board(array $parameters, bool $data_only)
    {
        $render_data = array();
        $session = new \Nelliel\Session();
        $site_domain = new \Nelliel\DomainSite($this->database);
        $dotdot = $parameters['dotdot'] ?? '';
        $extra_data = $parameters['extra_data'] ?? array();
        $treeline = $parameters['treeline'] ?? array();
        $index_render = $parameters['index_render'] ?? false;
        $render_data['show_styles'] = $parameters['show_styles'] ?? true;
        $render_data['session_active'] = $session->isActive();
        $output_head = new OutputHead($this->domain);
        $render_data['head'] = $output_head->render(['dotdot' => $dotdot]);
        $output_menu = new OutputMenu($this->domain);
        $render_data['show_manage_headers'] = $session->inModmode($this->domain);

        if ($render_data['show_styles'])
        {
            $render_data['styles'] = $output_menu->render(['menu' => 'styles', 'dotdot' => $dotdot], true);
        }

        $render_data['site_navigation'] = $output_menu->render(['menu' => 'site_navigation', 'dotdot' => $dotdot],
                true);

        $render_data['board_name'] = ($this->domain->setting('show_board_name')) ? $this->domain->setting('board_name') : '';
        $render_data['board_slogan'] = ($this->domain->setting('show_board_slogan')) ? $this->domain->setting(
                'board_slogan') : '';
        $render_data['board_banner_url'] = ($this->domain->setting('show_board_banner')) ? $this->domain->setting(
                'board_banner') : '';

        $render_data['is_site_header'] = false;
        $render_data['is_board_header'] = true;

        if ($this->domain->setting('show_board_favicon'))
        {
            $render_data['favicon_url'] = $this->domain->setting('board_favicon');
        }
        else
        {
            $render_data['favicon_url'] = $site_domain->setting('site_favicon');
        }

        if (!$index_render && !empty($treeline))
        {
            if (!isset($treeline[0]['subject']) || nel_true_empty($treeline[0]['subject']))
            {
                $render_data['page_title'] = $this->domain->setting('board_name') . ' > Thread #' .
                        $treeline[0]['post_number'];
            }
            else
            {
                $render_data['page_title'] = $this->domain->setting('board_name') . ' > ' . $treeline[0]['subject'];
            }
        }
        else
        {
            $render_data['page_title'] = $this->domain->setting('board_name');
        }

        if ($render_data['show_manage_headers'])
        {
            $render_data['manage_header'] = $extra_data['header'] ?? '';
            $render_data['manage_sub_header'] = $extra_data['sub_header'] ?? '';

            if ($this->domain->id() !== '')
            {
                $render_data['manage_board_header'] = _gettext('Current Board:') . ' ' . $this->domain->id();
            }
        }

        $render_data['boards_menu'] = $output_menu->render(['menu' => 'boards'], true);
        $output = $this->output($render_data, 'header', false, $data_only);
        return $output;
    }
}