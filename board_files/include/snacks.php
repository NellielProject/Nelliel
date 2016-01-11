<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Auto-ban on Spambot detection
//
function nel_ban_spambots($dataforce, $dbh)
{
    if (BS1_USE_SPAMBOT_TRAP && (!is_null($dataforce['sp_field1']) || !is_null($dataforce['sp_field2'])))
    {
        $dataforce['banreason'] = "Spambot. Nobody wants any. GTFO";
        $dataforce['bandays'] = 9001;
        $dataforce['banip'] = $_SERVER["REMOTE_ADDR"];
        nel_ban_hammer($dataforce, $dbh);
    }
}

//
// Banned md5 hashes
//
function nel_banned_md5($md5, $file)
{
    $cancer = array('', '');
    $total_cancer = count($cancer);
    
    for ($i = 0; $i < $total_cancer; ++ $i)
    {
        if ($md5 === $cancer[$i])
        {
            nel_derp(15, array('origin' => 'SNACKS', 'bad-filename' => $file['basic_filename'] . $file['ext'], 'files' => array($file)));
        }
    }
}

//
// Banned poster names
//
function nel_banned_name($name, $file)
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
// General wordfilters
//
function nel_word_filters($text)
{
    $cancer = array('', '');
    $chemo = array('', '');
    $total_cancer = count($cancer);
    
    for ($i = 0; $i < $total_cancer; ++ $i)
    {
        $text = preg_replace('#' . $cancer[$i] . '#', $chemo[$i], $text);
    }
    return $text;
}

//
// Apply b&hammer
//
function nel_apply_ban($dataforce, $dbh)
{
    $base_host = $_SERVER["REMOTE_ADDR"];
    
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
        
        $prepared = $dbh->prepare('UPDATE ' . BANTABLE . ' SET appeal=:bawww, appeal_status=1 WHERE host=:host');
        $prepared->bindParam(':bawww', $bawww, PDO::PARAM_STR);
        $prepared->bindParam(':host', @inet_pton($banned_ip), PDO::PARAM_STR);
        $prepared->execute();
        unset($prepared);
    }
    
    $prepared = $dbh->prepare('SELECT * FROM ' . BANTABLE . ' WHERE host=:host');
    $prepared->bindParam(':host', @inet_pton($base_host), PDO::PARAM_STR);
    $prepared->execute();
    $bandata = $prepared->fetch(PDO::FETCH_ASSOC);
    unset($prepared);
    
    $bandata['length_base'] = $bandata['length'] + $bandata['ban_time'];
    
    if (time() >= $bandata['length_base'])
    {
        $prepared = $dbh->prepare('DELETE FROM ' . BANTABLE . ' WHERE id=:banid');
        $prepared->bindParam(':banid', $bandata['id'], PDO::PARAM_INT);
        $prepared->execute();
        unset($prepared);
        return;
    }
    else
    {
        if (!empty($_SESSION))
        {
            nel_terminate_session();
        }
        
        nel_render_ban_page($dataforce, $bandata);
        die();
    }
}