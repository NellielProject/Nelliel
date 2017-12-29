<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/ban_panel.php';

//
// Ban control panel
//
function nel_ban_control($dataforce)
{
    $dbh = nel_database();
    $authorize = nel_authorize();
    $ban_hammer = nel_ban_hammer();
    $mode = $dataforce['mode'];

    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_ban_access'))
    {
        nel_derp(320, nel_stext('ERROR_320'));
    }

    if ($mode === 'admin->ban->modify')
    {
        nel_render_ban_panel_modify($dataforce);
    }
    else if ($mode === 'admin->ban->new')
    {
        nel_render_ban_panel_add($dataforce);
    }
    else if ($mode === 'admin->ban->add')
    {
        $ban_input = $ban_hammer->postToArray();
        $ban_hammer->addBan($ban_input);
        nel_render_main_ban_panel($dataforce);
    }
    else if ($mode === 'admin->ban->remove')
    {
        $ban_input = $ban_hammer->postToArray();
        $ban_hammer->removeBan($ban_input['ban_id']);
        nel_render_main_ban_panel($dataforce);
    }
    else if ($mode === 'admin->ban->update')
    {
        $ban_input = $ban_hammer->postToArray();
        $ban_hammer->modifyBan($ban_input);
        nel_render_main_ban_panel($dataforce);
    }
    else if ($mode === 'admin->ban->panel')
    {
        nel_render_main_ban_panel($dataforce);
    }
    else
    {
        ; //TODO: Error or something
    }

}
