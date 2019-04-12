<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelRoles extends OutputCore
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
        if (!isset($parameters['section']))
        {
            return;
        }

        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_roles_access'))
        {
            nel_derp(341, _gettext('You are not allowed to access the bans panel.'));
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
        $render_data = array();
        $user = $parameters['user'];
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $render_data['head'] = $output_head->render(['dotdot' => $dotdot]);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Roles')];
        $render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'extra_data' => $extra_data], true);
        $roles = $this->database->executeFetchAll('SELECT * FROM "' . ROLES_TABLE . '"', PDO::FETCH_ASSOC);
        $bgclass = 'row1';

        foreach ($roles as $role)
        {
            $role_data = array();
            $role_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $role_data['role_id'] = $role['role_id'];
            $role_data['role_level'] = $role['role_level'];
            $role_data['role_title'] = $role['role_title'];
            $role_data['capcode_text'] = $role['capcode_text'];
            $role_data['edit_url'] = MAIN_SCRIPT . '?module=roles&action=edit&role-id=' . $role['role_id'];
            $role_row_nodes['remove_url'] = MAIN_SCRIPT . '?module=roles&action=remove&role-id=' . $role['role_id'];
            $render_data['roles_list'][] = $role_data;
        }

        $render_data['new_role_url'] = MAIN_SCRIPT . '?module=roles&action=new';

        $render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/roles_panel_main',
                $render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output($render_data, 'page', true);
        echo $output;
        return $output;
    }

    private function renderEdit(array $parameters, bool $data_only)
    {
        $render_data = array();
        $user = $parameters['user'];
        $role_id = $parameters['role_id'];
        $authorization = new \Nelliel\Auth\Authorization($this->domain->database());
        $role = $authorization->getRole($role_id);
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $render_data['head'] = $output_head->render(['dotdot' => $dotdot]);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Edit Role')];
        $render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'extra_data' => $extra_data], true);

        if (is_null($role_id))
        {
            $render_data['form_action'] = MAIN_SCRIPT . '?module=roles&action=add';
        }
        else
        {
            $render_data['form_action'] = MAIN_SCRIPT . '?module=roles&action=update&role-id=' . $role_id;
        }

        if (!is_null($role_id))
        {
            $render_data['role_id'] = $role->auth_data['role_id'];
            $render_data['role_level'] = $role->auth_data['role_level'];
            $render_data['role_title'] = $role->auth_data['role_title'];
            $render_data['capcode_text'] = $role->auth_data['capcode_text'];
        }

        $permissions_list = $this->database->executeFetchAll(
                'SELECT * FROM "' . PERMISSIONS_TABLE . '" ORDER BY "entry" ASC', PDO::FETCH_ASSOC);

        foreach ($permissions_list as $permission)
        {
            $permission_data = array();

            if (!is_null($role_id))
            {
                if ($role->checkPermission($permission['permission']))
                {
                    $permission_data['checked'] = 'checked';
                }
            }

            $permission_data['permission'] = $permission['permission'];
            $permission_data['label'] = '(' . $permission['permission'] . ') - ' . $permission['description'];
        }

        $render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/roles_panel_edit',
                $render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output($render_data, 'page', true);
        echo $output;
        return $output;
    }
}