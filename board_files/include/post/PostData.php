<?php

namespace Nelliel\post;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class PostData
{
    private $board_id;
    private $staff_post;

    function __construct($board_id, $staff_post = false)
    {
        $this->board_id = $board_id;
        $this->staff_post = $staff_post;
    }

    public function processPostData($post)
    {
        $board_settings = nel_parameters_and_data()->boardSettings($this->board_id);
        $post->content_data['parent_thread'] = $this->checkEntry($_POST['new_post']['post_info']['response_to'], 'integer');

        if($post->content_data['parent_thread'] != 0)
        {
            $post->content_id->thread_id = $post->content_data['parent_thread'];
        }

        $post->content_data['name'] = $this->checkEntry($_POST['new_post']['post_info']['not_anonymous'], 'string');
        $post->content_data['email'] = $this->checkEntry($_POST['new_post']['post_info']['spam_target'], 'string');
        $post->content_data['subject'] = $this->checkEntry($_POST['new_post']['post_info']['verb'], 'string');
        $post->content_data['comment'] = $this->checkEntry($_POST['new_post']['post_info']['wordswordswords'], 'string');
        $post->content_data['fgsfds'] = $this->checkEntry($_POST['new_post']['post_info']['fgsfds'], 'string');
        $post->content_data['post_password'] = $this->checkEntry($_POST['new_post']['post_info']['sekrit'], 'string');
        $post->content_data['response_to'] = $this->checkEntry($_POST['new_post']['post_info']['response_to'], 'integer');
        $post->content_data['post_as_staff'] = (isset($_POST['post_as_staff'])) ? $this->checkEntry($_POST['post_as_staff'], 'boolean') : false;
        $post->content_data['mod_post'] = null;

        //TODO: Update this for ContentPost once we switch over
        if ($post->content_data['name'] !== '')
        {
            // = $this->staffPost($post_data);
            //$post_data = $this->tripcodes($post_data);

        }
        else
        {
            $post->content_data['name'] = _gettext('Anonymous');
        }

        if ($board_settings['force_anonymous'])
        {
            $post->content_data['name'] = _gettext('Anonymous');
            $post->content_data['email'] = '';
        }

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

    public function staffPost($post_data)
    {
        $authorize = nel_authorize();
        nel_sessions()->initializeSession('modmode', 'new-post');

        if(empty($_SESSION) || $post_data['post_as_staff'] === false)
        {
            return $post_data;
        }

        $user = $authorize->getUser($_SESSION['username']);

        if($authorize->getUserPerm($user['user_id'], 'perm_post_modmode') === false)
        {
            return $post_data;
        }

        $role_id = $authorize->getUserBoardRole($user['user_id'], $this->board_id);

        if($role_id === false || $authorize->getRolePerm($role_id, 'perm_post_modmode') === false)
        {
            $role_id = $authorize->getUserBoardRole($user['user_id'], '');

            if($role_id === false || $authorize->getRolePerm($role_id, 'perm_post_modmode') === false)
            {
                return $post_data; // TODO: Do error instead
            }
        }

        $post_data['name'] = $user['display_name'];
        $post_data['mod_post'] = $role_id;
        return $post_data;
    }

    public function tripcodes($post_data)
    {
        $references = nel_parameters_and_data()->boardReferences($this->board_id);
        $board_settings = nel_parameters_and_data()->boardSettings($this->board_id);
        $authorize = nel_authorize();
        $post_data['name'] = preg_replace("/#+$/", "", $post_data['name']);
        preg_match('/^([^#]*)(?:#)?([^#]*)(?:##)?(.*)$/u', $post_data['name'], $name_pieces);
        $post_data['name'] = $name_pieces[1];
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