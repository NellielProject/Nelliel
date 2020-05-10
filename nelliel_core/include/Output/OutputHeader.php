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

    function __construct(Domain $domain, bool $write_mode)
    {
        $this->domain = $domain;
        $this->writeMode($write_mode);
        $this->database = $domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
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
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $session = new \Nelliel\Account\Session($this->domain);
        $site_domain = new \Nelliel\DomainSite($this->database);
        $dotdot = $parameters['dotdot'] ?? '';
        $manage_headers = $parameters['manage_headers'] ?? array();
        $this->render_data['show_styles'] = $parameters['show_styles'] ?? true;
        $this->render_data['session_active'] = $session->isActive() && !$this->write_mode;
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_menu = new OutputMenu($this->domain, $this->write_mode);
        $this->render_data['show_manage_headers'] = $session->isActive() && !empty($manage_headers);

        if ($this->render_data['show_styles'])
        {
            $this->render_data['styles'] = $output_menu->render(['menu' => 'styles', 'dotdot' => $dotdot], true);
        }

        $output_navigation = new OutputNavigation($this->domain, $this->write_mode);
        $this->render_data['site_navigation'] = $output_navigation->render(
                ['navigation' => 'site_links', 'dotdot' => $dotdot], true);

        if (isset($parameters['use_site_titles']) && $parameters['use_site_titles'])
        {
            $this->render_data['is_site_header'] = true;
            $this->render_data['name'] = $site_domain->setting('name');
            $this->render_data['slogan'] = $site_domain->setting('slogan');
            $this->render_data['banner_url'] = $site_domain->setting('banner');
        }
        else
        {
            $this->render_data['is_site_header'] = false;
        }

        $this->render_data['is_board_header'] = false;
        $this->render_data['page_title'] = $site_domain->setting('name');

        if (!empty($manage_headers))
        {
            $this->render_data['manage_header'] = $manage_headers['header'] ?? '';
            $this->render_data['manage_sub_header'] = $manage_headers['sub_header'] ?? '';

            if ($this->domain->id() !== '_site_')
            {
                $this->render_data['manage_board_header'] = _gettext('Current Board:') . ' ' . $this->domain->id();
            }
        }

        $output = $this->output('header', $data_only, true);
        return $output;
    }

    private function board(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $session = new \Nelliel\Account\Session($this->domain);
        $site_domain = new \Nelliel\DomainSite($this->database);
        $dotdot = $parameters['dotdot'] ?? '';
        $manage_headers = $parameters['manage_headers'] ?? array();
        $treeline = $parameters['treeline'] ?? array();
        $index_render = $parameters['index_render'] ?? false;
        $this->render_data['show_styles'] = $parameters['show_styles'] ?? true;
        $this->render_data['session_active'] = $session->isActive() && !$this->write_mode;
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_menu = new OutputMenu($this->domain, $this->write_mode);
        $this->render_data['show_manage_headers'] = $session->isActive() && !empty($manage_headers);

        if ($this->render_data['show_styles'])
        {
            $this->render_data['styles'] = $output_menu->render(['menu' => 'styles', 'dotdot' => $dotdot], true);
        }

        $output_navigation = new OutputNavigation($this->domain, $this->write_mode);
        $this->render_data['site_navigation'] = $output_navigation->render(
                ['navigation' => 'site_links', 'dotdot' => $dotdot], true);

        $this->render_data['name'] = ($this->domain->setting('show_name')) ? $this->domain->setting('name') : '';
        $this->render_data['slogan'] = ($this->domain->setting('show_slogan')) ? $this->domain->setting('slogan') : '';
        $this->render_data['banner_url'] = ($this->domain->setting('show_banner')) ? $this->domain->setting('banner') : '';
        $this->render_data['is_site_header'] = false;
        $this->render_data['is_board_header'] = true;

        if (!$index_render && !empty($treeline))
        {
            if (!isset($treeline[0]['subject']) || nel_true_empty($treeline[0]['subject']))
            {
                $this->render_data['page_title'] = $this->domain->setting('name') . ' > Thread #' .
                        $treeline[0]['post_number'];
            }
            else
            {
                $this->render_data['page_title'] = $this->domain->setting('name') . ' > ' . $treeline[0]['subject'];
            }
        }
        else
        {
            $this->render_data['page_title'] = $this->domain->setting('name');
        }

        if ($this->render_data['show_manage_headers'])
        {
            $this->render_data['manage_header'] = $manage_headers['header'] ?? '';
            $this->render_data['manage_sub_header'] = $manage_headers['sub_header'] ?? '';

            if ($this->domain->id() !== '_site_')
            {
                $this->render_data['manage_board_header'] = _gettext('Current Board:') . ' ' . $this->domain->id();
            }
        }

        $this->render_data['board_navigation'] = $output_menu->render(['menu' => 'board_links', 'dotdot' => $dotdot],
                true);
        $output = $this->output('header', $data_only, true);
        return $output;
    }
}