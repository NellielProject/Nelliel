<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;

class OutputHeader extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function general(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $session = new \Nelliel\Account\Session();
        $this->render_data['session_active'] = $session->isActive() && !$this->write_mode;
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $this->render_data['show_styles'] = $parameters['show_styles'] ?? true;
        $output_menu = new OutputMenu($this->domain, $this->write_mode);

        if ($this->render_data['show_styles'])
        {
            $this->render_data['styles'] = $output_menu->styles([], true);
        }

        $output_navigation = new OutputNavigation($this->domain, $this->write_mode);
        $this->render_data['site_navigation'] = $output_navigation->siteLinks([], true);
        $this->render_data['use_site_header'] = true;
        $this->render_data['name'] = ($this->domain->setting('show_name')) ? $this->domain->setting('name') : '';
        $this->render_data['slogan'] = ($this->domain->setting('show_slogan')) ? $this->domain->setting('slogan') : '';
        $this->render_data['banner_url'] = ($this->domain->setting('show_banner')) ? $this->domain->setting('banner') : '';
        $this->render_data['page_title'] = $this->domain->setting('name');
        $output = $this->output('header', $data_only, true);
        return $output;
    }

    public function board(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $session = new \Nelliel\Account\Session();
        $treeline = $parameters['treeline'] ?? array();
        $index_render = $parameters['index_render'] ?? false;
        $this->render_data['session_active'] = $session->isActive() && !$this->write_mode;
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $this->render_data['show_styles'] = ($parameters['show_styles']) ?? true;
        $output_menu = new OutputMenu($this->domain, $this->write_mode);

        if ($this->render_data['show_styles'])
        {
            $this->render_data['styles'] = $output_menu->styles([], true);
        }

        $output_navigation = new OutputNavigation($this->domain, $this->write_mode);
        $this->render_data['site_navigation'] = $output_navigation->siteLinks([], true);
        $this->render_data['board_navigation'] = $output_navigation->boardLinks([], true);
        $this->render_data['use_board_header'] = true;
        $this->render_data['board_uri'] = '/' . $this->domain->reference('board_directory') . '/';
        $board_name = $this->domain->setting('name');

        if($this->domain->setting('show_name') && !nel_true_empty($this->domain->setting('name')))
        {
            $this->render_data['name'] = ' - ' . $board_name;
        }

        $this->render_data['slogan'] = ($this->domain->setting('show_slogan')) ? $this->domain->setting('slogan') : '';
        $this->render_data['banner_url'] = ($this->domain->setting('show_banner')) ? $this->domain->setting('banner') : '';

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

        $output = $this->output('header', $data_only, true);
        return $output;
    }

    public function manage(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $session = new \Nelliel\Account\Session();
        $site_domain = new \Nelliel\Domains\DomainSite($this->database);
        $this->render_data['session_active'] = $session->isActive() && !$this->write_mode;
        $this->render_data['panel'] = $parameters['panel'] ?? '';
        $this->render_data['section'] = $parameters['section'] ?? '';
        $this->render_data['show_styles'] = $parameters['show_styles'] ?? true;
        $this->render_data['is_panel'] = $parameters['is_panel'] ?? false;
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_menu = new OutputMenu($this->domain, $this->write_mode);

        if ($this->domain->id() === Domain::SITE)
        {
            $this->render_data['area'] = $parameters['area'] ?? _gettext('Site Management');
        }
        else
        {
            $this->render_data['board_id'] = $this->domain->id();
            $this->render_data['area'] = $parameters['area'] ?? _gettext('Board Management');
        }

        if ($this->render_data['show_styles'])
        {
            $this->render_data['styles'] = $output_menu->styles([], true);
        }

        $output_navigation = new OutputNavigation($this->domain, $this->write_mode);
        $this->render_data['site_navigation'] = $output_navigation->siteLinks([], true);
        $this->render_data['page_title'] = $site_domain->setting('name');
        $output = $this->output('header', $data_only, true);
        return $output;
    }
}