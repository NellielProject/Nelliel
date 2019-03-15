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
        $this->prepare('management/users_panel_main.html');
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Users')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $user_info_table = $this->dom->getElementById('user-info-table');
        $user_info_table_nodes = $user_info_table->getElementsByAttributeName('data-parse-id', true);
        $users = $this->database->executeFetchAll('SELECT * FROM "' . USERS_TABLE . '"', PDO::FETCH_ASSOC);
        $bgclass = 'row1';

        foreach ($users as $user_info)
        {
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $user_row = $this->dom->copyNode($user_info_table_nodes['user-info-row'], $user_info_table, 'append');
            $user_row->extSetAttribute('class', $bgclass);
            $user_row_nodes = $user_row->getElementsByAttributeName('data-parse-id', true);
            $user_row_nodes['user-id']->setContent($user_info['user_id']);
            $user_row_nodes['display-name']->setContent($user_info['display_name']);
            $user_row_nodes['active']->setContent($user_info['active']);
            $user_row_nodes['super-admin']->setContent($user_info['super_admin']);
            $user_row_nodes['user-edit-link']->extSetAttribute('href',
                    MAIN_SCRIPT . '?module=users&action=edit&user-id=' . $user_info['user_id']);
            $user_row_nodes['user-remove-link']->extSetAttribute('href',
                    MAIN_SCRIPT . '?module=users&action=remove&user-id=' . $user_info['user_id']);
        }

        $user_info_table_nodes['user-info-row']->remove();
        $this->dom->getElementById('new-user-link')->extSetAttribute('href', MAIN_SCRIPT . '?module=users&action=new');

        $this->domain->translator()->translateDom($this->dom);
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }

    private function renderEdit(array $parameters)
    {
        $user = $parameters['user'];
        $user_id = $parameters['user_id'];
        $this->prepare('management/users_panel_edit.html');
        $authorization = new \Nelliel\Auth\Authorization($this->database);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Edit User')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);

        if (is_null($user_id))
        {
            $this->dom->getElementById('user-edit-form')->extSetAttribute('action', MAIN_SCRIPT . '?module=users&action=add');
        }
        else
        {
            $edit_user = $authorization->getUser($user_id);
            $this->dom->getElementById('user-id-field')->extSetAttribute('value', $edit_user->auth_data['user_id']);
            $this->dom->getElementById('display-name')->extSetAttribute('value', $edit_user->auth_data['display_name']);
            $this->dom->getElementById('user-edit-form')->extSetAttribute('action',
                    MAIN_SCRIPT . '?module=users&action=update&user-id=' . $user_id);

            if ($edit_user->active())
            {
                $this->dom->getElementById('user-active')->extSetAttribute('checked', 'true');
            }

            if ($edit_user->isSuperAdmin())
            {
                $this->dom->getElementById('super-admin')->extSetAttribute('checked', 'true');
            }
        }

        $board_roles = $this->dom->getElementById('board-roles');
        $prepared = $this->database->prepare('SELECT * FROM "' . USER_ROLES_TABLE . '" WHERE "user_id" = ?');
        $user_roles = $this->database->executePreparedFetchAll($prepared, array($user_id), PDO::FETCH_ASSOC);

        if ($user_roles !== false)
        {
            foreach ($user_roles as $user_role)
            {
                if ($user_role['domain_id'] == '')
                {
                    $this->dom->getElementById('site-role')->extSetAttribute('value', $user_role['role_id']);
                }
                else
                {
                    $board_roles->parentNode->appendChild($board_roles->cloneNode(true));
                    $new_board->removeAttribute('id');
                    $new_board_nodes = $new_board->getElementsByAttributeName('data-parse-id', true);
                    $new_board_nodes['board-role-label']->setContent($user_role['domain']);
                    $new_board_nodes['board-role']->extSetAttribute('name', 'board_role_' . $user_role['domain']);
                    $new_board_nodes['board-role']->extSetAttribute('value', $user_role['domain']);
                }
            }
        }

        $board_roles->remove();

        $this->domain->translator()->translateDom($this->dom);
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }
}