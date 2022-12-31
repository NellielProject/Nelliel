<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Banners\Banners;
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
        $this->render_data['session_active'] = $this->session->isActive() && !$this->write_mode;
        $this->render_data['show_styles'] = true;
        $output_menu = new OutputMenu($this->domain, $this->write_mode);
        $this->render_data['styles'] = $output_menu->styles([], true);
        $output_navigation = new OutputNavigation($this->domain, $this->write_mode);
        $this->render_data['site_navigation'] = $output_navigation->siteLinks([], true);
        $this->render_data['use_site_header'] = true;
        $this->render_data['name'] = ($this->domain->setting('show_name')) ? $this->domain->setting('name') : '';
        $this->render_data['description'] = ($this->domain->setting('show_description')) ? $this->domain->setting(
            'description') : '';
        $this->displayBanners();
        $output = $this->output('header', $data_only, true, $this->render_data);
        return $output;
    }

    public function board(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $uri = $parameters['uri'] ?? $this->domain->reference('board_directory');
        $this->render_data['session_active'] = $this->session->isActive() && !$this->write_mode;
        $this->render_data['show_styles'] = true;
        $output_menu = new OutputMenu($this->domain, $this->write_mode);
        $this->render_data['styles'] = $output_menu->styles([], true);
        $output_navigation = new OutputNavigation($this->domain, $this->write_mode);
        $this->render_data['site_navigation'] = $output_navigation->siteLinks([], true);
        $this->render_data['board_navigation'] = $output_navigation->boardLinks([], true);
        $this->render_data['use_board_header'] = true;
        $this->render_data['board_uri'] = '/' . $uri . '/';
        $board_name = $this->domain->setting('name');

        if ($this->domain->setting('show_name') && !nel_true_empty($this->domain->setting('name'))) {
            $this->render_data['name'] = ' - ' . $board_name;
        }

        $this->render_data['description'] = ($this->domain->setting('show_description')) ? $this->domain->setting(
            'description') : '';
        $this->displayBanners();
        $output = $this->output('header', $data_only, true, $this->render_data);
        return $output;
    }

    public function overboard(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->render_data['session_active'] = $this->session->isActive() && !$this->write_mode;
        $this->render_data['show_styles'] = true;
        $output_menu = new OutputMenu($this->domain, $this->write_mode);
        $this->render_data['styles'] = $output_menu->styles([], true);
        $output_navigation = new OutputNavigation($this->domain, $this->write_mode);
        $this->render_data['site_navigation'] = $output_navigation->siteLinks([], true);
        $this->render_data['board_navigation'] = $output_navigation->boardLinks([], true);
        $this->render_data['use_overboard_header'] = true;
        $this->render_data['overboard_name'] = $parameters['name'] ?? null;
        $this->render_data['description'] = ($this->domain->setting('show_description')) ? $this->domain->setting(
            'description') : '';
        $this->displayBanners();
        $output = $this->output('headers/overboard', $data_only, true, $this->render_data);
        return $output;
    }

    public function manage(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->render_data['session_active'] = $this->session->isActive() && !$this->write_mode;
        $this->render_data['panel'] = $parameters['panel'] ?? '';
        $this->render_data['section'] = $parameters['section'] ?? '';
        $this->render_data['show_styles'] = true;
        $this->render_data['is_panel'] = $parameters['is_panel'] ?? false;
        $output_menu = new OutputMenu($this->domain, $this->write_mode);

        if ($this->domain->id() === Domain::SITE) {
            $this->render_data['area'] = $parameters['area'] ?? _gettext('Site Management');
        } else if ($this->domain->id() === Domain::GLOBAL) {
            $this->render_data['area'] = $parameters['area'] ?? _gettext('Global Board Management');
        } else {
            $this->render_data['board_id'] = $this->domain->id();
            $this->render_data['area'] = $parameters['area'] ?? _gettext('Board Management');
        }

        $this->render_data['styles'] = $output_menu->styles([], true);
        $output_navigation = new OutputNavigation($this->domain, $this->write_mode);
        $this->render_data['site_navigation'] = $output_navigation->siteLinks([], true);
        $output = $this->output('header', $data_only, true, $this->render_data);
        return $output;
    }

    private function displayBanners()
    {
        $banners = new Banners();
        $this->render_data['show_banner'] = $this->domain->setting('show_banners') &&
            !empty($banners->getList($this->domain->reference('banners_path'), true));
        $this->render_data['banner_display_width'] = $this->domain->setting('banner_display_width');
        $this->render_data['banner_display_height'] = $this->domain->setting('banner_display_height');
        $this->render_data['banner_url'] = nel_build_router_url([$this->domain->id(), 'banners', 'random']);
    }
}