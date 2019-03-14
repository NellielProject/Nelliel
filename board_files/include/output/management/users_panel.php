<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_users_panel_main($user, \Nelliel\Domain $domain)
{
    if (!$user->domainPermission($domain, 'perm_user_access'))
    {
        nel_derp(300, _gettext('You are not allowed to access the users panel.'));
    }

    $database = nel_database();
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    $output_header = new \Nelliel\Output\OutputHeader($domain);
    $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Users')];
    $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/users_panel_main.html');
    $user_info_table = $dom->getElementById('user-info-table');
    $user_info_table_nodes = $user_info_table->getElementsByAttributeName('data-parse-id', true);
    $users = $database->executeFetchAll('SELECT * FROM "' . USERS_TABLE . '"', PDO::FETCH_ASSOC);
    $bgclass = 'row1';

    foreach ($users as $user_info)
    {
        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $user_row = $dom->copyNode($user_info_table_nodes['user-info-row'], $user_info_table, 'append');
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
    $dom->getElementById('new-user-link')->extSetAttribute('href', MAIN_SCRIPT . '?module=users&action=new');

    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}

function nel_render_users_panel_edit($user, \Nelliel\Domain $domain, $user_id)
{
    if (!$user->domainPermission($domain, 'perm_user_access'))
    {
        nel_derp(300, _gettext('You are not allowed to access the users panel.'));
    }

    $database = nel_database();
    $authorization = new \Nelliel\Auth\Authorization($database);
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    $output_header = new \Nelliel\Output\OutputHeader($domain);
    $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Edit User')];
    $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/users_panel_edit.html');

    if (is_null($user_id))
    {
        $dom->getElementById('user-edit-form')->extSetAttribute('action', MAIN_SCRIPT . '?module=users&action=add');
    }
    else
    {
        $edit_user = $authorization->getUser($user_id);
        $dom->getElementById('user-id-field')->extSetAttribute('value', $edit_user->auth_data['user_id']);
        $dom->getElementById('display-name')->extSetAttribute('value', $edit_user->auth_data['display_name']);
        $dom->getElementById('user-edit-form')->extSetAttribute('action',
                MAIN_SCRIPT . '?module=users&action=update&user-id=' . $user_id);

        if ($edit_user->active())
        {
            $dom->getElementById('user-active')->extSetAttribute('checked', 'true');
        }

        if ($edit_user->isSuperAdmin())
        {
            $dom->getElementById('super-admin')->extSetAttribute('checked', 'true');
        }
    }

    $board_roles = $dom->getElementById('board-roles');
    $prepared = $database->prepare('SELECT * FROM "' . USER_ROLES_TABLE . '" WHERE "user_id" = ?');
    $user_roles = $database->executePreparedFetchAll($prepared, array($user_id), PDO::FETCH_ASSOC);

    if ($user_roles !== false)
    {
        foreach ($user_roles as $user_role)
        {
            if ($user_role['domain_id'] == '')
            {
                $dom->getElementById('site-role')->extSetAttribute('value', $user_role['role_id']);
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
    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}
