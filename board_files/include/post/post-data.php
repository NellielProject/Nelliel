<?php

function nel_collect_post_data()
{
    $post_data = array();
    $post_data['name'] = nel_check_post_entry($_POST['notanonymous'], "string");
    $post_data['email'] = nel_check_post_entry($_POST['spamtarget'], "string");
    $post_data['subject'] = nel_check_post_entry($_POST['verb'], "string");
    $post_data['comment'] = nel_check_post_entry($_POST['wordswordswords'], "string");
    $post_data['fgsfds'] = nel_check_post_entry($_POST['fgsfds'], "string");
    $post_data['pass'] = nel_check_post_entry($_POST['sekrit'], "string");

    BS_MAX_POST_FILES;

    if ($post_data['name'] !== '' && !BS_FORCE_ANONYMOUS)
    {
        $post_data = nel_check_mod_fake($post_data);
        preg_match('/^([^#]*)(#(?!#))?([^#]*)(##)?(.*)$/', $post_data['name'], $name_pieces);
        $post_data['name'] = $name_pieces[1];
        $post_data = nel_get_mod_post($post_data, $name_pieces);
        $post_data = nel_get_tripcodes($post_data, $name_pieces);
    }
    else
    {
        $post_data['name'] = nel_stext('THREAD_NONAME');
        $post_data['email'] = '';
    }

    return $post_data;
}

function nel_check_post_entry($post_item, $type)
{
    if (!empty($post_item))
    {
        if ($type === "integer" || $type === "int")
        {
            if (!is_numeric($post_item))
            {
                return null;
            }
        }
    }
    else
    {
        return null;
    }

    settype($post_item, $type);
    return $post_item;
}

function nel_check_mod_fake($post_data)
{
    nel_banned_name($post_data['name']);

    if (utf8_strpos($post_data['name'], nel_stext('THREAD_MODPOST')))
    {
        $post_data['name'] = nel_stext('FAKE_STAFF_ATTEMPT');
    }
    else if (utf8_strpos($post_data['name'], nel_stext('THREAD_ADMINPOST')))
    {
        $post_data['name'] = nel_stext('FAKE_STAFF_ATTEMPT');
    }
    else if (utf8_strpos($post_data['name'], nel_stext('THREAD_JANPOST')))
    {
        $post_data['name'] = nel_stext('FAKE_STAFF_ATTEMPT');
    }

    return $post_data;
}

function nel_get_mod_post($post_data, $name_pieces)
{
    $post_data['modpost'] = 0;

    if (!nel_session_ignored() && $name_pieces[5] === $_SESSION['settings']['staff_trip'])
    {
        if ($_SESSION['perms']['perm_post'])
        {
            if ($_SESSION['settings']['staff_type'] === 'admin')
            {
                $post_data['modpost'] = 3;
            }
            else if ($_SESSION['settings']['staff_type'] === 'moderator')
            {
                $post_data['modpost'] = 2;
            }
            else if ($_SESSION['settings']['staff_type'] === 'janitor')
            {
                $post_data['modpost'] = 1;
            }
        }

        if ($_SESSION['perms']['perm_sticky'] && utf8_strripos($dataforce['fgsfds'], 'sticky') !== FALSE)
        {
            $fgsfds['sticky'] = TRUE;
        }
    }

    return $post_data;
}

function nel_get_tripcodes($post_data, $name_pieces)
{
    global $plugins;
    $post_data['tripcode'] = '';
    $post_data['secure_tripcode'] = '';
    $post_data = $plugins->plugin_hook('pre-tripcode-processing', TRUE, array($post_data));

    if ($name_pieces[3] !== '' && BS_ALLOW_TRIPKEYS)
    {
        $raw_trip = iconv('UTF-8', 'SHIFT_JIS//IGNORE', $name_pieces[3]);
        $cap = strtr($raw_trip, '&amp;', '&');
        $cap = strtr($cap, '&#44;', ',');
        $salt = substr($cap . 'H.', 1, 2);
        $salt = preg_replace('#[^\.-z]#', '.#', $salt);
        $salt = strtr($salt, ':;<=>?@[\\]^_`', 'ABCDEFGabcdef');
        $final_trip = substr(crypt($cap, $salt), -10);
        $post_data['tripcode'] = iconv('SHIFT_JIS//IGNORE', 'UTF-8', $final_trip);
    }

    $post_data = $plugins->plugin_hook('tripcode-processing', TRUE, array($post_data, $name_pieces));

    if ($name_pieces[5] !== '' || $post_data['modpost'] > 0)
    {
        $raw_trip = iconv('UTF-8', 'SHIFT_JIS//IGNORE', $name_pieces[5]);
        $trip = nel_hash($raw_trip);
        $trip = base64_encode(pack("H*", $trip));
        $final_trip = substr($trip, -12);
        $post_data['secure_tripcode'] = iconv('SHIFT_JIS//IGNORE', 'UTF-8', $final_trip);
    }

    $post_data = $plugins->plugin_hook('secure-tripcode-processing', TRUE, array($post_data, $name_pieces));

    if ($name_pieces[1] === '' || (!empty($_SESSION) && $_SESSION['perms']['perm_post_anon']))
    {
        $post_data['name'] = nel_stext('THREAD_NONAME');
        $post_data['email'] = '';
    }

    $post_data = $plugins->plugin_hook('post-tripcode-processing', TRUE, array($post_data, $name_pieces));

    return $post_data;
}