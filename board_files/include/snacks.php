<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/ban_page.php';

//
// Auto-ban on Spambot detection
//
function nel_ban_spambots()
{
    $ban_hammer = new \Nelliel\BanHammer();

    if (!empty($_POST[nel_stext('TEXT_SPAMBOT_FIELD1')]) || !empty($_POST[nel_stext('TEXT_SPAMBOT_FIELD2')]))
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
function nel_file_hash_is_banned($file_hash, $hash_type)
{
    $banned_hashes = nel_get_file_filters();

    if (is_null($banned_hashes[$hash_type]))
    {
        return false;
    }

    return in_array($file_hash, $banned_hashes[$hash_type]);
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
            nel_derp(151, nel_stext('ERROR_151'), null, array('cancer' => $cancer[$i]));
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
                nel_derp(152, nel_stext('ERROR_152'), $board_id, array('cancer' => $cancer[$i]));
            }
        }
    }
}

function nel_ban_appeal($board_id)
{
    if ($_POST['ban_ip'] != $user_ip_address)
    {
        nel_derp(160, nel_stext('ERROR_160'), $board_id);
    }

    $ip_address = $_POST['ban_ip'];
    $bawww = $_POST['ban_appeal'];

    if (SQLTYPE === 'MYSQL')
    {
        nel_utf8_4byte_to_entities($bawww);
    }

    $prepared = $dbh->prepare('UPDATE "' . BAN_TABLE .
         '" SET "appeal" = ?, "appeal_status" = 1 WHERE "ip_address_starts" = ?');
    $dbh->executePrepared($prepared, array($bawww, @inet_pton($ip_address)));

    nel_apply_ban($board_id);
}

//
// Apply b&hammer
//
function nel_apply_ban($board_id)
{
    $dbh = nel_database();
    $ban_hammer = new \Nelliel\BanHammer();
    $user_ip_address = $_SERVER["REMOTE_ADDR"];
    $ban_info = $ban_hammer->getBanByIp($user_ip_address);
    $module = (isset($_GET['module'])) ? $_GET['module'] : null;
    $action = (isset($_POST['action'])) ? $_POST['action'] : null;

    if (empty($ban_info))
    {
        return;
    }

    if ($module === 'ban-page')
    {
        if ($action === 'add-appeal')
        {
            nel_ban_appeal($board_id);
        }
    }

    $length = $ban_info['length'] + $ban_info['start_time'];

    if (time() >= $length)
    {
        $ban_hammer->removeBan($ban_info['ban_id'], true);
        return;
    }

    nel_render_ban_page($board_id, $ban_info);
    nel_clean_exit();
}