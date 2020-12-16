<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelUsers extends OutputCore
{

    function __construct(Domain $domain, bool $write_mode)
    {
        $this->domain = $domain;
        $this->write_mode = $write_mode;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        if (!isset($parameters['section']))
        {
            return;
        }

        $user = $parameters['user'];

        if (!$user->checkPermission($this->domain, 'perm_manage_users'))
        {
            nel_derp(300, _gettext('You are not allowed to manage users.'));
        }

        switch ($parameters['section'])
        {
            case 'panel':
                $output = $this->renderPanel($parameters, $data_only);
                break;

            case 'edit':
                $output = $this->renderEdit($parameters, $data_only);
                break;
        }

        return $output;
    }

    private function renderPanel(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Users')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'manage_headers' => $manage_headers], true);
        $users = $this->database->executeFetchAll('SELECT * FROM "' . NEL_USERS_TABLE . '"', PDO::FETCH_ASSOC);
        $bgclass = 'row1';

        foreach ($users as $user_info)
        {
            $user_data = array();
            $user_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $user_data['user_id'] = $user_info['user_id'];
            $user_data['display_name'] = $user_info['display_name'];
            $user_data['active'] = $user_info['active'];

            if ($user_info['owner'] == 0)
            {
                $user_data['edit_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=users&actions=edit&user-id=' . $user_info['user_id'];
                $user_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=users&actions=remove&user-id=' . $user_info['user_id'];
            }

            $this->render_data['users_list'][] = $user_data;
        }

        $this->render_data['new_user_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=users&actions=new';
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/users_panel_main',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }

    private function renderEdit(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $user_id = $parameters['user_id'] ?? '';
        $authorization = new \Nelliel\Auth\Authorization($this->domain->database());
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Edit User')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'manage_headers' => $manage_headers], true);

        if (empty($user_id))
        {
            $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=users&actions=add';
        }
        else
        {
            $edit_user = $authorization->getUser($user_id);
            $this->render_data['user_id'] = $edit_user->auth_data['user_id'];
            $this->render_data['display_name'] = $edit_user->auth_data['display_name'];
            $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=users&actions=update&user-id=' . $user_id;
            $this->render_data['active'] = ($edit_user->active()) ? 'checked' : '';
        }

        if (!empty($user_id) && $edit_user->isSiteOwner())
        {
            $this->render_data['is_site_owner'] = true;
        }
        else
        {
            $this->render_data['is_site_owner'] = false;
            $prepared = $this->database->prepare(
                    'SELECT "role_id" FROM "' . NEL_USER_ROLES_TABLE . '" WHERE "user_id" = ? AND "domain_id" = ?');
            $site_role = $this->database->executePreparedFetch($prepared, array($user_id, '_site_'), PDO::FETCH_COLUMN);

            if (!empty($site_role))
            {
                $this->render_data['site_role_id'] = $site_role;
            }

            $domain_list = $this->database->executeFetchAll('SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"', PDO::FETCH_ASSOC);
            array_unshift($domain_list, ['board_id' => '_site_']); // For site domain
            $query = 'SELECT "role_id", "role_title" FROM "' . NEL_ROLES_TABLE . '"';
            $roles = $this->database->executeFetchAll($query, PDO::FETCH_ASSOC);

            foreach ($domain_list as $domain)
            {
                $domain_role_data = array();
                $domain_role_data['domain_id'] = $domain['board_id'];
                $domain_role_data['domain_name'] = $domain['board_id'];
                $domain_role_data['select_name'] = 'domain_role_' . $domain['board_id'];
                $domain_role_data['select_id'] = 'domain_role_' . $domain['board_id'];
                $prepared = $this->database->prepare(
                        'SELECT "role_id" FROM "' . NEL_USER_ROLES_TABLE . '" WHERE "user_id" = ? AND "domain_id" = ?');
                $role_id = $this->database->executePreparedFetch($prepared, array($user_id, $domain['board_id']),
                        PDO::FETCH_COLUMN);

                foreach ($roles as $role)
                {
                    $role_options = array();
                    $role_options['option_id'] = $role['role_id'];
                    $role_options['option_name'] = $role['role_title'];

                    if($role['role_id'] === $role_id)
                    {
                        $role_options['option_selected'] = 'selected';
                    }

                    $domain_role_data['roles']['options'][] = $role_options;
                }

                $this->render_data['domain_roles'][] = $domain_role_data;
            }
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/users_panel_edit',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}