<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class PostData
{
    private $board_id;

    function __construct($board_id = '')
    {
        $this->board_id = $board_id;
    }

    public function collectData()
    {
        $board_settings = nel_parameters_and_data()->boardSettings($this->board_id);
        $post_data = array();
        $post_data['parent_thread'] = $this->checkEntry($_POST['new_post']['post_info']['response_to'], "int");
        $post_data['name'] = $this->checkEntry($_POST['new_post']['post_info']['not_anonymous'], "string");
        $post_data['email'] = $this->checkEntry($_POST['new_post']['post_info']['spam_target'], "string");
        $post_data['subject'] = $this->checkEntry($_POST['new_post']['post_info']['verb'], "string");
        $post_data['comment'] = $this->checkEntry($_POST['new_post']['post_info']['wordswordswords'], "string");
        $post_data['fgsfds'] = $this->checkEntry($_POST['new_post']['post_info']['fgsfds'], "string");
        $post_data['password'] = $this->checkEntry($_POST['new_post']['post_info']['sekrit'], "string");
        $post_data['response_to'] = $this->checkEntry($_POST['new_post']['post_info']['response_to'], "int");

        if ($post_data['name'] !== '')
        {
            $post_data['name'] = preg_replace("/#+$/", "", $post_data['name']);
            preg_match('/^([^#]*)(?:#)?([^#]*)(?:##)?(.*)$/u', $post_data['name'], $name_pieces);
            $post_data['name'] = $name_pieces[1];
            $post_data = $this->tripcodes($post_data, $name_pieces);
            $post_data = $this->staffPost($post_data, $name_pieces);
        }
        else
        {
            $post_data['name'] = _gettext('Anonymous');
        }

        if ($board_settings['force_anonymous'])
        {
            $post_data['name'] = _gettext('Anonymous');
            $post_data['email'] = '';
        }

        return $post_data;
    }

    public function checkEntry($post_item, $type)
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

    public function staffPost($post_data, $name_pieces)
    {
        $authorize = nel_authorize();
        $post_data['modpost'] = null;

        if ($name_pieces[3] === '')
        {
            return $post_data;
        }

        $user = $authorize->get_tripcode_user($name_pieces[3]);

        if ($user === FALSE)
        {
            return $post_data;
        }

        $role = $authorize->get_user_info($user, 'role_id');

        if ($name_pieces[3] !== $authorize->get_user_info($user, 'user_secure_tripcode') ||
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

    public function tripcodes($post_data, $name_pieces)
    {
        $references = nel_parameters_and_data()->boardReferences($this->board_id);
        $board_settings = nel_parameters_and_data()->boardSettings($this->board_id);
        $authorize = nel_authorize();
        $post_data['tripcode'] = '';
        $post_data['secure_tripcode'] = '';

        if ($name_pieces[2] !== '' && $board_settings['allow_tripkeys'])
        {
            $trip = $this->tripcodeCharsetConvert($name_pieces[2], 'SHIFT_JIS', 'UTF-8');
            $salt = substr($trip . 'H.', 1, 2);
            $salt = preg_replace('#[^\.-z]#', '.', $salt);
            $salt = strtr($salt, ':;<=>?@[\\]^_`', 'ABCDEFGabcdef');
            $post_data['tripcode'] = substr(crypt($trip, $salt), -10);
        }

        if ($name_pieces[3] !== '' && $board_settings['allow_tripkeys'])
        {
            $trip = $name_pieces[3];
            $trip = hash(nel_parameters_and_data()->siteSettings('secure_tripcode_algorithm'), $trip . TRIPCODE_SALT);
            $trip = base64_encode(pack("H*", $trip));
            $post_data['secure_tripcode'] = substr($trip, 2, 10);
        }

        return $post_data;
    }

    public function tripcodeCharsetConvert($text, $to, $from)
    {
        if(function_exists('iconv'))
        {
            return iconv($from, $to . '//IGNORE', $text);
        }
        else if(function_exists('mb_convert_encoding'))
        {
            return mb_convert_encoding($text, $to, $from);
        }
        else
        {
            return $text;
        }
    }
}