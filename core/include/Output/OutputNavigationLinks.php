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
        $board_ids = $this->database->executeFetchAll('SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"',
            PDO::FETCH_COLUMN);
        $board_count = count($board_ids);
        $link_set = new LinkSet();
        $set_keys = array();
        $last_key = '';

        for ($i = 0; $i < $board_count; $i ++) {
            $board = Domain::getDomainFromID($board_ids[$i]);
            $link_set->addData($board->uri(), 'board_url', $board->reference('board_web_path'));
            $link_set->addData($board->uri(), 'name', $board->setting('name'));
            $link_set->addData($board->uri(), 'board_uri', $board->uri());
            $last_key = $board->uri();
            $set_keys[] = $board->uri();
        }

        if ($this->site_domain->setting('overboard_active')) {
            $key = $this->site_domain->setting('overboard_uri');
            $link_set->addData($key, 'board_uri', $this->site_domain->setting('overboard_uri'));
            $link_set->addData($key, 'board_name', $this->site_domain->setting('overboard_name'));
            $link_set->addData($key, 'board_url', NEL_BASE_WEB_PATH . $this->site_domain->setting('overboard_uri') . '/');
            $last_key = $key;
            $set_keys[] = $this->site_domain->setting('overboard_uri');
        }

        if ($this->site_domain->setting('sfw_overboard_active')) {
            $key = $this->site_domain->setting('sfw_overboard_uri');
            $link_set->addData($key, 'board_uri', $this->site_domain->setting('sfw_overboard_uri'));
            $link_set->addData($key, 'board_name', $this->site_domain->setting('sfw_overboard_name'));
            $link_set->addData($key, 'board_url',
                NEL_BASE_WEB_PATH . $this->site_domain->setting('sfw_overboard_uri') . '/');
            $last_key = $this->site_domain->setting('sfw_overboard_uri');
            $set_keys[] = $this->site_domain->setting('sfw_overboard_uri');
        }

        $link_set->addData($last_key, 'end', true);
        return $link_set->build($set_keys);
    }

    public function logged_in(): array
    {
        $domain = $this->site_domain;
        $do_translation = $domain->setting('translate_site_links');

        $translate = function (string $setting) use ($domain, $do_translation) {
            $value = $domain->setting($setting) ?? '';

            if ($do_translation && $value !== '') {
                $value = __($value);
            }

            return $value;
        };

        $options_keys = (array) json_decode($domain->setting('logged_in_link_set'));
        $link_set = new LinkSet();
        $base_data = array();
        $base_data['left_bracket'] = $translate('site_links_left_bracket');
        $base_data['right_bracket'] = $translate('site_links_right_bracket');

        $link_set->addLink('site_links_account', $base_data);
        $link_set->addData('site_links_account', 'text', $translate('site_links_account'));
        $link_set->addData('site_links_account', 'url', nel_build_router_url([Domain::SITE, 'account']));

        $link_set->addLink('site_links_site_panel', $base_data);
        $link_set->addData('site_links_site_panel', 'text', $translate('site_links_site_panel'));
        $link_set->addData('site_links_site_panel', 'url', nel_build_router_url([Domain::SITE, 'main-panel']));

        $link_set->addLink('site_links_global_panel', $base_data);
        $link_set->addData('site_links_global_panel', 'text', $translate('site_links_global_panel'));
        $link_set->addData('site_links_global_panel', 'url', nel_build_router_url([Domain::GLOBAL, 'main-panel']));

        if ($this->domain->id() !== Domain::SITE && $this->domain->id() !== Domain::GLOBAL) {
            $link_set->addLink('site_links_board_panel', $base_data);
            $link_set->addData('site_links_board_panel', 'text', $translate('site_links_board_panel'));
            $link_set->addData('site_links_board_panel', 'url',
                nel_build_router_url([$this->domain->uri(), 'main-panel']));
        }

        $link_set->addLink('site_links_board_list', $base_data);
        $link_set->addData('site_links_board_list', 'text', $translate('site_links_board_list'));
        $link_set->addData('site_links_board_list', 'url', nel_build_router_url([Domain::SITE, 'boardlist']));

        $link_set->addLink('site_links_logout', $base_data);
        $link_set->addData('site_links_logout', 'text', $translate('site_links_logout'));
        $link_set->addData('site_links_logout', 'url', nel_build_router_url([Domain::SITE, 'account', 'logout']));

        return $link_set->build($options_keys);
    }

    public function site(): array
    {
        $domain = $this->site_domain;
        $do_translation = $domain->setting('translate_site_links');

        $translate = function (string $setting) use ($domain, $do_translation) {
            $value = $domain->setting($setting) ?? '';

            if ($do_translation && $value !== '') {
                $value = __($value);
            }

            return $value;
        };

        $options_keys = (array) json_decode($domain->setting('site_navigation_link_set'));
        $link_set = new LinkSet();
        $base_data = array();
        $base_data['left_bracket'] = $translate('site_links_left_bracket');
        $base_data['right_bracket'] = $translate('site_links_right_bracket');

        $link_set->addLink('site_links_home', $base_data);
        $link_set->addData('site_links_home', 'text', $translate('site_links_home'));
        $link_set->addData('site_links_home', 'url', $this->site_domain->reference('home_page'));

        $link_set->addLink('site_links_news', $base_data);
        $link_set->addData('site_links_news', 'text', $translate('site_links_news'));
        $link_set->addData('site_links_news', 'url', NEL_BASE_WEB_PATH . 'news.html');

        $link_set->addLink('site_links_faq', $base_data);
        $link_set->addData('site_links_faq', 'text', $translate('site_links_faq'));
        $link_set->addData('site_links_faq', 'url', NEL_BASE_WEB_PATH . 'faq.html');

        $link_set->addLink('site_links_about_nelliel', $base_data);
        $link_set->addData('site_links_about_nelliel', 'text', $translate('site_links_about_nelliel'));
        $link_set->addData('site_links_about_nelliel', 'url', NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'about_nelliel');

        $link_set->addLink('site_links_blank_page', $base_data);
        $link_set->addData('site_links_blank_page', 'text', $translate('site_links_blank_page'));
        $link_set->addData('site_links_blank_page', 'url', NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'blank');

        $link_set->addLink('site_links_account', $base_data);
        $link_set->addData('site_links_account', 'text', $translate('site_links_account'));
        $link_set->addData('site_links_account', 'url', nel_build_router_url([Domain::SITE, 'account']));

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