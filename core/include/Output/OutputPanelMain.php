<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;

class OutputPanelMain extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/main');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Main');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $this->render_data['module_manage_boards'] = $this->session->user()->checkPermission($this->domain,
            'perm_manage_boards');
        $this->render_data['manage_boards_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=manage-boards';
        $this->render_data['module_users'] = $this->session->user()->checkPermission($this->domain, 'perm_users_view');
        $this->render_data['users_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=users';
        $this->render_data['module_roles'] = $this->session->user()->checkPermission($this->domain, 'perm_roles_view');
        $this->render_data['roles_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=roles';
        $this->render_data['module_permissions'] = $this->session->user()->checkPermission($this->domain,
            'perm_permissions_manage');
        $this->render_data['permissions_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=permissions';
        $this->render_data['module_site_config'] = $this->session->user()->checkPermission($this->domain,
            'perm_site_config_modify');
        $this->render_data['site_config_url'] = nel_build_router_url([Domain::SITE, 'config']);
        $this->render_data['module_file_filters'] = $this->session->user()->checkPermission($this->domain,
            'perm_file_filters_manage');
        $this->render_data['file_filters_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=file-filters';
        $this->render_data['module_board_defaults'] = $this->session->user()->checkPermission($this->domain,
            'perm_board_defaults_modify');
        $this->render_data['board_defaults_url'] = nel_build_router_url([Domain::SITE, 'board-defaults']);
        $this->render_data['module_bans'] = $this->session->user()->checkPermission($this->domain, 'perm_bans_view');
        $this->render_data['bans_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=bans';
        $this->render_data['module_reports'] = $this->session->user()->checkPermission($this->domain,
            'perm_reports_view');
        $this->render_data['reports_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=reports';
        $this->render_data['module_templates'] = $this->session->user()->checkPermission($this->domain,
            'perm_templates_manage');
        $this->render_data['templates_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=templates';
        $this->render_data['module_filetypes'] = $this->session->user()->checkPermission($this->domain,
            'perm_filetypes_manage');
        $this->render_data['filetypes_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=filetypes';
        $this->render_data['module_styles'] = $this->session->user()->checkPermission($this->domain,
            'perm_styles_manage');
        $this->render_data['styles_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=styles';
        $this->render_data['module_image_sets'] = $this->session->user()->checkPermission($this->domain,
            'perm_image_sets_manage');
        $this->render_data['image_sets_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=image-sets';
        $this->render_data['module_news'] = $this->session->user()->checkPermission($this->domain, 'perm_news_manage');
        $this->render_data['news_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=news';
        $this->render_data['module_word_filters'] = $this->session->user()->checkPermission($this->domain,
            'perm_word_filters_manage');
        $this->render_data['word_filters_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=word-filters';
        $this->render_data['module_blotter'] = $this->session->user()->checkPermission($this->domain,
            'perm_blotter_manage');
        $this->render_data['blotter_url'] = nel_build_router_url([Domain::SITE, 'blotter']);
        $this->render_data['module_pages'] = $this->session->user()->checkPermission($this->domain, 'perm_pages_manage');
        $this->render_data['pages_url'] = nel_build_router_url([Domain::SITE, 'pages']);;
        $this->render_data['module_embeds'] = $this->session->user()->checkPermission($this->domain,
            'perm_embeds_manage');
        $this->render_data['embeds_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=embeds';
        $this->render_data['module_content_ops'] = $this->session->user()->checkPermission($this->domain,
            'perm_content_ops_manage');
        $this->render_data['content_ops_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=content-ops';
        $this->render_data['module_capcodes'] = $this->session->user()->checkPermission($this->domain,
            'perm_capcodes_manage');
        $this->render_data['capcodes_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=capcodes';
        $this->render_data['module_noticeboard'] = $this->session->user()->checkPermission($this->domain,
            'perm_noticeboard_view');
        $this->render_data['noticeboard_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=noticeboard';
        $this->render_data['module_plugins'] = $this->session->user()->checkPermission($this->domain,
            'perm_plugins_manage');
        $this->render_data['plugins_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=plugins';

        $this->render_data['regen_overboard_pages'] = $this->session->user()->checkPermission($this->domain,
            'perm_regen_overboard');
        $this->render_data['regen_overboard_url'] = nel_build_router_url([Domain::SITE, 'regen', 'overboard']);
        $this->render_data['regen_site_pages'] = $this->session->user()->checkPermission($this->domain,
            'perm_regen_pages');
        $this->render_data['regen_pages_url'] = nel_build_router_url([Domain::SITE, 'regen', 'pages']);
        $this->render_data['regen_site_caches'] = $this->session->user()->checkPermission($this->domain,
            'perm_regen_cache');
        $this->render_data['regen_caches_url'] = nel_build_router_url([Domain::SITE, 'regen', 'cache']);
        $this->render_data['module_extract_gettext'] = $this->session->user()->checkPermission($this->domain,
            'perm_extract_gettext');
        $this->render_data['extract_gettext_url'] = nel_build_router_url(
            [Domain::SITE, 'language', 'gettext', 'extract']);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}