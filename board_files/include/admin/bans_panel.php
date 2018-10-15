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
    $ban_hammer = new \Nelliel\BanHammer(nel_database(), nel_authorize());
    $user = $authorize->getUser($_SESSION['username']);

    if ($inputs['action'] === 'modify')
    {
        if (!$user->boardPerm($inputs['board_id'], 'perm_ban_modify'))
        {
            nel_derp(322, _gettext('You are not allowed to modify bans.'));
        }

        nel_render_ban_panel_modify($inputs['board_id']);
    }
    else if ($inputs['action'] === 'new')
    {
        if (!$user->boardPerm($inputs['board_id'], 'perm_ban_add'))
        {
            nel_derp(321, _gettext('You are not allowed to add new bans.'));
        }

        $ip = (isset($_GET['ban_ip'])) ? $_GET['ban_ip'] : '';
        $type = (isset($_GET['ban_type'])) ? $_GET['ban_type'] : 'GENERAL';
        nel_render_ban_panel_add($inputs['board_id'], $ip, $type);
    }
    else if ($inputs['action'] === 'add')
    {
        if (!$user->boardPerm($inputs['board_id'], 'perm_ban_add'))
        {
            nel_derp(321, _gettext('You are not allowed to add new bans.'));
        }

        $ban_input = $ban_hammer->postToArray();
        $ban_hammer->addBan($ban_input);

        if (isset($_GET['post-id']))
        {
            if (isset($_POST['mod_post_comment']) && !empty($_POST['mod_post_comment']))
            {
                $post_table = nel_parameters_and_data()->boardReferences($inputs['board_id'], 'post_table');
                $prepared = $dbh->prepare(
                        'UPDATE "' . $post_table . '" SET "mod_comment" = ? WHERE "post_number" = ?');

                $dbh->executePrepared($prepared, array($_POST['mod_post_comment'], $_GET['post-id']));
                $regen = new \Nelliel\Regen();
                $regen->threads($inputs['board_id'], true, array($_GET['post-id']));
                $regen->index($inputs['board_id']);
            }
        }

        nel_render_main_ban_panel($inputs['board_id']);
    }
    else if ($inputs['action'] === 'remove')
    {
        if (!$user->boardPerm($inputs['board_id'], 'perm_ban_modify'))
        {
            nel_derp(323, _gettext('You are not allowed to delete bans.'));
        }

        $ban_input = $ban_hammer->postToArray();
        $ban_hammer->removeBan($inputs['board_id'], $_GET['ban_id']);
        nel_render_main_ban_panel($inputs['board_id']);
    }
    else if ($inputs['action'] === 'update')
    {
        if (!$user->boardPerm($inputs['board_id'], 'perm_ban_modify'))
        {
            nel_derp(322, _gettext('You are not allowed to modify bans.'));
        }

        $ban_input = $ban_hammer->postToArray();
        $ban_hammer->modifyBan($ban_input);
        nel_render_main_ban_panel($inputs['board_id']);
    }
    else
    {
        if (!$user->boardPerm($inputs['board_id'], 'perm_ban_access'))
        {
            nel_derp(320, _gettext('You are not allowed to access the bans panel.'));
        }

        nel_render_main_ban_panel($inputs['board_id']);
    }
}
