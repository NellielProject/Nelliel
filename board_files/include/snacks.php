<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Auto-ban on Spambot detection
//
function nel_ban_spambots($dataforce)
{
    if (BS_USE_SPAMBOT_TRAP && (!is_null($dataforce['sp_field1']) || !is_null($dataforce['sp_field2'])))
    {
        $ban_info['type'] = 'SPAMBOT';
        $ban_info['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $ban_info['reason'] = 'Ur a spambot. Nobody wants any. GTFO!';
        $ban_info['length'] = 86400 * 9001;
        nel_ban_hammer()->addBan($ban_input);
    }
}

//
// Banned hashes
//
function nel_banned_hash($hash, $file)
{
    $cancer = array('', '');
    $total_cancer = count($cancer);

    for ($i = 0; $i < $total_cancer; ++ $i)
    {
        if ($hash === $cancer[$i])
        {
            nel_derp(15, nel_stext('ERROR_15'), array('bad-filename' => $file['filename'] . $file['ext'], 'files' => array($file)));
        }
    }
}

//
// Banned poster names
//
function nel_banned_name($name)
{
    $cancer = array('', '');
    $total_cancer = count($cancer);

    for ($i = 0; $i < $total_cancer; ++ $i)
    {
        if ($cancer[$i] === $name)
        {
            nel_derp(16, nel_stext('ERROR_16'), array('cancer' => $cancer[$i]));
        }
    }
}

//
// Banned text in comments
//
function nel_banned_text($text, $file)
{
    $cancer = array('samefag', '');
    $total_cancer = count($cancer);

    for ($i = 0; $i < $total_cancer; ++ $i)
    {
        if ($cancer[$i] !== '')
        {
            $test = utf8_strpos($text, $cancer[$i]);

            if ($test !== FALSE)
            {
                nel_derp(17, nel_stext('ERROR_17'), array('cancer' => $cancer[$i]));
            }
        }
    }
}

//
// Apply b&hammer
//
function nel_apply_ban($dataforce)
{
    $dbh = nel_database();
    $ban_hammer = nel_ban_hammer();
    $user_ip_address = $_SERVER["REMOTE_ADDR"];

    if ($dataforce['mode'] === 'ban_appeal')
    {
        if($_POST['ban_ip'] != $user_ip_address)
        {
            nel_derp(0, nel_stext('ERROR_0')); // TODO: Make a hax error here
        }

        $ip_address = $_POST['ban_ip'];
        $bawww = $_POST['ban_appeal'];
        $prepared = $dbh->prepare('UPDATE "' . BAN_TABLE . '" SET "appeal" = ?, "appeal_status" = 1 WHERE "ip_address" = ?');
        $dbh->executePrepared($prepared, array($bawww, $ip_address));
    }

    $ban_info = $ban_hammer->getBanByIp($user_ip_address);

    if(empty($ban_info))
    {
        return;
    }

    $length = $ban_info['length'] + $ban_info['start_time'];

    if (time() >= $length)
    {
        $ban_hammer->removeBan($ban_info['ban_id'], true);
        return;
    }

    nel_render_ban_page($dataforce, $ban_info);
    nel_clean_exit($dataforce, true);
}