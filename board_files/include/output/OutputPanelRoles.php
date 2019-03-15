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
    private $database;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $domain->database();
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        if(!isset($parameters['section']))
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
                $this->renderPanel($parameters);
                break;

            case 'edit':
                $this->renderEdit($parameters);
                break;
        }
    }

    private function renderPanel(array $parameters)
    {
        $user = $parameters['user'];
        $this->prepare('management/roles_panel_main.html');
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Roles')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $role_info_table = $this->dom->getElementById('role-info-table');
        $role_info_table_nodes = $role_info_table->getElementsByAttributeName('data-parse-id', true);
        $roles = $this->database->executeFetchAll('SELECT * FROM "' . ROLES_TABLE . '"', PDO::FETCH_ASSOC);
        $bgclass = 'row1';

        foreach ($roles as $role)
        {
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $role_row = $this->dom->copyNode($role_info_table_nodes['role-info-row'], $role_info_table, 'append');
            $role_row->extSetAttribute('class', $bgclass);
            $role_row_nodes = $role_row->getElementsByAttributeName('data-parse-id', true);
            $role_row_nodes['role-id']->setContent($role['role_id']);
            $role_row_nodes['level']->setContent($role['role_level']);
            $role_row_nodes['title']->setContent($role['role_title']);
            $role_row_nodes['capcode-text']->setContent($role['capcode_text']);
            $role_row_nodes['role-edit-link']->extSetAttribute('href',
                    MAIN_SCRIPT . '?module=roles&action=edit&role-id=' . $role['role_id']);
            $role_row_nodes['role-remove-link']->extSetAttribute('href',
                    MAIN_SCRIPT . '?module=roles&action=remove&role-id=' . $role['role_id']);
        }

        $role_info_table_nodes['role-info-row']->remove();
        $this->dom->getElementById('new-role-link')->extSetAttribute('href', MAIN_SCRIPT . '?module=roles&action=new');

        $this->domain->translator()->translateDom($this->dom);
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }

    private function renderEdit(array $parameters)
    {
        $user = $parameters['user'];
        $role_id = $parameters['role_id'];
        $this->prepare('management/roles_panel_edit.html');
        $authorization = new \Nelliel\Auth\Authorization($this->domain->database());
        $role = $authorization->getRole($role_id);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Edit Role')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);

        if (is_null($role_id))
        {
            $this->dom->getElementById('role-edit-form')->extSetAttribute('action', MAIN_SCRIPT . '?module=roles&action=add');
        }
        else
        {
            $this->dom->getElementById('role-edit-form')->extSetAttribute('action',
                    MAIN_SCRIPT . '?module=roles&action=update&role-id=' . $role_id);
        }

        $role_settings_table = $this->dom->getElementById('role-edit-settings');
        $role_settings_nodes = $role_settings_table->getElementsByAttributeName('data-parse-id', true);
        $permissions_list = $this->database->executeFetchAll('SELECT * FROM "' . PERMISSIONS_TABLE . '" ORDER BY "entry" ASC',
                PDO::FETCH_ASSOC);

        if (!is_null($role_id))
        {
            $this->dom->getElementById('role_id')->extSetAttribute('value', $role->auth_data['role_id']);
            $this->dom->getElementById('role_level')->extSetAttribute('value', $role->auth_data['role_level']);
            $this->dom->getElementById('role_title')->extSetAttribute('value', $role->auth_data['role_title']);
            $this->dom->getElementById('capcode_text')->setContent($role->auth_data['capcode_text']);
        }

        foreach ($permissions_list as $permission)
        {
            $permission_row = $this->dom->copyNode($role_settings_nodes['permissions-row'], $role_settings_table, 'append');
            $permission_row_nodes = $permission_row->getElementsByAttributeName('data-parse-id', true);

            if (!is_null($role_id))
            {
                if ($role->checkPermission($permission['permission']))
                {
                    $permission_row_nodes['entry-checkbox']->extSetAttribute('checked', true);
                }
            }

            $permission_row_nodes['entry-checkbox']->extSetAttribute('name', $permission['permission']);
            $permission_row_nodes['entry-hidden-checkbox']->extSetAttribute('name', $permission['permission']);
            $permission_row_nodes['entry-label']->setContent(
                    '(' . $permission['permission'] . ') - ' . $permission['description']);
        }

        $role_settings_nodes['permissions-row']->remove();

        $this->domain->translator()->translateDom($this->dom);
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }
}