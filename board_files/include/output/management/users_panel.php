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
    nel_render_general_header($domain, null,
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Users')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/users_panel_main.html');
    $user_info_table = $dom->getElementById('user-info-table');
    $user_info_table_nodes = $user_info_table->getElementsByAttributeName('data-parse-id', true);
    $users = $database->executeFetchAll('SELECT * FROM "' . USERS_TABLE . '"',
            PDO::FETCH_ASSOC);
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
        $user_row_nodes['user-edit-link']->extSetAttribute('href',
                PHP_SELF . '?module=users&action=edit&user-id=' . $user_info['user_id']);
        $user_row_nodes['user-remove-link']->extSetAttribute('href',
                PHP_SELF . '?module=users&action=remove&user-id=' . $user_info['user_id']);
    }

    $user_info_table_nodes['user-info-row']->remove();
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
    nel_render_general_header($domain, null,
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Edit User')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/users_panel_edit.html');

    if (is_null($user_id))
    {
        $dom->getElementById('user-edit-form')->extSetAttribute('action', PHP_SELF . '?module=users&action=add');
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
