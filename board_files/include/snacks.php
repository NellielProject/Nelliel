<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/ban_page.php';

//
// Auto-ban on Spambot detection
//
function nel_ban_spambots($dataforce)
{
    $ban_hammer = nel_ban_hammer();

    if (BS_USE_SPAMBOT_TRAP && (!empty($_POST[nel_stext('TEXT_SPAMBOT_FIELD1')]) || !empty($_POST[nel_stext('TEXT_SPAMBOT_FIELD2')])))
    {
        $ban_input['type'] = 'SPAMBOT';
        $ban_input['ip_address_start'] = $_SERVER['REMOTE_ADDR'];
        $ban_input['reason'] = 'Ur a spambot. Nobody wants any. GTFO!';
        $ban_input['length'] = 86400 * 9001;
        $ban_hammer->addBan($ban_input);
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
            nel_derp(150, nel_stext('ERROR_150'), array('bad-filename' => $file['filename'] . $file['ext'], 'files' => array($file)));
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
            nel_derp(151, nel_stext('ERROR_151'), array('cancer' => $cancer[$i]));
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
                nel_derp(152, nel_stext('ERROR_152'), array('cancer' => $cancer[$i]));
            }
        }
    }
}

//
// Apply b&hammer
//
function nel_apply_ban($dataforce, $board_id)
{
    $dbh = nel_database();
    $ban_hammer = nel_ban_hammer();
    $user_ip_address = $_SERVER["REMOTE_ADDR"];

    if (isset($dataforce['mode_segments'][2]) && $dataforce['mode_segments'][2] === 'appeal')
    {
        if($_POST['ban_ip'] != $user_ip_address)
        {
            nel_derp(160, nel_stext('ERROR_160'));
        }

        $ip_address = $_POST['ban_ip'];
        $bawww = $_POST['ban_appeal'];
        $prepared = $dbh->prepare('UPDATE "' . BAN_TABLE . '" SET "appeal" = ?, "appeal_status" = 1 WHERE "ip_address_starts" = ?');
        $dbh->executePrepared($prepared, array($bawww, @inet_pton($ip_address)));
    }

    $ban_info = $ban_hammer->getBanByIp($user_ip_address);

    if(empty($ban_info))
    {
        return;
    }

    $length = $ban_info['length'] + $ban_info['start_time'];

    if (time() >= $length)
    {
        $ban_hammer->removeBan($board_id, $ban_info['ban_id'], true);
        return;
    }

    nel_render_ban_page($dataforce, $ban_info);
    nel_clean_exit($dataforce, true);
}