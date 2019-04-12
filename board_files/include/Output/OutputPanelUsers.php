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

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
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

        if (!$user->domainPermission($this->domain, 'perm_user_access'))
        {
            nel_derp(300, _gettext('You are not allowed to access the users panel.'));
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
        $this->render_data = array();
        $user = $parameters['user'];
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Users')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);
        $users = $this->database->executeFetchAll('SELECT * FROM "' . USERS_TABLE . '"', PDO::FETCH_ASSOC);
        $bgclass = 'row1';

        foreach ($users as $user_info)
        {
            $user_data = array();
            $user_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $user_data['user_id'] = $user_info['user_id'];
            $user_data['display_name'] = $user_info['display_name'];
            $user_data['active'] = $user_info['active'];
            $user_data['super_admin'] = $user_info['super_admin'];
            $user_data['edit_url'] = MAIN_SCRIPT . '?module=users&action=edit&user-id=' . $user_info['user_id'];
            $this->render_data['users_list'][] = $user_data;
        }

        $this->render_data['new_user_url'] = MAIN_SCRIPT . '?module=users&action=new';
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/users_panel_main',
                $this->render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('page', $data_only, true);
        echo $output;
        return $output;
    }

    private function renderEdit(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $user = $parameters['user'];
        $user_id = $parameters['user_id'];
        $authorization = new \Nelliel\Auth\Authorization($this->domain->database());
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Edit User')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);

        if (is_null($user_id))
        {
            $this->render_data['form_action'] = MAIN_SCRIPT . '?module=users&action=add';
        }
        else
        {
            $edit_user = $authorization->getUser($user_id);
            $this->render_data['user_id'] = $edit_user->auth_data['user_id'];
            $this->render_data['display_name'] = $edit_user->auth_data['display_name'];
            $this->render_data['form_action'] = MAIN_SCRIPT . '?module=users&action=update&user-id=' . $user_id;
            $this->render_data['active'] = ($edit_user->active()) ? 'checked' : '';
            $this->render_data['super_admin'] = ($edit_user->isSuperAdmin()) ? 'checked' : '';
        }

        $prepared = $this->database->prepare(
                'SELECT "role_id" FROM "' . USER_ROLES_TABLE . '" WHERE "user_id" = ? AND "domain_id" = ?');
        $site_role = $this->database->executePreparedFetch($prepared, array($user_id, ''), PDO::FETCH_COLUMN);

        if (!empty($site_role))
        {
            $this->render_data['site_role_id'] = $site_role;
        }

        $board_list = $this->database->executeFetchAll('SELECT * FROM "' . BOARD_DATA_TABLE . '"', PDO::FETCH_ASSOC);

        foreach ($board_list as $board)
        {
            $board_role_data = array();
            $board_role_data['board_id'] = $board['board_id'];
            $prepared = $this->database->prepare(
                    'SELECT "role_id" FROM "' . USER_ROLES_TABLE . '" WHERE "user_id" = ? AND "domain_id" = ?');
            $role_id = $this->database->executePreparedFetch($prepared, array($user_id, $board['board_id']),
                    PDO::FETCH_COLUMN);

            if (!empty($role_id))
            {
                $board_role_data['role_id'] = $user_role['role_id'];
            }

            $this->render_data['board_roles'][] = $board_role_data;
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/users_panel_edit',
                $this->render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('page', $data_only, true);
        echo $output;
        return $output;
    }
}