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

    if (!empty($_POST[_gettext('TEXT_SPAMBOT_FIELD1')]) || !empty($_POST[_gettext('TEXT_SPAMBOT_FIELD2')]))
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
    $banned_hashes = nel_parameters_and_data()->fileFilters();

    if (!isset($banned_hashes[$hash_type]))
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
            nel_derp(151, _gettext('That name is banned.'), array('cancer' => $cancer[$i]));
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
                nel_derp(152, _gettext('Cancer detected in post: '), array('cancer' => $cancer[$i]));
            }
        }
    }
}

function nel_ban_appeal($board_id, $ban_info)
{
    $dbh = nel_database();

    if ($_POST['ban_ip'] != @inet_ntop($ban_info['ip_address_start']))
    {
        nel_derp(160, _gettext('Your ip address does not match the one listed in the ban.'));
    }

    if ($ban_info['appeal_status'] > 0)
    {
        nel_derp(161, _gettext('You have already appealed your ban.'));
    }

    $bawww = $_POST['ban_appeal'];
    $prepared = $dbh->prepare('UPDATE "' . BAN_TABLE .
         '" SET "appeal" = ?, "appeal_status" = 1 WHERE "ban_id" = ?');
    $dbh->executePrepared($prepared, array($bawww, $ban_info['ban_id']));
    return;
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
            nel_ban_appeal($board_id, $ban_info);
            $ban_info = $ban_hammer->getBanById($ban_info['ban_id']);
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