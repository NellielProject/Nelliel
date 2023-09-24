<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputNavigation extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function boardLinks(): array
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

    public function accountLinks(): array
    {
        $this->renderSetup();
        $left_bracket = $this->getUISetting($this->site_domain, 'site_nav_links_left_bracket');
        $right_bracket = $this->getUISetting($this->site_domain, 'site_nav_links_right_bracket');
        $this->render_data['account_nav_links_account']['text'] = $this->getUISetting($this->site_domain,
            'account_nav_links_account');
        $this->render_data['account_nav_links_account']['url'] = nel_build_router_url([Domain::SITE, 'account']);
        $this->render_data['account_nav_links_account']['left_bracket'] = $left_bracket;
        $this->render_data['account_nav_links_account']['right_bracket'] = $right_bracket;
        $this->render_data['account_nav_links_site_panel']['text'] = $this->getUISetting($this->site_domain,
            'account_nav_links_site_panel');
        $this->render_data['account_nav_links_site_panel']['url'] = nel_build_router_url([Domain::SITE, 'main-panel']);
        $this->render_data['account_nav_links_site_panel']['left_bracket'] = $left_bracket;
        $this->render_data['account_nav_links_site_panel']['right_bracket'] = $right_bracket;
        $this->render_data['account_nav_links_global_panel']['text'] = $this->getUISetting($this->site_domain,
            'account_nav_links_global_panel');
        $this->render_data['account_nav_links_global_panel']['url'] = nel_build_router_url(
            [Domain::GLOBAL, 'main-panel']);
        $this->render_data['account_nav_links_global_panel']['left_bracket'] = $left_bracket;
        $this->render_data['account_nav_links_global_panel']['right_bracket'] = $right_bracket;
        $this->render_data['account_nav_links_board_panel']['text'] = $this->getUISetting($this->site_domain,
            'account_nav_links_board_panel');
        $this->render_data['account_nav_links_board_panel']['url'] = nel_build_router_url(
            [$this->domain->id(), 'main-panel']);
        $this->render_data['account_nav_links_board_panel']['left_bracket'] = $left_bracket;
        $this->render_data['account_nav_links_board_panel']['right_bracket'] = $right_bracket;
        $this->render_data['account_nav_links_board_list']['text'] = $this->getUISetting($this->site_domain,
            'account_nav_links_board_list');
        $this->render_data['account_nav_links_board_list']['url'] = nel_build_router_url([Domain::SITE, 'boardlist']);
        $this->render_data['account_nav_links_board_list']['left_bracket'] = $left_bracket;
        $this->render_data['account_nav_links_board_list']['right_bracket'] = $right_bracket;
        $this->render_data['account_nav_links_logout']['text'] = $this->getUISetting($this->site_domain,
            'account_nav_links_logout');
        $this->render_data['account_nav_links_logout']['url'] = nel_build_router_url(
            [Domain::SITE, 'account', 'logout']);
        $this->render_data['account_nav_links_logout']['left_bracket'] = $left_bracket;
        $this->render_data['account_nav_links_logout']['right_bracket'] = $right_bracket;

        $link_data = array();
        $domain_is_board = $this->domain->id() !== Domain::SITE && $this->domain->id() !== Domain::GLOBAL;

        $link_data[] = $this->render_data['account_nav_links_account'];
        $link_data[] = $this->render_data['account_nav_links_site_panel'];
        $link_data[] = $this->render_data['account_nav_links_global_panel'];

        if ($domain_is_board) {
            $link_data[] = $this->render_data['account_nav_links_board_panel'];
        }

        $link_data[] = $this->render_data['account_nav_links_board_list'];
        $link_data[] = $this->render_data['account_nav_links_logout'];
        return $link_data;
    }

    public function siteLinks(): array
    {
        $this->renderSetup();
        $this->render_data['session_active'] = $this->session->isActive() && !$this->write_mode;
        $left_bracket = $this->getUISetting($this->site_domain, 'site_nav_links_left_bracket');
        $right_bracket = $this->getUISetting($this->site_domain, 'site_nav_links_right_bracket');

        $this->render_data['site_nav_links_home']['text'] = $this->getUISetting($this->site_domain,
            'site_nav_links_home');
        $this->render_data['site_nav_links_home']['url'] = $this->site_domain->reference('home_page');
        $this->render_data['site_nav_links_home']['left_bracket'] = $left_bracket;
        $this->render_data['site_nav_links_home']['right_bracket'] = $right_bracket;
        $this->render_data['site_nav_links_news']['text'] = $this->getUISetting($this->site_domain,
            'site_nav_links_news');
        $this->render_data['site_nav_links_news']['url'] = NEL_BASE_WEB_PATH . 'news.html';
        $this->render_data['site_nav_links_news']['left_bracket'] = $left_bracket;
        $this->render_data['site_nav_links_news']['right_bracket'] = $right_bracket;
        $this->render_data['site_nav_links_faq']['text'] = $this->getUISetting($this->site_domain, 'site_nav_links_faq');
        $this->render_data['site_nav_links_faq']['url'] = NEL_BASE_WEB_PATH . 'faq.html';
        $this->render_data['site_nav_links_faq']['left_bracket'] = $left_bracket;
        $this->render_data['site_nav_links_faq']['right_bracket'] = $right_bracket;
        $this->render_data['site_nav_links_about_nelliel']['text'] = $this->getUISetting($this->site_domain,
            'site_nav_links_about_nelliel');
        $this->render_data['site_nav_links_about_nelliel']['url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'about_nelliel';
        $this->render_data['site_nav_links_about_nelliel']['left_bracket'] = $left_bracket;
        $this->render_data['site_nav_links_about_nelliel']['right_bracket'] = $right_bracket;
        $this->render_data['site_nav_links_blank_page']['text'] = $this->getUISetting($this->site_domain,
            'site_nav_links_blank_page');
        $this->render_data['site_nav_links_blank_page']['url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'blank';
        $this->render_data['site_nav_links_blank_page']['left_bracket'] = $left_bracket;
        $this->render_data['site_nav_links_blank_page']['right_bracket'] = $right_bracket;

        $link_data = array();
        $link_data[] = $this->render_data['site_nav_links_home'];
        $link_data[] = $this->render_data['site_nav_links_news'];
        $link_data[] = $this->render_data['site_nav_links_faq'];

        if ($this->site_domain->setting('overboard_active')) {
            $this->render_data['site_nav_links_overboard']['text'] = $this->site_domain->setting('overboard_name');
            $this->render_data['site_nav_links_overboard']['url'] = NEL_BASE_WEB_PATH .
                $this->site_domain->setting('overboard_uri') . '/';
            $this->render_data['site_nav_links_overboard']['left_bracket'] = $left_bracket;
            $this->render_data['site_nav_links_overboard']['right_bracket'] = $right_bracket;
            $link_data[] = $this->render_data['site_nav_links_overboard'];
        }

        if ($this->site_domain->setting('sfw_overboard_active')) {
            $this->render_data['site_nav_links_sfw_overboard']['text'] = $this->site_domain->setting(
                'sfw_overboard_name');
            $this->render_data['site_nav_links_sfw_overboard']['url'] = NEL_BASE_WEB_PATH .
                $this->site_domain->setting('sfw_overboard_uri') . '/';
            $this->render_data['site_nav_links_sfw_overboard']['left_bracket'] = $left_bracket;
            $this->render_data['site_nav_links_sfw_overboard']['right_bracket'] = $right_bracket;
            $link_data[] = $this->render_data['site_nav_links_sfw_overboard'];
        }

        $link_data[] = $this->render_data['site_nav_links_about_nelliel'];
        $link_data[] = $this->render_data['site_nav_links_blank_page'];
        return $link_data;
    }

    public function boardPages(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $in_modmode = $parameters['in_modmode'] ?? false;
        $render_data = array();

        if (!$this->write_mode) {
            $render_data['catalog_url'] = nel_build_router_url([$this->domain->id(), 'catalog'], true);
            $render_data['index_url'] = nel_build_router_url([$this->domain->id(), 'index'], true);
        }

        if ($in_modmode) {
            $render_data['form_action'] = nel_build_router_url([$this->domain->id(), 'threads'], false, 'modmode');
            $render_data['catalog_url'] = nel_build_router_url([$this->domain->id(), 'catalog'], true, 'modmode');
            $render_data['index_url'] = nel_build_router_url([$this->domain->id()], true, 'modmode');
        } else {
            $render_data['form_action'] = nel_build_router_url([$this->domain->id(), 'threads']);
            $render_data['catalog_url'] = 'catalog.html';
            $render_data['index_url'] = 'index.html';
        }

        $render_data['show_catalog_link'] = $this->domain->setting('enable_catalog') &&
            $this->domain->setting('show_catalog_link');
        $render_data['show_index_link'] = $this->domain->setting('enable_index') &&
            $this->domain->setting('show_index_link');

        return $render_data;
    }
}