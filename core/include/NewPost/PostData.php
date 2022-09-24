<?php
declare(strict_types = 1);

namespace Nelliel\NewPost;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Cites;
use Nelliel\Wordfilters;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Content\Post;
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

    public function processPostData(Post $post)
    {
        if (!isset($_POST['new_post'])) {
            nel_derp(40,
                "No POST data was received. The request may have been too big or server settings need to be adjusted.");
        }

        $post->changeData('parent_thread', $this->checkEntry($_POST['new_post']['post_info']['response_to'], 'integer'));
        $post->contentID()->changeThreadID($post->data('parent_thread'));
        $post->changeData('op', $post->data('parent_thread') === 0);
        $post->changeData('reply_to', $post->data('parent_thread')); // This may enable nested posts in the future
        $post->changeData('ip_address', nel_request_ip_address());
        $post->changeData('hashed_ip_address', nel_request_ip_address(true));
        $post->changeData('visitor_id', nel_visitor_id(), false);

        $name = $this->checkEntry($_POST['new_post']['post_info']['not_anonymous'] ?? '', 'string');
        $name = $this->fieldLengthCheck('name', $name);
        $staff_post = $this->staffPost();

        $enable_name = $post->data('op') ? $this->domain->setting('enable_op_name_field') : $this->domain->setting(
            'enable_reply_name_field');
        $enable_email = $post->data('op') ? $this->domain->setting('enable_op_email_field') : $this->domain->setting(
            'enable_reply_email_field');
        $enable_subject = $post->data('op') ? $this->domain->setting('enable_op_subject_field') : $this->domain->setting(
            'enable_reply_subject_field');
        $enable_comment = $post->data('op') ? $this->domain->setting('enable_op_comment_field') : $this->domain->setting(
            'enable_reply_comment_field');
        $require_name = $post->data('op') ? $this->domain->setting('require_op_name') : $this->domain->setting(
            'require_reply_name');
        $require_email = $post->data('op') ? $this->domain->setting('require_op_email') : $this->domain->setting(
            'require_reply_email');
        $require_subject = $post->data('op') ? $this->domain->setting('require_op_subject') : $this->domain->setting(
            'require_reply_subject');
        $require_comment = $post->data('op') ? $this->domain->setting('require_op_comment') : $this->domain->setting(
            'require_reply_comment');

        if (nel_true_empty($name) || !$enable_name || $this->domain->setting('forced_anonymous')) {
            $name_choices = json_decode($this->domain->setting('anonymous_names'), true);

            if ($this->domain->setting('use_anonymous_names') && !is_null($name_choices)) {
                $name = $name_choices[mt_rand(0, count($name_choices) - 1)];
            } else {
                $name = null;
            }
        } else {
            $matches = array();
            $trip_string = '';
            $type = '';

            if (preg_match('/([^#]+)?(##|#)(.+)/', $name, $matches) === 1) {
                $name = $matches[1];
                $type = $matches[2];
                $trip_string = $matches[3];
            }

            if (preg_match('/(.+)? ## (.+)/', $trip_string, $matches) === 1) {
                $trip_string = $matches[1];
                $capcode_string = $matches[2];

                if ($staff_post) {
                    $post->changeData('capcode', $this->capcode($capcode_string));
                }
            }

            if ($this->domain->setting('allow_tripcodes')) {
                if ($type === '##') {
                    $post->changeData('secure_tripcode', $this->secureTripcode($trip_string));
                }

                if ($type === '#') {
                    $post->changeData('tripcode', $this->tripcode($trip_string));
                }
            }

            if ($this->domain->setting('require_tripcode') && nel_true_empty($post->data('tripcode')) &&
                nel_true_empty($post->data('secure_tripcode'))) {
                nel_derp(41, _gettext('A tripcode or secure tripcode is required to post.'));
            }
        }

        $raw_html = $this->checkEntry($_POST['raw_html'] ?? false, 'boolean');

        if ($raw_html && $this->session->user()->checkPermission($this->domain, 'perm_raw_html')) {
            $post->getMoar()->modify('raw_html', true);
        }

        $disable_markdown = $this->checkEntry($_POST['no_markdown'] ?? false, 'boolean');

        if ($disable_markdown) {
            $post->getMoar()->modify('no_markdown', true);
        }

        if ($staff_post) {
            $this->session->ignore(true);
            $user = $this->session->user();

            if (!$user->checkPermission($this->domain, 'perm_custom_name')) {
                $name = $user->id();
            }

            $post->changeData('username', $user->id());
        }

        $post->changeData('name', $name);

        if (nel_true_empty($post->data('name')) && $require_name) {
            nel_derp(41, _gettext('A name is required to post.'));
        }

        if ($enable_email && !$this->domain->setting('forced_anonymous')) {
            $email = $this->checkEntry($_POST['new_post']['post_info']['spam_target'] ?? '', 'string');
            $post->changeData('email', $this->fieldLengthCheck('email', $email));
        }

        if (nel_true_empty($post->data('email')) && $require_email) {
            nel_derp(42, _gettext('An email is required to post.'));
        }

        if ($enable_subject) {
            $subject = $this->checkEntry($_POST['new_post']['post_info']['verb'] ?? '', 'string');
            $post->changeData('subject', $this->fieldLengthCheck('subject', $subject));
        }

        if (nel_true_empty($post->data('subject')) && $require_subject) {
            nel_derp(43, _gettext('A subject is required to post.'));
        }

        if ($enable_comment) {
            $original_comment = $_POST['new_post']['post_info']['wordswordswords'] ?? '';
            $comment = $this->checkEntry($original_comment, 'string');
            $post->changeData('original_comment', $comment);
            $post->changeData('comment', $this->fieldLengthCheck('comment', $comment));
        }

        if (nel_true_empty($post->data('comment')) && $require_comment) {
            nel_derp(44, _gettext('A comment is required to post.'));
        }

        if ($this->domain->setting('enable_fgsfds_field')) {
            $post->changeData('fgsfds', $this->checkEntry($_POST['new_post']['post_info']['fgsfds'] ?? '', 'string'));
        }

        if ($this->domain->setting('enable_password_field')) {
            $post->changeData('password', $this->checkEntry($_POST['new_post']['post_info']['sekrit'] ?? '', 'string'));
        }

        $post->changeData('response_to', $this->checkEntry($_POST['new_post']['post_info']['response_to'], 'integer'));

        if (!nel_true_empty($post->data('comment'))) {
            $wordfilters = new Wordfilters($this->domain->database());
            $post->changeData('comment', $wordfilters->apply($post->data('comment'), $this->domain));
            $cites = new Cites($this->domain->database());
            $cite_list = $cites->getCitesFromText($post->data('comment'), false);

            if (count($cite_list['board']) > $this->domain->setting('max_cites')) {
                nel_derp(45,
                    sprintf(_gettext('Comment contains too many cites. Maximum is %d.'),
                        $this->domain->setting('max_cites')));
            }

            if (count($cite_list['crossboard']) > $this->domain->setting('max_crossboard_cites')) {
                nel_derp(46,
                    sprintf(_gettext('Comment contains too many cross-board cites. Maximum is %d.'),
                        $this->domain->setting('max_crossboard_cites')));
            }

            $url_protocols = $this->domain->setting('url_protocols');
            $url_split_regex = '#(' . $url_protocols . ')(:\/\/)#';

            if (preg_match_all($url_split_regex, $post->data('comment')) > $this->domain->setting('max_comment_urls')) {
                nel_derp(47,
                    sprintf(_gettext('Comment contains too many URLs. Maximum is %d.'),
                        $this->domain->setting('max_comment_urls')));
            }
        }

        $time = nel_get_microtime();
        $post->changeData('post_time', $time['time']);
        $post->changeData('post_time_milli', $time['milli']);
    }

    public function checkEntry($post_item, $type)
    {
        if ($type === "integer" || $type === "int") {
            if (!is_numeric($post_item)) {
                return null;
            }
        }

        if ($type === "string" || $type === "str") {
            if ($post_item === '') {
                return null;
            }
        }

        settype($post_item, $type);
        return $post_item;
    }

    public function staffPost(): bool
    {
        $valid = (isset($_POST['post_as_staff'])) ? $this->checkEntry($_POST['post_as_staff'], 'boolean') : false;

        if (!$valid || !$this->session->isActive()) {
            return false;
        }

        $user = $this->session->user();

        if (!$user->checkPermission($this->domain, 'perm_post_as_staff')) {
            return false;
        }

        return true;
    }

    public function tripcode(string $key): string
    {
        $tripcode = '';
        $trip_key = $this->tripcodeCharsetConvert($key, 'SHIFT_JIS', 'UTF-8');
        $salt = utf8_substr($trip_key . 'H..', 1, 2);
        $salt = preg_replace('/[^\.-z]/', '.', $salt);
        $salt = strtr($salt, ':;<=>?@[\\]^_`', 'ABCDEFGabcdef');
        $tripcode = utf8_substr(crypt($trip_key, $salt), -10);
        return $tripcode;
    }

    public function secureTripcode(string $key): string
    {
        $secure_tripcode = '';
        $trip_code = hash_hmac(nel_site_domain()->setting('secure_tripcode_algorithm'), $key, NEL_TRIPCODE_PEPPER);
        $trip_code = base64_encode(pack("H*", $trip_code));
        $secure_tripcode = utf8_substr($trip_code, 2, 10);
        return $secure_tripcode;
    }

    public function capcode(string $key): string
    {
        $role = $this->session->user()->getDomainRole($this->domain);

        if ($role->getData('capcode') !== $key &&
            !$this->session->user()->checkPermission($this->domain, 'perm_custom_capcode')) {
            return '';
        }

        return $key;
    }

    public function tripcodeCharsetConvert($text, $to, $from)
    {
        if (function_exists('iconv')) {
            return iconv($from, $to . '//IGNORE', $text);
        } else if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($text, $to, $from);
        } else {
            return $text;
        }
    }

    public function fieldLengthCheck(string $field_name, ?string $text)
    {
        if (is_null($text)) {
            return $text;
        }

        $length = utf8_strlen($text);
        $min = 0;
        $max = 0;
        $error_number = 0;
        $error_message = '';

        switch ($field_name) {
            case 'name':
                $min = $this->domain->setting('min_name_length');
                $max = $this->domain->setting('max_name_length');
                $error_number = 48;
                $error_message = sprintf(_gettext('Name must be between %s and %s characters.'), $min, $max);
                break;

            case 'email':
                $min = $this->domain->setting('min_email_length');
                $max = $this->domain->setting('max_email_length');
                $error_number = 49;
                $error_message = sprintf(_gettext('Email must be between %s and %s characters.'), $min, $max);
                break;

            case 'subject':
                $min = $this->domain->setting('min_subject_length');
                $max = $this->domain->setting('max_subject_length');
                $error_number = 50;
                $error_message = sprintf(_gettext('Subject must be between %s and %s characters.'), $min, $max);
                break;

            case 'comment':
                $min = $this->domain->setting('min_comment_length');
                $max = $this->domain->setting('max_comment_length');
                $error_number = 51;
                $error_message = sprintf(_gettext('Comment must be between %s and %s characters.'), $min, $max);
                break;
        }

        if ($length >= $min && $length <= $max) {
            return $text;
        }

        if ($length > $max && $this->domain->setting('truncate_long_fields')) {
            return utf8_substr($text, 0, $max);
        }

        nel_derp($error_number, $error_message);
    }
}