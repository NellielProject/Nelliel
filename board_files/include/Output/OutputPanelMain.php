<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelMain extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters = array(), bool $data_only = false)
    {
        $session = new \Nelliel\Session(true);
        $user = $session->sessionUser();
        $dotdot = ($parameters['dotdot']) ?? '';
        $this->startTimer();
        $render_data = array();
        $output_head = new OutputHead($this->domain);
        $render_data['head'] = $output_head->render(['dotdot' => $dotdot]);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Main Panel')];
        $render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'extra_data' => $extra_data], true);
        $boards = $this->database->executeFetchAll('SELECT * FROM "' . BOARD_DATA_TABLE . '"', PDO::FETCH_ASSOC);

        if ($boards !== false)
        {
            foreach ($boards as $board)
            {
                $board_data['board_url'] = MAIN_SCRIPT . '?module=main-panel&board_id=' . $board['board_id'];
                $board_data['board_id'] = '/' . $board['board_id'] . '/';
                $render_data['board_list'][] = $board_data;
            }
        }

        $render_data['module_manage_boards'] = $user->domainPermission($this->domain, 'perm_manage_boards_access');
        $render_data['manage_boards_url'] = MAIN_SCRIPT . '?module=manage-boards';
        $render_data['module_users'] = $user->domainPermission($this->domain, 'perm_user_access');
        $render_data['users_url'] = MAIN_SCRIPT . '?module=users';
        $render_data['module_roles'] = $user->domainPermission($this->domain, 'perm_role_access');
        $render_data['roles_url'] = MAIN_SCRIPT . '?module=roles';
        $render_data['module_site_settings'] = $user->domainPermission($this->domain, 'perm_site_config_access');
        $render_data['site_settings_url'] = MAIN_SCRIPT . '?module=site-settings';
        $render_data['module_file_filters'] = $user->domainPermission($this->domain, 'perm_file_filters_access');
        $render_data['file_filters_url'] = MAIN_SCRIPT . '?module=file-filters';
        $render_data['module_board_defaults'] = $user->domainPermission($this->domain, 'perm_board_defaults_access');
        $render_data['board_defaults_url'] = MAIN_SCRIPT . '?module=board-defaults';
        $render_data['module_reports'] = $user->domainPermission($this->domain, 'perm_reports_access');
        $render_data['reports_url'] = MAIN_SCRIPT . '?module=reports';
        $render_data['module_templates'] = $user->domainPermission($this->domain, 'perm_templates_access');
        $render_data['templates_url'] = MAIN_SCRIPT . '?module=templates';
        $render_data['module_filetypes'] = $user->domainPermission($this->domain, 'perm_filetypes_access');
        $render_data['filetypes_url'] = MAIN_SCRIPT . '?module=filetypes';
        $render_data['module_styles'] = $user->domainPermission($this->domain, 'perm_styles_access');
        $render_data['styles_url'] = MAIN_SCRIPT . '?module=styles';
        $render_data['module_permissions'] = $user->domainPermission($this->domain, 'perm_permissions_access');
        $render_data['permissions_url'] = MAIN_SCRIPT . '?module=permissions';
        $render_data['module_icon_sets'] = $user->domainPermission($this->domain, 'perm_icon_sets_access');
        $render_data['icon_sets_url'] = MAIN_SCRIPT . '?module=icon-sets';
        $render_data['module_news'] = $user->domainPermission($this->domain, 'perm_news_access');
        $render_data['news_url'] = MAIN_SCRIPT . '?module=news';
        $render_data['module_extract_gettext'] = $user->domainPermission($this->domain, 'perm_extract_gettext');
        $render_data['extract_gettext_url'] = MAIN_SCRIPT . '?module=language&action=extract-gettext';
        $render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/main_panel', $render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output($render_data, 'page', true);
        echo $output;
        return $output;
    }
}