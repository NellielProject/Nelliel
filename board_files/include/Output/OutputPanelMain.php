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
    private $database;

    function __construct(Domain $domain)
    {
        $this->database = $domain->database();
        $this->domain = $domain;
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $session = new \Nelliel\Session(true);
        $user = $session->sessionUser();

        // Temp
        $this->render_instance = $this->domain->renderInstance();
        $this->render_instance->startTimer();

        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Options')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $template_loader = new \Mustache_Loader_FilesystemLoader($this->domain->templatePath(), ['extension' => '.html']);
        $render_instance = new \Mustache_Engine(['loader' => $template_loader]);
        $template_loader->load('management/panels/main_panel');
        $boards = $this->database->executeFetchAll('SELECT * FROM "' . BOARD_DATA_TABLE . '"', PDO::FETCH_ASSOC);

        if ($boards !== false)
        {
            foreach ($boards as $board)
            {
                $board_data['board_url'] = MAIN_SCRIPT . '?module=main-panel&board_id=' . $board['board_id'];
                $board_data['board_id'] = '/' . $board['board_id'] . '/';
                $render_input['board_list'][] = $board_data;
            }
        }

        $render_input['module_manage_boards'] = $user->domainPermission($this->domain, 'perm_manage_boards_access');
        $render_input['manage_boards_url'] = MAIN_SCRIPT . '?module=manage-boards';
        $render_input['module_users'] = $user->domainPermission($this->domain, 'perm_user_access');
        $render_input['users_url'] = MAIN_SCRIPT . '?module=users';
        $render_input['module_roles'] = $user->domainPermission($this->domain, 'perm_role_access');
        $render_input['roles_url'] = MAIN_SCRIPT . '?module=roles';
        $render_input['module_site_settings'] = $user->domainPermission($this->domain, 'perm_site_config_access');
        $render_input['site_settings_url'] = MAIN_SCRIPT . '?module=site-settings';
        $render_input['module_file_filters'] = $user->domainPermission($this->domain, 'perm_file_filters_access');
        $render_input['file_filters_url'] = MAIN_SCRIPT . '?module=file-filters';
        $render_input['module_board_defaults'] = $user->domainPermission($this->domain, 'perm_board_defaults_access');
        $render_input['board_defaults_url'] = MAIN_SCRIPT . '?module=board-defaults';
        $render_input['module_reports'] = $user->domainPermission($this->domain, 'perm_reports_access');
        $render_input['reports_url'] = MAIN_SCRIPT . '?module=reports';
        $render_input['module_templates'] = $user->domainPermission($this->domain, 'perm_templates_access');
        $render_input['templates_url'] = MAIN_SCRIPT . '?module=templates';
        $render_input['module_filetypes'] = $user->domainPermission($this->domain, 'perm_filetypes_access');
        $render_input['filetypes_url'] = MAIN_SCRIPT . '?module=filetypes';
        $render_input['module_styles'] = $user->domainPermission($this->domain, 'perm_styles_access');
        $render_input['styles_url'] = MAIN_SCRIPT . '?module=styles';
        $render_input['module_permissions'] = $user->domainPermission($this->domain, 'perm_permissions_access');
        $render_input['permissions_url'] = MAIN_SCRIPT . '?module=permissions';
        $render_input['module_icon_sets'] = $user->domainPermission($this->domain, 'perm_icon_sets_access');
        $render_input['icon_sets_url'] = MAIN_SCRIPT . '?module=icon-sets';
        $render_input['module_news'] = $user->domainPermission($this->domain, 'perm_news_access');
        $render_input['news_url'] = MAIN_SCRIPT . '?module=news';
        $render_input['module_extract_gettext'] = $user->domainPermission($this->domain, 'perm_extract_gettext');
        $render_input['extract_gettext_url'] = MAIN_SCRIPT . '?module=language&action=extract-gettext';

        $this->render_instance->appendToOutput($render_instance->render('management/panels/main_panel', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $output_footer->render(['dotdot' => '', 'styles' => false]);
        echo $this->render_instance->getOutput();
        nel_clean_exit();
    }
}