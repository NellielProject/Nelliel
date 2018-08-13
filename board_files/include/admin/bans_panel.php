<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/ban_panel.php';

//
// Ban control panel
//
function nel_ban_control($inputs)
{
    $dbh = nel_database();
    $authorize = nel_authorize();
    $ban_hammer = new \Nelliel\BanHammer();

    if ($inputs['action'] === 'modify')
    {
        if (!$authorize->getUserPerm($_SESSION['username'], 'perm_ban_modify', $inputs['board_id']))
        {
            nel_derp(322, _gettext('You are not allowed to modify bans.'));
        }

        nel_render_ban_panel_modify($inputs['board_id']);
    }
    else if ($inputs['action'] === 'new')
    {
        if (!$authorize->getUserPerm($_SESSION['username'], 'perm_ban_add', $inputs['board_id']))
        {
            nel_derp(321, _gettext('You are not allowed to add new bans.'));
        }

        $ip = (isset($_GET['ban_ip'])) ? $_GET['ban_ip'] : '';
        $type = (isset($_GET['ban_type'])) ? $_GET['ban_type'] : 'GENERAL';
        nel_render_ban_panel_add($inputs['board_id'], $ip, $type);
    }
    else if ($inputs['action'] === 'add')
    {
        if (!$authorize->getUserPerm($_SESSION['username'], 'perm_ban_add', $inputs['board_id']))
        {
            nel_derp(321, _gettext('You are not allowed to add new bans.'));
        }

        $ban_input = $ban_hammer->postToArray();
        $ban_hammer->addBan($ban_input);
        nel_render_main_ban_panel($inputs['board_id']);
    }
    else if ($inputs['action'] === 'remove')
    {
        if (!$authorize->getUserPerm($_SESSION['username'], 'perm_ban_delete', $inputs['board_id']))
        {
            nel_derp(323, _gettext('You are not allowed to delete bans.'));
        }

        $ban_input = $ban_hammer->postToArray();
        $ban_hammer->removeBan($ban_input['ban_id']);
        nel_render_main_ban_panel($inputs['board_id']);
    }
    else if ($inputs['action'] === 'update')
    {
        if (!$authorize->getUserPerm($_SESSION['username'], 'perm_ban_modify', $inputs['board_id']))
        {
            nel_derp(322, _gettext('You are not allowed to modify bans.'));
        }

        $ban_input = $ban_hammer->postToArray();
        $ban_hammer->modifyBan($ban_input);
        nel_render_main_ban_panel($inputs['board_id']);
    }
    else
    {
        if (!$authorize->getUserPerm($_SESSION['username'], 'perm_ban_access', $inputs['board_id']))
        {
            nel_derp(320, _gettext('You are not allowed to access the bans panel.'));
        }

        nel_render_main_ban_panel($inputs['board_id']);
    }
}
