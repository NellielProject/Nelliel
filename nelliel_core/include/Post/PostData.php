<?php

namespace Nelliel\Post;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Cites;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Content\ContentPost;
use Nelliel\Domains\Domain;

class PostData
{
    private $domain;
    private $authorization;
    private $session;

    function __construct(Domain $domain, Authorization $authorization, Session $session)
    {
        $this->domain = $domain;
        $this->authorization = $authorization;
        $this->session = $session;
    }

    public function processPostData(ContentPost $post)
    {
        if (!isset($_POST['new_post']))
        {
            nel_derp(35,
                    "No POST data was received. The request may have been too big or server settings need to be adjusted.");
        }

        $post->changeData('parent_thread', $this->checkEntry($_POST['new_post']['post_info']['response_to'], 'integer'));
        $post->contentID()->changeThreadID($post->data('parent_thread'));
        $post->changeData('reply_to', $post->data('parent_thread')); // This may enable nested posts in the future
        $post->changeData('ip_address', nel_request_ip_address());
        $post->changeData('hashed_ip_address', nel_request_ip_address(true));
        $poster_name = $this->checkEntry($_POST['new_post']['post_info']['not_anonymous'], 'string');
        $post->changeData('poster_name', $this->fieldMaxCheck('poster_name', $poster_name));
        $email = $this->checkEntry($_POST['new_post']['post_info']['spam_target'], 'string');
        $post->changeData('email', $this->fieldMaxCheck('email', $email));
        $subject = $this->checkEntry($_POST['new_post']['post_info']['verb'], 'string');
        $post->changeData('subject', $this->fieldMaxCheck('subject', $subject));
        $comment = $this->checkEntry($_POST['new_post']['post_info']['wordswordswords'], 'string');
        $post->changeData('comment', $this->fieldMaxCheck('comment', $comment));
        $post->changeData('fgsfds', $this->checkEntry($_POST['new_post']['post_info']['fgsfds'], 'string'));
        $post->changeData('post_password', $this->checkEntry($_POST['new_post']['post_info']['sekrit'], 'string'));
        $post->changeData('response_to', $this->checkEntry($_POST['new_post']['post_info']['response_to'], 'integer'));
        $post->changeData('post_as_staff',
                (isset($_POST['post_as_staff'])) ? $this->checkEntry($_POST['post_as_staff'], 'boolean') : false);

        if (!$post->data('post_as_staff'))
        {
            $this->session->ignore(true);
        }

        $response_to = $post->data('response_to') > 0;

        if (!$response_to)
        {
            if (nel_true_empty($post->data('comment')) && $this->domain->setting('require_op_comment'))
            {
                nel_derp(41, _gettext('A comment is required when starting a thread.'));
            }
        }
        else
        {
            if (nel_true_empty($post->data('comment')) && $this->domain->setting('require_reply_comment'))
            {
                nel_derp(42, _gettext('A comment is required when replying.'));
            }
        }

        $this->staffPost($post);

        if ($post->data('poster_name') !== '')
        {
            $this->tripcodes($post);
        }
        else
        {
            $post->changeData('poster_name', _gettext('Anonymous'));
        }

        if ($this->domain->setting('forced_anonymous'))
        {
            $post->changeData('poster_name', _gettext('Anonymous'));
            $post->changeData('email', '');
        }

        if(!nel_true_empty($post->data('comment')))
        {
            $cites = new Cites($this->domain->database());
            $cite_list = $cites->getCitesFromText($post->data('comment'), false);

            if (count($cite_list['board']) > $this->domain->setting('max_cites'))
            {
                nel_derp(44,
                        sprintf(_gettext('Comment contains too many cites. Maximum is %d.'),
                                $this->domain->setting('max_cites')));
            }

            if (count($cite_list['crossboard']) > $this->domain->setting('max_crossboard_cites'))
            {
                nel_derp(45,
                        sprintf(_gettext('Comment contains too many cross-board cites. Maximum is %d.'),
                                $this->domain->setting('max_crossboard_cites')));
            }
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

    public function staffPost($post)
    {
        if (!$post->data('post_as_staff'))
        {
            return;
        }

        $this->session->init(true);

        if (!$this->session->isActive())
        {
            return;
        }

        $user = $this->session->user();

        if (!$user->checkPermission($this->domain, 'perm_board_post_as_staff'))
        {
            return;
        }

        $role = $user->checkRole($this->domain);

        if ($role !== false)
        {
            $post->changeData('poster_name', $user->auth_data['display_name']);
            $post->changeData('mod_post_id', $role->id());
        }
    }

    public function tripcodes($post)
    {
        $site_domain = new \Nelliel\Domains\DomainSite($this->domain->database());
        $name_pieces = array();
        $post->changeData('poster_name', preg_replace("/#+$/", "", $post->data('poster_name')));
        preg_match('/^([^#]*)(?:#)?([^#]*)(?:##)?(.*)$/u', $post->data('poster_name'), $name_pieces);
        $post->changeData('poster_name', $name_pieces[1]);
        $post->changeData('tripcode', '');
        $post->changeData('secure_tripcode', '');

        if ($name_pieces[2] !== '' && $this->domain->setting('allow_tripcodes'))
        {
            $trip = $this->tripcodeCharsetConvert($name_pieces[2], 'SHIFT_JIS', 'UTF-8');
            $salt = substr($trip . 'H.', 1, 2);
            $salt = preg_replace('#[^\.-z]#', '.', $salt);
            $salt = strtr($salt, ':;<=>?@[\\]^_`', 'ABCDEFGabcdef');
            $post->changeData('tripcode', substr(crypt($trip, $salt), -10));
        }

        if ($name_pieces[3] !== '' && $this->domain->setting('allow_tripcodes'))
        {
            $trip = $name_pieces[3];
            $trip = hash($site_domain->setting('secure_tripcode_algorithm'), $trip . NEL_TRIPCODE_PEPPER);
            $trip = base64_encode(pack("H*", $trip));
            $post->changeData('secure_tripcode', substr($trip, 2, 10));
        }

        $post = nel_plugins()->processHook('nel-post-tripcodes', [$this->domain, $name_pieces], $post);
    }

    public function tripcodeCharsetConvert($text, $to, $from)
    {
        if (function_exists('iconv'))
        {
            return iconv($from, $to . '//IGNORE', $text);
        }
        else if (function_exists('mb_convert_encoding'))
        {
            return mb_convert_encoding($text, $to, $from);
        }
        else
        {
            return $text;
        }
    }

    public function fieldMaxCheck(string $field_name, ?string $text)
    {
        if (is_null($text))
        {
            return $text;
        }

        switch ($field_name)
        {
            case 'poster_name':
                if (utf8_strlen($text) > $this->domain->setting('max_name_length'))
                {
                    if ($this->domain_setting('truncate_long_fields'))
                    {
                        $text = utf8_substr($text, 0, $this->domain->setting('max_name_length'));
                    }
                    else
                    {
                        nel_derp(30, _gettext('Name is too long.'));
                    }
                }
                break;

            case 'email':
                if (utf8_strlen($text) > $this->domain->setting('max_email_length'))
                {
                    if ($this->domain_setting('truncate_long_fields'))
                    {
                        $text = utf8_substr($text, 0, $this->domain->setting('max_email_length'));
                    }
                    else
                    {
                        nel_derp(31, _gettext('Email is too long.'));
                    }
                }

                break;

            case 'subject':
                if (utf8_strlen($text) > $this->domain->setting('max_subject_length'))
                {
                    if ($this->domain_setting('truncate_long_fields'))
                    {
                        $text = utf8_substr($text, 0, $this->domain->setting('max_subject_length'));
                    }
                    else
                    {
                        nel_derp(32, _gettext('Subject is too long.'));
                    }
                }

                break;

            case 'comment':
                if (utf8_strlen($text) > $this->domain->setting('max_comment_length'))
                {
                    if ($this->domain_setting('truncate_long_fields'))
                    {
                        $text = utf8_substr($text, 0, $this->domain->setting('max_comment_length'));
                    }
                    else
                    {
                        nel_derp(33, _gettext('Comment is too long.'));
                    }
                }

                break;
        }

        return $text;
    }
}