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
        $session = new \Nelliel\Session();
        $site_domain = new \Nelliel\DomainSite($this->database);
        $dotdot = $parameters['dotdot'] ?? '';
        $manage_headers = $parameters['manage_headers'] ?? array();
        $this->render_data['show_styles'] = $parameters['show_styles'] ?? true;
        $ignore_session = $parameters['ignore_session'] ?? false;
        $this->render_data['session_active'] = $session->isActive() && !$ignore_session;
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_menu = new OutputMenu($this->domain);
        $this->render_data['show_manage_headers'] = $session->isActive() && !empty($manage_headers);
        
        if ($this->render_data['show_styles'])
        {
            $this->render_data['styles'] = $output_menu->render(
                    ['menu' => 'styles', 'dotdot' => $dotdot, 'ignore_session' => $ignore_session], true);
        }
        
        $this->render_data['site_navigation'] = $output_menu->render(
                ['menu' => 'site_navigation', 'dotdot' => $dotdot, 'ignore_session' => $ignore_session], true);
        
        if (isset($parameters['use_site_titles']) && $parameters['use_site_titles'])
        {
            $this->render_data['is_site_header'] = true;
            $this->render_data['site_name'] = $site_domain->setting('site_name');
            $this->render_data['site_slogan'] = $site_domain->setting('site_slogan');
            $this->render_data['site_banner_url'] = $site_domain->setting('site_banner');
        }
        else
        {
            $this->render_data['is_site_header'] = false;
        }
        
        $this->render_data['is_board_header'] = false;
        
        if ($site_domain->setting('show_site_favicon'))
        {
            $this->render_data['favicon_url'] = $site_domain->setting('site_favicon');
        }
        
        $this->render_data['page_title'] = $site_domain->setting('board_name');
        
        if (!empty($manage_headers))
        {
            $this->render_data['manage_header'] = $manage_headers['header'] ?? '';
            $this->render_data['manage_sub_header'] = $manage_headers['sub_header'] ?? '';
            
            if ($this->domain->id() !== '')
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
        $session = new \Nelliel\Session();
        $site_domain = new \Nelliel\DomainSite($this->database);
        $dotdot = $parameters['dotdot'] ?? '';
        $manage_headers = $parameters['manage_headers'] ?? array();
        $treeline = $parameters['treeline'] ?? array();
        $index_render = $parameters['index_render'] ?? false;
        $ignore_session = $parameters['ignore_session'] ?? false;
        $this->render_data['show_styles'] = $parameters['show_styles'] ?? true;
        $this->render_data['session_active'] = $session->isActive() && !$ignore_session;
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_menu = new OutputMenu($this->domain);
        $this->render_data['show_manage_headers'] = $session->isActive() && !empty($manage_headers);
        
        if ($this->render_data['show_styles'])
        {
            $this->render_data['styles'] = $output_menu->render(
                    ['menu' => 'styles', 'dotdot' => $dotdot, 'ignore_session' => $ignore_session], true);
        }
        
        $this->render_data['site_navigation'] = $output_menu->render(
                ['menu' => 'site_navigation', 'dotdot' => $dotdot, 'ignore_session' => $ignore_session], true);
        
        $this->render_data['board_name'] = ($this->domain->setting('show_board_name')) ? $this->domain->setting(
                'board_name') : '';
        $this->render_data['board_slogan'] = ($this->domain->setting('show_board_slogan')) ? $this->domain->setting(
                'board_slogan') : '';
        $this->render_data['board_banner_url'] = ($this->domain->setting('show_board_banner')) ? $this->domain->setting(
                'board_banner') : '';
        
        $this->render_data['is_site_header'] = false;
        $this->render_data['is_board_header'] = true;
        
        if ($this->domain->setting('show_board_favicon'))
        {
            $this->render_data['favicon_url'] = $this->domain->setting('board_favicon');
        }
        else
        {
            $this->render_data['favicon_url'] = $site_domain->setting('site_favicon');
        }
        
        if (!$index_render && !empty($treeline))
        {
            if (!isset($treeline[0]['subject']) || nel_true_empty($treeline[0]['subject']))
            {
                $this->render_data['page_title'] = $this->domain->setting('board_name') . ' > Thread #' .
                        $treeline[0]['post_number'];
            }
            else
            {
                $this->render_data['page_title'] = $this->domain->setting('board_name') . ' > ' . $treeline[0]['subject'];
            }
        }
        else
        {
            $this->render_data['page_title'] = $this->domain->setting('board_name');
        }
        
        if ($this->render_data['show_manage_headers'])
        {
            $this->render_data['manage_header'] = $manage_headers['header'] ?? '';
            $this->render_data['manage_sub_header'] = $manage_headers['sub_header'] ?? '';
            
            if ($this->domain->id() !== '')
            {
                $this->render_data['manage_board_header'] = _gettext('Current Board:') . ' ' . $this->domain->id();
            }
        }
        
        $this->render_data['boards_menu'] = $output_menu->render(['menu' => 'boards'], true);
        $output = $this->output('header', $data_only, true);
        return $output;
    }
}