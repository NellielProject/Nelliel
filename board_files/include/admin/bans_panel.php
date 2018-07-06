<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/ban_panel.php';

//
// Ban control panel
//
function nel_ban_control($board_id, $action)
{
    $dbh = nel_database();
    $authorize = nel_authorize();
    $ban_hammer = new \Nelliel\BanHammer();

    if ($action === 'modify')
    {
        if (!$authorize->getUserPerm($_SESSION['username'], 'perm_ban_modify', $board_id))
        {
            nel_derp(322, _gettext('You are not allowed to modify bans.'));
        }

        nel_render_ban_panel_modify($board_id);
    }
    else if ($action === 'new')
    {
        if (!$authorize->getUserPerm($_SESSION['username'], 'perm_ban_add', $board_id))
        {
            nel_derp(321, _gettext('You are not allowed to add new bans.'));
        }

        nel_render_ban_panel_add($board_id);
    }
    else if ($action === 'add')
    {
        if (!$authorize->getUserPerm($_SESSION['username'], 'perm_ban_add', $board_id))
        {
            nel_derp(321, _gettext('You are not allowed to add new bans.'));
        }

        $ban_input = $ban_hammer->postToArray();
        $ban_hammer->addBan($ban_input);
        nel_render_main_ban_panel($board_id);
    }
    else if ($action === 'remove')
    {
        if (!$authorize->getUserPerm($_SESSION['username'], 'perm_ban_delete', $board_id))
        {
            nel_derp(323, _gettext('You are not allowed to delete bans.'));
        }

        $ban_input = $ban_hammer->postToArray();
        $ban_hammer->removeBan($ban_input['ban_id']);
        nel_render_main_ban_panel($board_id);
    }
    else if ($action === 'update')
    {
        if (!$authorize->getUserPerm($_SESSION['username'], 'perm_ban_modify', $board_id))
        {
            nel_derp(322, _gettext('You are not allowed to modify bans.'));
        }

        $ban_input = $ban_hammer->postToArray();
        $ban_hammer->modifyBan($ban_input);
        nel_render_main_ban_panel($board_id);
    }
    else
    {
        if (!$authorize->getUserPerm($_SESSION['username'], 'perm_ban_access', $board_id))
        {
            nel_derp(320, _gettext('You are not allowed to access the bans panel.'));
        }

        nel_render_main_ban_panel($board_id);
    }
}
