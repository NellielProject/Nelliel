<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelRoles extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function main(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Roles')];
        $this->render_data['header'] = $output_header->general(['manage_headers' => $manage_headers], true);
        $roles = $this->database->executeFetchAll('SELECT * FROM "' . NEL_ROLES_TABLE . '" ORDER BY "role_level" DESC',
                PDO::FETCH_ASSOC);
        $bgclass = 'row1';

        foreach ($roles as $role)
        {
            $role_data = array();
            $role_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $role_data['role_id'] = $role['role_id'];
            $role_data['role_level'] = $role['role_level'];
            $role_data['role_title'] = $role['role_title'];
            $role_data['capcode'] = $role['capcode'];
            $role_data['edit_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=roles&actions=edit&role-id=' .
                    $role['role_id'];
            $role_row_nodes['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    'module=admin&section=roles&actions=remove&role-id=' . $role['role_id'];
            $this->render_data['roles_list'][] = $role_data;
        }

        $this->render_data['new_role_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=roles&actions=new';

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/roles_panel_main',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }

    public function new(array $parameters, bool $data_only)
    {
        $this->edit($parameters, $data_only);
    }

    public function edit(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $role_id = $parameters['role_id'] ?? '';
        $authorization = new \Nelliel\Auth\Authorization($this->domain->database());
        $role = $authorization->getRole($role_id);
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Edit Role')];
        $this->render_data['header'] = $output_header->general(['manage_headers' => $manage_headers], true);

        if (empty($role_id))
        {
            $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=roles&actions=add';
        }
        else
        {
            $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    'module=admin&section=roles&actions=update&role-id=' . $role_id;
        }

        if (!empty($role_id))
        {
            $this->render_data['role_id'] = $role->auth_data['role_id'];
            $this->render_data['role_level'] = $role->auth_data['role_level'];
            $this->render_data['role_title'] = $role->auth_data['role_title'];
            $this->render_data['capcode'] = $role->auth_data['capcode'];
        }

        $permissions_list = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_PERMISSIONS_TABLE . '" ORDER BY "entry" ASC', PDO::FETCH_ASSOC);

        foreach ($permissions_list as $permission)
        {
            $permission_data = array();

            if (!empty($role_id))
            {
                if ($role->checkPermission($permission['permission']))
                {
                    $permission_data['checked'] = 'checked';
                }
            }

            $permission_data['permission'] = $permission['permission'];
            $permission_data['label'] = '(' . $permission['permission'] . ') - ' . $permission['description'];
            $this->render_data['permissions_list'][] = $permission_data;
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/roles_panel_edit',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}