<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_roles_panel_main($user, $domain)
{
    if (!$user->boardPerm('', 'perm_role_access'))
    {
        nel_derp(310, _gettext('You are not allowed to access the staff panel.'));
    }

    $database = nel_database();
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    nel_render_general_header($domain->renderInstance(), null, null,
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Roles')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/roles_panel_main.html');
    $role_info_table = $dom->getElementById('role-info-table');
    $role_info_table_nodes = $role_info_table->getElementsByAttributeName('data-parse-id', true);
    $roles = $database->executeFetchAll('SELECT * FROM "' . ROLES_TABLE . '"', PDO::FETCH_ASSOC);
    $bgclass = 'row1';

    foreach ($roles as $role)
    {
        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $role_row = $dom->copyNode($role_info_table_nodes['role-info-row'], $role_info_table, 'append');
        $role_row->extSetAttribute('class', $bgclass);
        $role_row_nodes = $role_row->getElementsByAttributeName('data-parse-id', true);
        $role_row_nodes['role-id']->setContent($role['role_id']);
        $role_row_nodes['level']->setContent($role['role_level']);
        $role_row_nodes['title']->setContent($role['role_title']);
        $role_row_nodes['capcode-text']->setContent($role['capcode_text']);
        $role_row_nodes['role-edit-link']->extSetAttribute('href',
                PHP_SELF . '?module=roles&action=edit&role-id=' . $role['role_id']);
        $role_row_nodes['role-remove-link']->extSetAttribute('href',
                PHP_SELF . '?module=roles&action=remove&role-id=' . $role['role_id']);
    }

    $role_info_table_nodes['role-info-row']->remove();
    $dom->getElementById('new-role-link')->extSetAttribute('href', PHP_SELF . '?module=roles&action=new');


    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}

function nel_render_roles_panel_edit($user, $domain, $role_id)
{
    if (!$user->boardPerm('', 'perm_role_access'))
    {
        nel_derp(310, _gettext('You are not allowed to access the roles panel.'));
    }

    $database = nel_database();
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $translator = new \Nelliel\Language\Translator();
    $role = $authorization->getRole($role_id);
    $domain->renderInstance()->startRenderTimer();
    nel_render_general_header($domain->renderInstance(), null, null,
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Edit Role')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/roles_panel_edit.html');

    if (is_null($role_id))
    {
        $dom->getElementById('role-edit-form')->extSetAttribute('action', PHP_SELF . '?module=roles&action=add');
    }
    else
    {
        $dom->getElementById('role-edit-form')->extSetAttribute('action',
                PHP_SELF . '?module=roles&action=update&role-id=' . $role_id);
    }

    $role_settings_table = $dom->getElementById('role-edit-settings');
    $role_settings_nodes = $role_settings_table->getElementsByAttributeName('data-parse-id', true);
    $permissions_list = $database->executeFetchAll('SELECT * FROM "' . PERMISSIONS_TABLE . '" ORDER BY "entry" ASC',
            PDO::FETCH_ASSOC);

    if (!is_null($role_id))
    {
        $dom->getElementById('role_id')->extSetAttribute('value', $role->auth_data['role_id']);
        $dom->getElementById('role_level')->extSetAttribute('value', $role->auth_data['role_level']);
        $dom->getElementById('role_title')->extSetAttribute('value', $role->auth_data['role_title']);
        $dom->getElementById('capcode_text')->setContent($role->auth_data['capcode_text']);
    }

    foreach ($permissions_list as $permission)
    {
        $permission_row = $dom->copyNode($role_settings_nodes['permissions-row'], $role_settings_table, 'append');
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
    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}