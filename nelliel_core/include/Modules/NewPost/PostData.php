<?php
declare(strict_types = 1);

namespace Nelliel\Modules\NewPost;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Cites;
use Nelliel\Modules\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Content\ContentPost;
use Nelliel\Domains\Domain;
use Nelliel\Wordfilters;

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
        $name = $this->checkEntry($_POST['new_post']['post_info']['not_anonymous'], 'string');

        if (nel_true_empty($name) || $this->domain->setting('forced_anonymous'))
        {
            $name_choices = json_decode($this->domain->setting('anonymous_names'), true);

            if (!is_null($name_choices))
            {
                $name = $name_choices[mt_rand(0, count($name_choices) - 1)];
            }
            else
            {
                $name = 'Anonymous';
            }
        }

        $name_text = $this->fieldMaxCheck('name', $name);
        $post->changeData('name', $this->posterName($name_text));

        if ($this->domain->setting('allow_tripcodes'))
        {
            $post->changeData('tripcode', $this->tripcode($name_text));
            $post->changeData('secure_tripcode', $this->secureTripcode($name_text));
        }

        if ($this->domain->setting('forced_anonymous'))
        {
            $email = '';
        }
        else
        {
            $email = $this->checkEntry($_POST['new_post']['post_info']['spam_target'], 'string');
        }

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

        $this->staffPost($post, $name_text);

        if (!nel_true_empty($post->data('comment')))
        {
            $wordfilters = new Wordfilters($this->domain->database());
            $post->changeData('comment', $wordfilters->apply($post->data('comment'), $this->domain));
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

            $url_protocols = $this->domain->setting('url_protocols');
            $url_split_regex = '#(' . $url_protocols . ')(:\/\/)#';

            if (preg_match_all($url_split_regex, $post->data('comment')) > $this->domain->setting('max_comment_urls'))
            {
                nel_derp(46,
                        sprintf(_gettext('Comment contains too many URLs. Maximum is %d.'),
                                $this->domain->setting('max_comment_urls')));
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

    public function staffPost(ContentPost $post, string $name_text): void
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

        if (!$user->checkPermission($this->domain, 'perm_post_as_staff'))
        {
            return;
        }

        $post->changeData('capcode', $this->capcode($name_text));
        $post->changeData('name', $user->getData('display_name'));
        $post->changeData('account_id', $user->id());
    }

    public function posterName(string $text): string
    {
        $matches = array();
        $name = '';

        if (preg_match('/([^#]*)/u', $text, $matches) === 1)
        {
            $name = $matches[1];
        }

        return $name;
    }

    public function tripcode(string $text): string
    {
        $matches = array();
        $tripcode = '';

        if (preg_match('/#((?:(?!##| ## ).)*)/u', $text, $matches) === 1)
        {
            $trip_key = $this->tripcodeCharsetConvert($matches[1], 'SHIFT_JIS', 'UTF-8');
            $salt = substr($trip_key . 'H.', 1, 2);
            $salt = preg_replace('#[^\.-z]#', '.', $salt);
            $salt = strtr($salt, ':;<=>?@[\\]^_`', 'ABCDEFGabcdef');
            $tripcode = substr(crypt($trip_key, $salt), -10);
        }

        return $tripcode;
    }

    public function secureTripcode(string $text): string
    {
        $matches = array();
        $secure_tripcode = '';

        if (preg_match('/##((?:(?! ## ).)*)/u', $text, $matches) === 1)
        {
            $trip_key = $matches[1];
            $trip_code = hash(nel_site_domain()->setting('secure_tripcode_algorithm'), $trip_key . NEL_TRIPCODE_PEPPER);
            $trip_code = base64_encode(pack("H*", $trip_code));
            $secure_tripcode = substr($trip_code, 2, 10);
        }

        return $secure_tripcode;
    }

    public function capcode(string $text): string
    {
        $capcode = '';
        $matches = array();

        if ($this->session->user()->checkPermission($this->domain, 'perm_custom_capcode') &&
                preg_match('/ ## (.*)/u', $text, $matches) === 1)
        {
            $capcode = $matches[1];
        }
        else
        {
            if ($this->session->user()->isSiteOwner())
            {
                $capcode = 'Site Owner';
            }
            else
            {
                $role = $this->session->user()->getDomainRole($this->domain);
                $capcode = $role->getData('capcode');
            }
        }

        return $capcode;
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
            case 'name':
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