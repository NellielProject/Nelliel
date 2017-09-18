<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Apply b&hammer
//
function nel_ban_hammer($dataforce)
{
    $dbh = nel_get_db_handle();
    $authorize = nel_get_authorization();

    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_ban_add'))
    {
        nel_derp(104, array('origin' => 'ADMIN'));
    }

    if ($dataforce['snacks'] === 'addban')
    {
        $prepared = $dbh->prepare('INSERT INTO ' . BAN_TABLE . ' (board,type,ip_address,poster_name,reason,length,ban_time)
								VALUES ("' . POST_TABLE . '",NULL,NULL,NULL,:reason,:length,' . time() . ')');
        $prepared->bindParam(':ip_address', @inet_pton($dataforce['banip']), PDO::PARAM_STR);
        $prepared->bindParam(':reason', $dataforce['banreason'], PDO::PARAM_STR);
        $prepared->bindParam(':length', (($dataforce['timedays'] * 86400) + ($dataforce['timehours'] * 3600)), PDO::PARAM_INT);
        $prepared->execute();
        $prepared->closeCursor();
        return;
    }

    reset($_POST);

    $manual = FALSE;
    $manual_host = '';
    $i = 0;
    $current_num = '';
    $ban_input = array();

    while ($item = each($_POST))
    {
        if ($item[0] === 'mode' && $item[1] === 'admin->ban->add')
        {
            $manual = TRUE;
            if ($i !== 0)
            {
                ++ $i;
            }
        }

        if ($item[0] === 'postban' . $item[1])
        {
            if ($i !== 0)
            {
                ++ $i;
            }

            $current_num = $item[1];
            $ban_input[$i] = array('num' => $item[1], 'days' => 0, 'hours' => 0, 'message' => '', 'reason' => '', 'name' => '', 'ip_address' => '');
        }

        if ($item[0] === 'timedays' . $current_num)
        {
            $ban_input[$i]['days'] = $item[1] * 86400;
        }

        if ($item[0] === 'timehours' . $current_num)
        {
            $ban_input[$i]['hours'] = $item[1] * 3600;
        }

        if ($item[0] === 'banmessage' . $current_num)
        {
            $ban_input[$i]['message'] = $item[1];
        }

        if ($item[0] === 'banreason' . $current_num)
        {
            $ban_input[$i]['reason'] = $item[1];
        }

        if ($item[0] === 'banname' . $current_num)
        {
            $ban_input[$i]['name'] = $item[1];
        }

        if ($item[0] === 'banhost' . $current_num)
        {
            $ban_input[$i]['ip_address'] = $item[1];
        }
    }

    $count_posts = count($ban_input);
    $i = 0;

    while ($i < $count_posts)
    {
        if (!$manual)
        {
            $prepared = $dbh->prepare('SELECT ip_address,mod_comment FROM ' . POST_TABLE . ' WHERE post_number=?');
            $prepared->bindParam(1, $ban_input[$i]['num'], PDO::PARAM_INT);
            $prepared->execute();
            $baninfo1 = $prepared->fetch(PDO::FETCH_ASSOC);
            $prepared->closeCursor();

            if (!empty($baninfo1))
            {
                $prepared = $dbh->prepare('SELECT * FROM ' . BAN_TABLE . ' WHERE ip_address=?');
                $prepared->bindParam(1, @inet_ntop($ban_input[$i]['ip_address']), PDO::PARAM_STR);
                $prepared->execute();
                $baninfo2 = $prepared->fetch(PDO::FETCH_ASSOC);
                $prepared->closeCursor();

                if ($baninfo2['id'] && $baninfo2['board'] === TABLEPREFIX)
                {
                    $prepared = $dbh->prepare('DELETE FROM ' . BAN_TABLE . ' WHERE id=?');
                    $prepared->bindParam(1, $baninfo2['id'], PDO::PARAM_INT);
                    $prepared->execute();
                    $prepared->closeCursor();
                }
            }

            // Append mod ban message to post if it was given
            if ($ban_input[$i]['message'] !== '')
            {
                $mod_comment = $baninfo1['mod_comment'] . '<br>(' . $ban_input[$i]['message'] . ')';
                $prepared = $dbh->prepare('UPDATE ' . POST_TABLE . ' SET mod_comment=? WHERE post_number=?');
                $prepared->bindParam(1, $mod_comment, PDO::PARAM_STR);
                $prepared->bindParam(2, $ban_input[$i]['num'], PDO::PARAM_INT);
                $prepared->execute();
                $prepared->closeCursor();
            }
        }

        $banlength = $ban_input[$i]['days'] + $ban_input[$i]['hours'];
        $prepared = $dbh->prepare('INSERT INTO ' . BAN_TABLE . ' (type,ip_address,poster_name,reason,length,ban_time) //same
									VALUES (NULL,:ip_address,:poster_name,:reason,:length,:time)');
        $prepared->bindParam(':ip_address', @inet_pton($ban_input[$i]['ip_address']), PDO::PARAM_STR);

        if ($manual)
        {
            $prepared->bindParam(':poster_name', NULL, PDO::PARAM_NULL);
        }
        else
        {
            $prepared->bindParam(':poster_name', $ban_input[$i]['name'], PDO::PARAM_STR);
        }

        $prepared->bindParam(':reason', $ban_input[$i]['reason'], PDO::PARAM_STR);
        $prepared->bindParam(':length', $banlength, PDO::PARAM_INT);
        $prepared->bindParam(':time', time(), PDO::PARAM_INT);
        $prepared->execute();
        $prepared->closeCursor();
        ++ $i;
    }
}
