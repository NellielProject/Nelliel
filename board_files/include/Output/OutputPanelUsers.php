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

    public function render(array $parameters = array())
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

        $this->render_core->startTimer();
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Users')];
        $this->render_core->appendToOutput(
                $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]));
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
            $render_input['users_list'][] = $user_data;
        }

        $render_input['new_user_url'] = MAIN_SCRIPT . '?module=users&action=new';

        $this->render_core->appendToOutput(
                $this->render_core->renderFromTemplateFile('management/panels/users_panel_main', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_core->appendToOutput($output_footer->render(['dotdot' => '', 'generate_styles' => false]));
        echo $this->render_core->getOutput();
        nel_clean_exit();
    }

    private function renderEdit(array $parameters)
    {
        $user = $parameters['user'];
        $user_id = $parameters['user_id'];
        $authorization = new \Nelliel\Auth\Authorization($this->domain->database());

        $this->render_core->startTimer();
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Edit User')];
        $this->render_core->appendToOutput(
                $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]));

        if (is_null($user_id))
        {
            $render_input['form_action'] = MAIN_SCRIPT . '?module=users&action=add';
        }
        else
        {
            $edit_user = $authorization->getUser($user_id);
            $render_input['user_id'] = $edit_user->auth_data['user_id'];
            $render_input['display_name'] = $edit_user->auth_data['display_name'];
            $render_input['form_action'] = MAIN_SCRIPT . '?module=users&action=update&user-id=' . $user_id;
            $render_input['active'] = ($edit_user->active()) ? 'checked' : '';
            $render_input['super_admin'] = ($edit_user->isSuperAdmin()) ? 'checked' : '';
        }

        $prepared = $this->database->prepare(
                'SELECT "role_id" FROM "' . USER_ROLES_TABLE . '" WHERE "user_id" = ? AND "domain_id" = ?');
        $site_role = $this->database->executePreparedFetch($prepared, array($user_id, ''), PDO::FETCH_COLUMN);

        if (!empty($site_role))
        {
            $render_input['site_role_id'] = $site_role;
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

            $render_input['board_roles'][] = $board_role_data;
        }

        $this->render_core->appendToOutput(
                $this->render_core->renderFromTemplateFile('management/panels/users_panel_edit', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_core->appendToOutput($output_footer->render(['dotdot' => '', 'generate_styles' => false]));
        echo $this->render_core->getOutput();
        nel_clean_exit();
    }
}