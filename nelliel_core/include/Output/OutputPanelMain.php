<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;
use Nelliel\Auth\Authorization;

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
        $authorization = new Authorization($this->database);
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Main');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_USER_ROLES_TABLE . '" WHERE "user_id" = ?');
        $user_roles = $this->database->executePreparedFetchAll($prepared, [$this->session->user()->id()],
                PDO::FETCH_ASSOC);
        $boards = $this->database->executeFetchAll('SELECT * FROM "' . NEL_BOARD_DATA_TABLE . '"', PDO::FETCH_ASSOC);

        $roles_list = array();
        $roles = $this->database->executeFetchAll('SELECT "role_id", "role_title" FROM "' . NEL_ROLES_TABLE . '"',
                PDO::FETCH_ASSOC);

        foreach ($roles as $role)
        {
            $roles_list[$role['role_id']]['role_title'] = $role['role_title'];
        }

        $user_roles_list = array();
        $has_global = false;
        $global_role_id = '';
        $global_role_title = '';

        foreach ($user_roles as $user_role)
        {
            $user_roles_list[$user_role['domain_id']]['role_id'] = $user_role['role_id'];

            if (isset($roles_list[$user_role['role_id']]))
            {
                $user_roles_list[$user_role['domain_id']]['role_title'] = $roles_list[$user_role['role_id']]['role_title'];
            }

            if ($user_role['domain_id'] === Domain::GLOBAL)
            {
                $has_global = true;
                $global_role_id = $user_role['role_id'];
                $global_role_title = $roles_list[$user_role['role_id']]['role_title'];
            }
        }

        if (is_array($boards))
        {
            foreach ($boards as $board)
            {
                if ($board['board_id'] === Domain::SITE)
                {
                    continue;
                }

                if (!isset($user_roles_list[$board['board_id']]) && !$this->session->user()->isSiteOwner() &&
                        !$has_global)
                {
                    continue;
                }

                $board_data['board_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                        'module=admin&section=board-main-panel&board-id=' . $board['board_id'];
                $board_data['board_id'] = '/' . $board['board_id'] . '/';
                $global_level_lower = $authorization->roleLevelCheck($global_role_id,
                        $user_roles_list[$board['board_id']]['role_title'] ?? '');

                if ($this->session->user()->isSiteOwner())
                {
                    $board_data['board_role'] = _gettext('Site Owner');
                }
                else if (!isset($user_roles_list[$board['board_id']]) || !$global_level_lower)
                {
                    $board_data['board_role'] = ($has_global) ? $global_role_title : '';
                }
                else
                {
                    $board_data['board_role'] = $user_roles_list[$board['board_id']]['role_title'];
                }

                $this->render_data['board_list'][] = $board_data;
            }
        }

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
        $this->render_data['module_site_settings'] = $this->session->user()->checkPermission($this->domain,
                'perm_site_config_modify');
        $this->render_data['site_settings_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=site-settings';
        $this->render_data['module_file_filters'] = $this->session->user()->checkPermission($this->domain,
                'perm_file_filters_manage');
        $this->render_data['file_filters_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=file-filters';
        $this->render_data['module_board_defaults'] = $this->session->user()->checkPermission($this->domain,
                'perm_board_defaults_modify');
        $this->render_data['board_defaults_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=board-defaults';
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
        $this->render_data['module_icon_sets'] = $this->session->user()->checkPermission($this->domain,
                'perm_icon_sets_manage');
        $this->render_data['icon_sets_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=icon-sets';
        $this->render_data['module_news'] = $this->session->user()->checkPermission($this->domain, 'perm_news_manage');
        $this->render_data['news_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=news';
        $this->render_data['module_dnsbl'] = $this->session->user()->checkPermission($this->domain, 'perm_dnsbl_manage');
        $this->render_data['dnsbl_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=dnsbl';
        $this->render_data['module_word_filters'] = $this->session->user()->checkPermission($this->domain,
                'perm_word_filters_manage');
        $this->render_data['word_filters_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=word-filters';
        $this->render_data['module_blotter'] = $this->session->user()->checkPermission($this->domain,
                'perm_blotter_manage');
        $this->render_data['blotter_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=blotter';
        $this->render_data['regen_overboard_pages'] = $this->session->user()->checkPermission($this->domain,
                'perm_regen_overboard');
        $this->render_data['regen_overboard_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                'module=regen&actions=overboard-all-pages';
        $this->render_data['regen_site_pages'] = $this->session->user()->checkPermission($this->domain,
                'perm_regen_pages');
        $this->render_data['regen_pages_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
        'module=regen&actions=site-all-pages';
        $this->render_data['regen_site_caches'] = $this->session->user()->checkPermission($this->domain,
                'perm_regen_cache');
        $this->render_data['regen_caches_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=regen&actions=site-all-caches';
        $this->render_data['module_extract_gettext'] = $this->session->user()->checkPermission($this->domain,
                'perm_extract_gettext');
        $this->render_data['extract_gettext_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                'module=language&actions=extract-gettext';
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}