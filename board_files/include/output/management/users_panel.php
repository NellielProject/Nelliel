<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_users_panel_main($user, $domain)
{
    if (!$user->boardPerm('', 'perm_user_access'))
    {
        nel_derp(300, _gettext('You are not allowed to access the users panel.'));
    }

    $database = nel_database();
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    nel_render_general_header($domain->renderInstance(), null, null,
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Users')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/users_panel_main.html');

    $user_list = $dom->getElementById('user-list');
    $user_list_nodes = $user_list->getElementsByAttributeName('data-parse-id', true);
    $users = $database->executeFetchAll('SELECT "user_id", "display_name" FROM "' . USER_TABLE . '"', PDO::FETCH_ASSOC);

    foreach ($users as $user)
    {
        $user_node = $dom->copyNode($user_list_nodes['edit-user-link'], $user_list, 'append');
        $user_node->setContent($user['user_id'] . ' - ' . $user['display_name']);
        $user_node->extSetAttribute('href',
                PHP_SELF . '?module=users&action=edit&user-id=' . $user['user_id']);
    }

    $user_list_nodes['edit-user-link']->remove();
    $dom->getElementById('new-user-link')->extSetAttribute('href', PHP_SELF . '?module=users&action=new');

    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}

function nel_render_users_panel_edit($user, $domain, $user_id)
{
    if (!$user->boardPerm('', 'perm_user_access'))
    {
        nel_derp(300, _gettext('You are not allowed to access the users panel.'));
    }

    $database = nel_database();
    $authorization = new \Nelliel\Auth\Authorization($database);
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    nel_render_general_header($domain->renderInstance(), null, null,
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Edit User')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/users_panel_edit.html');

    if (is_null($user_id))
    {
        $dom->getElementById('user-edit-form')->extSetAttribute('action',
                PHP_SELF . '?module=users&action=add');
    }
    else
    {
        $user = $authorization->getUser($user_id);
        $dom->getElementById('user-id-field')->extSetAttribute('value', $user->auth_data['user_id']);
        $dom->getElementById('display_name')->extSetAttribute('value', $user->auth_data['display_name']);
        $dom->getElementById('user-edit-form')->extSetAttribute('action',
                PHP_SELF . '?module=users&action=update&user-id=' . $user_id);
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
        foreach ($boards as $board)
        {
            $new_board = $board_roles->cloneNode(true);
            $board_roles->parentNode->appendChild($new_board);
            $new_board->removeAttribute('id');
            $new_board_nodes = $new_board->getElementsByAttributeName('data-parse-id', true);

            if ($board === '')
            {
                $new_board_nodes['user-board-role-label']->setContent(_gettext('All Boards'));
            }
            else
            {
                $new_board_nodes['user-board-role-label']->setContent($board);
            }

            $new_board_nodes['user-board-role-id']->extSetAttribute('name', 'user_board_role_' . $board);

            if (isset($user_boards[$board]))
            {
                $new_board_nodes['user-board-role-id']->extSetAttribute('value', $user_boards[$board]);
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
