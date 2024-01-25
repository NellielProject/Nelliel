<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputNavigationLinks extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function boards(): array
    {
        $this->renderSetup();
        $board_ids = $this->database->executeFetchAll('SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"',
            PDO::FETCH_COLUMN);
        $board_count = count($board_ids);
        $end = $board_count - 1;
        $link_data = array();

        for ($i = 0; $i < $board_count; $i ++) {
            $board = Domain::getDomainFromID($board_ids[$i], $this->database);
            $board_info = array();
            $board_info['board_url'] = $board->reference('board_web_path');
            $board_info['name'] = $board->setting('name');
            $board_info['board_uri'] = $board->uri();
            $board_info['end'] = $i === $end;
            $link_data['boards'][] = $board_info;
        }

        return $link_data;
    }

    public function account(): array
    {
        $domain = $this->site_domain;
        $do_translation = $domain->setting('translate_account_nav_links');

        $translate = function (string $setting) use ($domain, $do_translation) {
            $value = $domain->setting($setting) ?? '';

            if ($do_translation && $value !== '') {
                $value = __($value);
            }

            return $value;
        };

        $options_keys = ['account_nav_links_account', 'account_nav_links_site_panel', 'account_nav_links_global_panel',
            'account_nav_links_board_panel', 'account_nav_links_board_list', 'account_nav_links_logout'];
        $link_set = new LinkSet();
        $base_data = array();
        $base_data['left_bracket'] = $translate('account_nav_links_left_bracket');
        $base_data['right_bracket'] = $translate('account_nav_links_right_bracket');

        $link_set->addLink('account_nav_links_account', $base_data);
        $link_set->addData('account_nav_links_account', 'text', $translate('account_nav_links_account'));
        $link_set->addData('account_nav_links_account', 'url', nel_build_router_url([Domain::SITE, 'account']));

        $link_set->addLink('account_nav_links_site_panel', $base_data);
        $link_set->addData('account_nav_links_site_panel', 'text', $translate('account_nav_links_site_panel'));
        $link_set->addData('account_nav_links_site_panel', 'url', nel_build_router_url([Domain::SITE, 'main-panel']));

        $link_set->addLink('account_nav_links_global_panel', $base_data);
        $link_set->addData('account_nav_links_global_panel', 'text', $translate('account_nav_links_global_panel'));
        $link_set->addData('account_nav_links_global_panel', 'url', nel_build_router_url([Domain::GLOBAL, 'main-panel']));

        if ($this->domain->id() !== Domain::SITE && $this->domain->id() !== Domain::GLOBAL) {
            $link_set->addLink('account_nav_links_board_panel', $base_data);
            $link_set->addData('account_nav_links_board_panel', 'text', $translate('account_nav_links_board_panel'));
            $link_set->addData('account_nav_links_board_panel', 'url',
                nel_build_router_url([$this->domain->uri(), 'main-panel']));
        }

        $link_set->addLink('account_nav_links_board_list', $base_data);
        $link_set->addData('account_nav_links_board_list', 'text', $translate('account_nav_links_board_list'));
        $link_set->addData('account_nav_links_board_list', 'url', nel_build_router_url([Domain::SITE, 'boardlist']));

        $link_set->addLink('account_nav_links_logout', $base_data);
        $link_set->addData('account_nav_links_logout', 'text', $translate('account_nav_links_logout'));
        $link_set->addData('account_nav_links_logout', 'url', nel_build_router_url([Domain::SITE, 'account', 'logout']));

        return $link_set->build($options_keys);
    }

    public function site(): array
    {
        $domain = $this->site_domain;
        $do_translation = $domain->setting('translate_site_nav_links');

        $translate = function (string $setting) use ($domain, $do_translation) {
            $value = $domain->setting($setting) ?? '';

            if ($do_translation && $value !== '') {
                $value = __($value);
            }

            return $value;
        };

        $options_keys = ['site_nav_links_home', 'site_nav_links_news', 'site_nav_links_faq', 'site_nav_links_overboard',
            'site_nav_links_sfw_overboard', 'site_nav_links_about_nelliel', 'site_nav_links_blank_page',
            'site_nav_links_account'];
        $link_set = new LinkSet();
        $base_data = array();
        $base_data['left_bracket'] = $translate('site_nav_links_left_bracket');
        $base_data['right_bracket'] = $translate('site_nav_links_right_bracket');

        $link_set->addLink('site_nav_links_home', $base_data);
        $link_set->addData('site_nav_links_home', 'text', $translate('site_nav_links_home'));
        $link_set->addData('site_nav_links_home', 'url', $this->site_domain->reference('home_page'));

        $link_set->addLink('site_nav_links_news', $base_data);
        $link_set->addData('site_nav_links_news', 'text', $translate('site_nav_links_news'));
        $link_set->addData('site_nav_links_news', 'url', NEL_BASE_WEB_PATH . 'news.html');

        $link_set->addLink('site_nav_links_faq', $base_data);
        $link_set->addData('site_nav_links_faq', 'text', $translate('site_nav_links_faq'));
        $link_set->addData('site_nav_links_faq', 'url', NEL_BASE_WEB_PATH . 'faq.html');

        $link_set->addLink('site_nav_links_about_nelliel', $base_data);
        $link_set->addData('site_nav_links_about_nelliel', 'text', $translate('site_nav_links_about_nelliel'));
        $link_set->addData('site_nav_links_about_nelliel', 'url', NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'about_nelliel');

        $link_set->addLink('site_nav_links_blank_page', $base_data);
        $link_set->addData('site_nav_links_blank_page', 'text', $translate('site_nav_links_blank_page'));
        $link_set->addData('site_nav_links_blank_page', 'url', NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'blank');

        if (!$this->session->isActive() || $this->session->ignore()) {
            $link_set->addLink('site_nav_links_account', $base_data);
            $link_set->addData('site_nav_links_account', 'text', $translate('account_nav_links_account'));
            $link_set->addData('site_nav_links_account', 'url', nel_build_router_url([Domain::SITE, 'account']));
        }

        if ($this->site_domain->setting('overboard_active')) {
            $link_set->addLink('site_nav_links_overboard', $base_data);
            $link_set->addData('site_nav_links_overboard', 'text', $this->site_domain->setting('overboard_name'));
            $link_set->addData('site_nav_links_overboard', 'url',
                NEL_BASE_WEB_PATH . $this->site_domain->setting('overboard_uri') . '/');
        }

        if ($this->site_domain->setting('sfw_overboard_active')) {
            $link_set->addLink('site_nav_links_sfw_overboard', $base_data);
            $link_set->addData('site_nav_links_sfw_overboard', 'text', $this->site_domain->setting('overboard_name'));
            $link_set->addData('site_nav_links_sfw_overboard', 'url',
                NEL_BASE_WEB_PATH . $this->site_domain->setting('sfw_overboard_uri') . '/');
        }

        return $link_set->build($options_keys);
    }

    public function boardPages(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $in_modmode = $parameters['in_modmode'] ?? false;
        $display_type = $parameters['display'] ?? 'index';
        $render_data = array();

        if (!$this->write_mode) {
            $render_data['catalog_url'] = nel_build_router_url([$this->domain->uri(), 'catalog'], true);
            $render_data['index_url'] = nel_build_router_url([$this->domain->uri(), 'index'], true);
        }

        if ($in_modmode) {
            $render_data['form_action'] = nel_build_router_url([$this->domain->uri(), 'threads'], false, 'modmode');
            $render_data['catalog_url'] = nel_build_router_url([$this->domain->uri(), 'catalog'], true, 'modmode');
            $render_data['index_url'] = nel_build_router_url([$this->domain->uri()], true, 'modmode');
        } else {
            $render_data['form_action'] = nel_build_router_url([$this->domain->uri(), 'threads']);
            $render_data['catalog_url'] = 'catalog.html';
            $render_data['index_url'] = 'index.html';
        }

        $render_data['show_catalog_link'] = $display_type === 'index' && $this->domain->setting('enable_catalog') &&
            $this->domain->setting('show_catalog_link');
        $render_data['show_index_link'] = $display_type === 'catalog' && $this->domain->setting('enable_index') &&
            $this->domain->setting('show_index_link');

        return $render_data;
    }
}