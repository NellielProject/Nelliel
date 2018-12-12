<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_staff_panel_main()
{
    $database = nel_database();
    $translator = new \Nelliel\Language\Translator();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, null,
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Staff')));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/staff_panel_main.html');

    $user_list = $dom->getElementById('user-list');
    $user_list_nodes = $user_list->getElementsByAttributeName('data-parse-id', true);
    $users = $database->executeFetchAll('SELECT "user_id", "display_name" FROM "' . USER_TABLE . '"', PDO::FETCH_ASSOC);

    foreach ($users as $user)
    {
        $user_node = $dom->copyNode($user_list_nodes['edit-user-link'], $user_list, 'append');
        $user_node->setContent($user['user_id'] . ' - ' . $user['display_name']);
        $user_node->extSetAttribute('href', PHP_SELF . '?module=staff&section=user&action=edit&user-id=' . $user['user_id']);
    }

    $user_list_nodes['edit-user-link']->remove();
    $dom->getElementById('new-user-link')->extSetAttribute('href', PHP_SELF . '?module=staff&section=user&action=new');

    $role_list = $dom->getElementById('role-list');
    $role_list_nodes = $role_list->getElementsByAttributeName('data-parse-id', true);
    $roles = $database->executeFetchAll('SELECT "role_id", "role_title" FROM "' . ROLES_TABLE . '"', PDO::FETCH_ASSOC);

    foreach ($roles as $role)
    {
        $role_node = $dom->copyNode($role_list_nodes['edit-role-link'], $role_list, 'append');
        $role_node->setContent($role['role_id'] . ' - ' . $role['role_title']);
        $role_node->extSetAttribute('href', PHP_SELF . '?module=staff&section=role&action=edit&role-id=' . $role['role_id']);
    }

    $role_list_nodes['edit-role-link']->remove();
    $dom->getElementById('new-role-link')->extSetAttribute('href', PHP_SELF . '?module=staff&section=role&action=new');

    $translator->translateDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}

function nel_render_staff_panel_user_edit($user_id)
{
    $database = nel_database();
    $authorization = new \Nelliel\Auth\Authorization($database);
    $translator = new \Nelliel\Language\Translator();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, null,
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Staff: Edit User')));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/staff_panel_user_edit.html');
    $dom->getElementById('user-edit-form')->extSetAttribute('action',
            PHP_SELF . '?module=staff&section=user&action=update');

    if (!is_null($user_id))
    {
        $user = $authorization->getUser($user_id);
        $dom->getElementById('user-id-field')->extSetAttribute('value', $user->auth_data['user_id']);
        $dom->getElementById('display_name')->extSetAttribute('value', $user->auth_data['display_name']);
    }

    $board_roles = $dom->getElementById('board-roles');
    $boards = $database->executeFetchAll('SELECT "board_id" FROM "' . BOARD_DATA_TABLE . '"', PDO::FETCH_COLUMN);
    array_unshift($boards, '');

    $prepared = $database->prepare('SELECT * FROM "' . USER_ROLE_TABLE . '" WHERE "user_id" = ?');
    $result = $database->executePreparedFetchAll($prepared, array($user_id), PDO::FETCH_ASSOC);
    $user_boards = array();

    if ($result !== false)
    {
        foreach ($result as $board_role)
        {
            $user_boards[$board_role['board']] = $board_role['role_id'];
        }
    }

    if ($boards !== false)
    {
        foreach ($boards as $domain)
        {
            $new_board = $board_roles->cloneNode(true);
            $board_roles->parentNode->appendChild($new_board);
            $new_board->removeAttribute('id');
            $new_board_nodes = $new_board->getElementsByAttributeName('data-parse-id', true);

            if($domain === '')
            {
                $new_board_nodes['user-board-role-label']->setContent(_gettext('All Boards'));
            }
            else
            {
                $new_board_nodes['user-board-role-label']->setContent($domain);
            }

            $new_board_nodes['user-board-role-id']->extSetAttribute('name', 'user_board_role_' . $domain);

            if(isset($user_boards[$domain]))
            {
                $new_board_nodes['user-board-role-id']->extSetAttribute('value', $board_role['role_id']);
            }
        }
    }

    $board_roles->remove();
    $translator->translateDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}

function nel_render_staff_panel_role_edit($role_id)
{
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $translator = new \Nelliel\Language\Translator();
    $role = $authorization->getRole($role_id);
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, null,
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Staff: Edit Role')));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/staff_panel_role_edit.html');
    $dom->getElementById('role-edit-form')->extSetAttribute('action',
            PHP_SELF . '?module=staff&section=role&action=update');

    if (!is_null($role_id))
    {
        $dom->getElementById('role_id')->extSetAttribute('value', $role->auth_data['role_id']);
        $dom->getElementById('role_level')->extSetAttribute('value', $role->auth_data['role_level']);
        $dom->getElementById('role_title')->extSetAttribute('value', $role->auth_data['role_title']);
        $dom->getElementById('capcode_text')->extSetAttribute('value', $role->auth_data['capcode_text']);

        foreach ($role->permissions->auth_data as $key => $value)
        {
            if ($value === true)
            {
                $element = $dom->getElementById($key);

                if(!is_null($element))
                {
                    $element->extSetAttribute('checked', $value);
                }
            }
        }
    }

    $translator->translateDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}