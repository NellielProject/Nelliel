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
        $dataforce['banreason'] = "Spambot. Nobody wants any. GTFO";
        $dataforce['bandays'] = 9001;
        $dataforce['banip'] = $_SERVER["REMOTE_ADDR"];
        $dataforce['snacks'] = 'addban';
        nel_ban_hammer($dataforce);
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
            nel_derp(15, array('origin' => 'SNACKS', 'bad-filename' => $file['filename'] . $file['ext'], 'files' => array($file)));
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
            nel_derp(16, array('origin' => 'SNACKS'));
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
                nel_derp(17, array('origin' => 'SNACKS', 'cancer' => $cancer[$i]));
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
    $base_ip_address = $_SERVER["REMOTE_ADDR"];

    if ($dataforce['mode'] === 'banappeal')
    {
        reset($_POST);

        while ($item = each($_POST))
        {
            if ($item[0] === 'bawww')
            {
                $bawww = $item[1];
            }
            else if ($item[0] === 'banned_ip')
            {
                $banned_ip = $item[1];
            }
        }

        $prepared = $dbh->prepare('UPDATE ' . BAN_TABLE . ' SET appeal=:bawww, appeal_status=? WHERE ip_address=?');
        $prepared->bindParam(1, $bawww, PDO::PARAM_STR);
        $prepared->bindParam(2, $banned_ip, PDO::PARAM_STR);
        $prepared->execute();
        $prepared->closeCursor();
    }

    $prepared = $dbh->prepare('SELECT * FROM ' . BAN_TABLE . ' WHERE ip_address=?');
    $prepared->bindParam(1, $base_ip_address, PDO::PARAM_STR);
    $prepared->execute();
    $bandata = $prepared->fetch(PDO::FETCH_ASSOC);
    $prepared->closeCursor();

    $bandata['length_base'] = $bandata['length'] + $bandata['ban_time'];

    if (time() >= $bandata['length_base'])
    {
        $prepared = $dbh->prepare('DELETE FROM ' . BAN_TABLE . ' WHERE id=?');
        $prepared->bindParam(1, $bandata['id'], PDO::PARAM_INT);
        $prepared->execute();
        $prepared->closeCursor();
        return;
    }

    if (!empty($_SESSION))
    {
        nel_terminate_session();
    }

    nel_render_ban_page($dataforce, $bandata);
    die();
}