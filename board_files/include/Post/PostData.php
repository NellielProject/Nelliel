<?php

namespace Nelliel\Post;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use \Nelliel\Domain;

class PostData
{
    private $domain;
    private $authorization;

    function __construct(Domain $domain, $authorization)
    {
        $this->domain = $domain;
        $this->authorization = $authorization;
    }

    public function processPostData($post)
    {
        $post->content_data['parent_thread'] = $this->checkEntry($_POST['new_post']['post_info']['response_to'], 'integer');

        if($post->content_data['parent_thread'] != 0)
        {
            $post->content_id->thread_id = $post->content_data['parent_thread'];
        }

        $post->content_data['reply_to'] = $post->content_data['parent_thread']; // This may enable nested posts in the future
        $post->content_data['ip_address'] = inet_pton($_SERVER['REMOTE_ADDR']);
        $post->content_data['poster_name'] = $this->checkEntry($_POST['new_post']['post_info']['not_anonymous'], 'string');
        $post->content_data['email'] = $this->checkEntry($_POST['new_post']['post_info']['spam_target'], 'string');
        $post->content_data['subject'] = $this->checkEntry($_POST['new_post']['post_info']['verb'], 'string');
        $post->content_data['comment'] = $this->checkEntry($_POST['new_post']['post_info']['wordswordswords'], 'string');
        $post->content_data['fgsfds'] = $this->checkEntry($_POST['new_post']['post_info']['fgsfds'], 'string');
        $post->content_data['post_password'] = $this->checkEntry($_POST['new_post']['post_info']['sekrit'], 'string');
        $post->content_data['response_to'] = $this->checkEntry($_POST['new_post']['post_info']['response_to'], 'integer');
        $post->content_data['post_as_staff'] = (isset($_POST['post_as_staff'])) ? $this->checkEntry($_POST['post_as_staff'], 'boolean') : false;
        $post->content_data['mod_post_id'] = null;

        if ($post->content_data['poster_name'] !== '')
        {
            $this->tripcodes($post);
            $this->staffPost($post);
        }
        else
        {
            $post->content_data['poster_name'] = _gettext('Anonymous');
        }

        if ($this->domain->setting('force_anonymous'))
        {
            $post->content_data['poster_name'] = _gettext('Anonymous');
            $post->content_data['email'] = '';
        }

        $post = nel_plugins()->processHook('nel-post-data-processed', [$this->domain], $post);
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

    public function staffPost($post)
    {
        if(!$post->content_data['post_as_staff'])
        {
            return;
        }

        $session = new \Nelliel\Account\Session();

        if(!$session->isActive())
        {
            return;
        }

        $user = $session->sessionUser();

        if(!$user->checkPermission($this->domain, 'perm_board_post_as_staff'))
        {
            return;
        }

        $role = $user->checkRole($this->domain);

        if($role !== false)
        {
            $post->content_data['poster_name'] = $user->auth_data['display_name'];
            $post->content_data['mod_post_id'] = $role->auth_id;
        }
    }

    public function tripcodes($post)
    {
        $site_domain = new \Nelliel\DomainSite($this->domain->database());
        $post->content_data['poster_name'] = preg_replace("/#+$/", "", $post->content_data['poster_name']);
        preg_match('/^([^#]*)(?:#)?([^#]*)(?:##)?(.*)$/u', $post->content_data['poster_name'], $name_pieces);
        $post->content_data['poster_name'] = $name_pieces[1];
        $post->content_data['tripcode'] = '';
        $post->content_data['secure_tripcode'] = '';


        if ($name_pieces[2] !== '' && $this->domain->setting('allow_tripkeys'))
        {
            $trip = $this->tripcodeCharsetConvert($name_pieces[2], 'SHIFT_JIS', 'UTF-8');
            $salt = substr($trip . 'H.', 1, 2);
            $salt = preg_replace('#[^\.-z]#', '.', $salt);
            $salt = strtr($salt, ':;<=>?@[\\]^_`', 'ABCDEFGabcdef');
            $post->content_data['tripcode'] = substr(crypt($trip, $salt), -10);
        }

        if ($name_pieces[3] !== '' && $this->domain->setting('allow_tripkeys'))
        {
            $trip = $name_pieces[3];
            $trip = hash($site_domain->setting('secure_tripcode_algorithm'), $trip . TRIPCODE_PEPPER);
            $trip = base64_encode(pack("H*", $trip));
            $post->content_data['secure_tripcode'] = substr($trip, 2, 10);
        }

        $post = nel_plugins()->processHook('nel-post-tripcodes', [$this->domain, $name_pieces], $post);
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