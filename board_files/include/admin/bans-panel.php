<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Update ban info
//
function nel_update_ban($dataforce)
{
    $dbh = nel_database();
    $mode = $dataforce['mode_action'];

    if ($mode === 'update')
    {
        $ban_input = array('days' => 0, 'hours' => 0, 'reason' => '', 'response' => '', 'review' => FALSE, 'status' => 0, 'length' => '');

        foreach ($_POST as $key => $val)
        {
            if ($key === 'timedays')
            {
                $ban_input['days'] = $val * 86400;
            }

            if ($key === 'timehours')
            {
                $ban_input['hours'] = $val * 3600;
            }

            if ($key === 'banreason')
            {
                $ban_input['reason'] = $val;
            }

            if ($key === 'appealresponse')
            {
                $ban_input['response'] = $val;
            }

            if ($key === 'appealreview')
            {
                $ban_input['review'] = TRUE;
            }

            if ($key === 'appealstatus')
            {
                $ban_input['status'] = $val;
            }

            if ($key === 'original')
            {
                $ban_input['length'] = $val;
            }
        }

        $bantotal = (int) $ban_input['days'] + (int) $ban_input['hours'];

        if ($ban_input['review'])
        {
            $ban_input['status'] = ((int) $ban_input['length'] !== $bantotal) ? 3 : 2;
        }

        $prepared = $dbh->prepare('UPDATE ' . BAN_TABLE . ' SET reason=:reason, length=:length, appeal_response=:response, appeal_status=:status WHERE id=:banid');
        $prepared->bindParam(':reason', $ban_input['reason'], PDO::PARAM_STR);
        $prepared->bindParam(':length', $bantotal, PDO::PARAM_INT);
        $prepared->bindParam(':response', $ban_input['response'], PDO::PARAM_STR);
        $prepared->bindParam(':status', $ban_input['status'], PDO::PARAM_INT);
        $prepared->bindParam(':banid', $dataforce['banid'], PDO::PARAM_INT);
        $prepared->execute();
        $prepared->closeCursor();
    }
}

//
// Ban control panel
//
function nel_ban_control($dataforce)
{
    $dbh = nel_database();
    $authorize = nel_get_authorization();
    $mode = $dataforce['mode'];

    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_ban_access'))
    {
        nel_derp(101, array('origin' => 'ADMIN'));
    }

    require_once INCLUDE_PATH . 'output/ban-panel-generation.php';

    if ($mode === 'modify')
    {
        nel_render_ban_panel_modify($dataforce);
    }
    else if ($mode === 'admin->ban->new')
    {
        nel_render_ban_panel_add($dataforce);
    }
    else if ($mode === 'admin->ban->add')
    {
        $dataforce['snacks'] = 'addban';
        nel_ban_hammer($dataforce);
        nel_render_main_ban_panel($dataforce);
    }
    else if ($mode === 'remove')
    {
         $dbh->query('DELETE FROM ' . BAN_TABLE . ' WHERE id=' . $dataforce['banid'] . '');
        nel_update_ban($dataforce, $authorize);
    }
    else if ($mode === 'update')
    {
        nel_update_ban($dataforce, $authorize);
    }
    else if ($mode === 'admin->ban->panel')
    {
        nel_render_main_ban_panel($dataforce);
    }
    else
    {
        ; // error here
    }
}
