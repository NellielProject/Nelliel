<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Banners\Banners;
use Nelliel\Domains\Domain;

class OutputFooter extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function general(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->render_data['use_general_footer'] = true;
        $this->allFooters();
        $this->displayBanners();
        $output = $this->output('footers/general', $data_only, true, $this->render_data);
        return $output;
    }

    public function board(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->render_data['use_board_footer'] = true;
        $this->allFooters();
        $this->displayBanners();
        $output = $this->output('footers/board', $data_only, true, $this->render_data);
        return $output;
    }

    public function manage(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->render_data['use_manage_footer'] = true;
        $this->allFooters();
        $output = $this->output('footers/manage', $data_only, true, $this->render_data);
        return $output;
    }

    private function allFooters(): void
    {
        if (!nel_true_empty($this->domain->setting('board_footer_text'))) {
            foreach ($this->output_filter->newlinesToArray($this->domain->setting('board_footer_text')) as $line) {
                $this->render_data['board_footer_lines'][]['text'] = htmlspecialchars($line);
            }
        }

        if (!nel_true_empty($this->site_domain->setting('site_footer_text'))) {
            foreach ($this->output_filter->newlinesToArray($this->site_domain->setting('site_footer_text')) as $line) {
                $this->render_data['site_footer_lines'][]['text'] = htmlspecialchars($line);
            }
        }

        $output_menu = new OutputMenu($this->domain, $this->write_mode);
        $this->render_data['styles'] = $output_menu->styles([], true);
        $this->render_data['show_top_styles'] = $this->domain->setting('show_top_styles');
        $this->render_data['show_bottom_styles'] = $this->domain->setting('show_bottom_styles');
    }

    private function displayBanners(): void
    {
        $banners = new Banners();
        $is_board_domain = $this->domain->id() !== Domain::SITE && $this->domain->id() !== Domain::GLOBAL;
        $site_banners_available = !empty($banners->getList($this->site_domain->reference('banners_path'), true));
        $board_banners_available = !empty($banners->getList($this->domain->reference('banners_path'), true));

        if ($site_banners_available &&
            (!$is_board_domain || $this->site_domain->setting('show_bottom_banners_on_boards'))) {
            $this->render_data['site_banner_url'] = nel_build_router_url(
                [$this->site_domain->uri(), 'banners', 'random']);
            $this->render_data['show_site_bottom_banner'] = $this->site_domain->setting('show_bottom_banners');
            $this->render_data['site_banner_url'] = nel_build_router_url(
                [$this->site_domain->uri(), 'banners', 'random']);
            $this->render_data['site_banner_display_width'] = $this->site_domain->setting('banner_display_width');
            $this->render_data['site_banner_display_height'] = $this->site_domain->setting('banner_display_height');
        }

        if ($is_board_domain && $board_banners_available) {
            $this->render_data['board_banner_url'] = nel_build_router_url([$this->domain->uri(), 'banners', 'random']);
            $this->render_data['show_board_bottom_banner'] = $this->domain->setting('show_bottom_banners');
            $this->render_data['board_banner_url'] = nel_build_router_url([$this->domain->uri(), 'banners', 'random']);
            $this->render_data['board_banner_display_width'] = $this->site_domain->setting('banner_display_width');
            $this->render_data['board_banner_display_height'] = $this->site_domain->setting('banner_display_height');
        }
    }
}