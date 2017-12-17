<?php

function nel_collect_post_data()
{
    $post_data = array();
    $post_data['parent_thread'] = nel_check_post_entry($_POST['new_post']['post_info']['response_to'], "int");
    $post_data['name'] = nel_check_post_entry($_POST['new_post']['post_info']['not_anonymous'], "string");
    $post_data['email'] = nel_check_post_entry($_POST['new_post']['post_info']['spam_target'], "string");
    $post_data['subject'] = nel_check_post_entry($_POST['new_post']['post_info']['verb'], "string");
    $post_data['comment'] = nel_check_post_entry($_POST['new_post']['post_info']['wordswordswords'], "string");
    $post_data['fgsfds'] = nel_check_post_entry($_POST['new_post']['post_info']['fgsfds'], "string");
    $post_data['password'] = nel_check_post_entry($_POST['new_post']['post_info']['sekrit'], "string");

    if ($post_data['name'] !== '')
    {
        preg_match('/^([^#]*)(#(?!#))?([^#]*)(##)?(.*)$/', $post_data['name'], $name_pieces);
        $post_data['name'] = $name_pieces[1];
        $post_data = nel_get_tripcodes($post_data, $name_pieces);
        $post_data = nel_get_staff_post($post_data, $name_pieces);
    }

    if (BS_FORCE_ANONYMOUS || $post_data['name'] === '')
    {
        $post_data['name'] = nel_stext('THREAD_NONAME');
        $post_data['email'] = '';
    }

    return $post_data;
}

function nel_check_post_entry($post_item, $type)
{
    if ($type === "integer" || $type === "int")
    {
        if (!is_numeric($post_item))
        {
            return null;
        }
    }

    if ($type === "string" || $type === "str")
    {
        if ($post_item === '')
        {
            return null;
        }
    }

    settype($post_item, $type);
    return $post_item;
}

function nel_get_staff_post($post_data, $name_pieces)
{
    $authorize = nel_authorize();
    $post_data['modpost'] = null;

    if ($name_pieces[5] === '')
    {
        return $post_data;
    }

    $user = $authorize->get_tripcode_user($name_pieces[5]);

    if ($user === FALSE)
    {
        return $post_data;
    }

    $role = $authorize->get_user_info($user, 'role_id');

    if ($name_pieces[5] !== $authorize->get_user_info($user, 'user_tripcode') ||
         !$authorize->get_role_info($role, 'perm_post_default_name'))
    {
        return $post_data;
    }

    $post_data['modpost'] = $role;

    if (!$authorize->get_role_info($role, 'perm_post_custom_name'))
    {
        $post_data['name'] = $authorize->get_user_info($user, 'user_title');
    }

    return $post_data;
}

function nel_get_tripcodes($post_data, $name_pieces)
{
    global $plugins;

    $authorize = nel_authorize();
    $post_data['tripcode'] = '';
    $post_data['secure_tripcode'] = '';
    $post_data = $plugins->plugin_hook('in-before-tripcode-processing', TRUE, array($post_data, $name_pieces));

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

    if ($name_pieces[5] !== '')
    {
        $raw_trip = iconv('UTF-8', 'SHIFT_JIS//IGNORE', $name_pieces[5]);
        $trip = hash(SECURE_TRIPCODE_ALGORITHM, $raw_trip . TRIPCODE_SALT);
        $trip = base64_encode(pack("H*", $trip));
        $final_trip = substr($trip, -12);
        $post_data['secure_tripcode'] = iconv('SHIFT_JIS//IGNORE', 'UTF-8', $final_trip);
    }

    $post_data = $plugins->plugin_hook('in-after-tripcode-processing', TRUE, array($post_data, $name_pieces));
    return $post_data;
}
