<?php
declare(strict_types = 1);

namespace Nelliel\NewPost;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Cites;
use Nelliel\Dice;
use Nelliel\FGSFDS;
use Nelliel\IPInfo;
use Nelliel\ROBOT9000;
use Nelliel\VisitorInfo;
use Nelliel\Account\Session;
use Nelliel\Account\Authorization;
use Nelliel\Content\ContentID;
use Nelliel\Content\Post;
use Nelliel\Domains\Domain;
use Nelliel\Filters\Filters;

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
            nel_derp(40, __('No POST data was received. The request may have been too big.'));
        }

        $new_post_data = $_POST['new_post'];
        $thread_id = $new_post_data['thread_id'] ?? '';

        if (!ContentID::isContentID($thread_id)) {
            nel_derp(76, __('No recognizable thread ID provided.'));
        }

        $thread_content_id = new ContentID($thread_id);
        $is_op = $thread_content_id->threadID() === 0;
        $sub_threads_enabled = false; // TODO: Finish sub thread implementation

        if($is_op) {
            $reply_to = 0;
        } else {
            $reply_to = $thread_content_id->threadID();
            //$reply_to = intval($new_post_data['reply_to'] ?? $thread_content_id->threadID());
        }

        $parent_post_content_id = $thread_content_id;
        $parent_post_content_id->changePostID($reply_to);
        $parent_post = $parent_post_content_id->getInstanceFromID($this->domain);

        if($sub_threads_enabled && $parent_post->exists()) {
            $post->changeData('reply_to', $parent_post->contentID()->postID());
            $post->changeData('reply_depth', $parent_post->getData('reply_depth') + 1);
        } else {
            $post->changeData('reply_to', $reply_to);
            $post->changeData('reply_depth', 0);
        }

        $require_name = $is_op ? $this->domain->setting('require_op_name') : $this->domain->setting(
            'require_reply_name');
        $require_email = $is_op ? $this->domain->setting('require_op_email') : $this->domain->setting(
            'require_reply_email');
        $require_subject = $is_op ? $this->domain->setting('require_op_subject') : $this->domain->setting(
            'require_reply_subject');
        $require_comment = $is_op ? $this->domain->setting('require_op_comment') : $this->domain->setting(
            'require_reply_comment');

        $name = strval($new_post_data['not_anonymous'] ?? '');
        $name = $this->fieldLengthCheck('name', $name);

        if (nel_true_empty($name) && $require_name) {
            nel_derp(41, _gettext('A name is required to post.'));
        }

        $email = strval($new_post_data['spam_target'] ?? '');
        $email = $this->fieldLengthCheck('email', $name);

        if (nel_true_empty($email) && $require_email) {
            nel_derp(42, _gettext('An email is required to post.'));
        }

        $subject = strval($new_post_data['verb'] ?? '');
        $subject = $this->fieldLengthCheck('subject', $subject);

        if (nel_true_empty($subject) && $require_subject) {
            nel_derp(43, _gettext('A subject is required to post.'));
        }

        $original_comment = strval($new_post_data['wordswordswords'] ?? '');
        $comment = $this->fieldLengthCheck('comment', $original_comment);

        if (nel_true_empty($original_comment) && $require_comment) {
            nel_derp(44, _gettext('A comment is required to post.'));
        }

        $post->changeData('parent_thread', $thread_content_id->threadID());
        $post->contentID()->changeThreadID($post->getData('parent_thread'));
        $post->changeData('op', $is_op);
        $ip_info = new IPInfo(nel_request_ip_address());
        $post->changeData('hashed_ip_address', $ip_info->getInfo('hashed_ip_address'));
        $post->changeData('unhashed_ip_address', $ip_info->getInfo('unhashed_ip_address'));
        $visitor_info = new VisitorInfo(nel_visitor_id());
        $visitor_info->updateLastActivity(time());
        $post->changeData('visitor_id', $visitor_info->getInfo('visitor_id'));

        $enable_email = $is_op ? $this->domain->setting('enable_op_email_field') : $this->domain->setting(
            'enable_reply_email_field');
        $enable_subject = $is_op ? $this->domain->setting('enable_op_subject_field') : $this->domain->setting(
            'enable_reply_subject_field');
        $enable_comment = $is_op ? $this->domain->setting('enable_op_comment_field') : $this->domain->setting(
            'enable_reply_comment_field');

        $this->processName($name, $post);

        $raw_html = boolval($_POST['raw_html'] ?? false);

        if ($raw_html && $this->session->user()->checkPermission($this->domain, 'perm_raw_html')) {
            $post->getMoar()->changeSectionData('nelliel', 'raw_html', true);
        }

        $disable_markup = boolval($_POST['no_markup'] ?? false);

        if ($disable_markup) {
            $post->getMoar()->changeSectionData('nelliel', 'no_markup', true);
        }

        if ($enable_email && !$this->domain->setting('forced_anonymous')) {
            $post->changeData('email', $email);
        }

        if ($enable_subject) {
            $post->changeData('subject', $subject);
        }

        if ($enable_comment) {
            $post->changeData('original_comment', $original_comment);
            $post->changeData('comment', $comment);
        }

        if ($this->domain->setting('r9k_enable_board')) {
            $this->checkR9K($post->getData('comment'), $post->getData('hashed_ip_address'));
        }

        if ($this->domain->setting('enable_fgsfds_field')) {
            $post->changeData('fgsfds', strval($new_post_data['fgsfds'] ?? ''));
        }

        if ($this->domain->setting('enable_password_field')) {
            $password = strval($new_post_data['sekrit'] ?? '');
            $post->changeData('password',
                substr($password, 0, nel_crypt_config()->configValue('post_password_max_length')));
        }

        if (!nel_true_empty($post->getData('comment'))) {
            $filters = new Filters($this->domain->database());
            $post->changeData('comment',
                $filters->applyWordfilters($post->getData('comment'), [$this->domain->id(), Domain::GLOBAL]));
            $cites = new Cites($this->domain->database());
            $cite_list = $cites->getCitesFromText($post->getData('comment'), false);

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

            if (preg_match_all($url_split_regex, $post->getData('comment')) > $this->domain->setting('max_comment_urls')) {
                nel_derp(47,
                    sprintf(_gettext('Comment contains too many URLs. Maximum is %d.'),
                        $this->domain->setting('max_comment_urls')));
            }
        }

        $time = nel_get_microtime();
        $post->changeData('post_time', $time['time']);
        $post->changeData('post_time_milli', $time['milli']);

        if ($this->domain->setting('process_new_post_commands')) {
            $this->processFGSFDS($post);
        }

        $fgsfds = new FGSFDS();

        if (!$fgsfds->commandIsSet('noko') && $this->domain->setting('always_noko')) {
            $fgsfds->addCommand('noko', true);
        }

        $post->changeData('sage', false);

        if ($this->domain->setting('allow_sage')) {
            $post->changeData('sage', $fgsfds->commandIsSet('sage'));
        }

        if ($this->domain->setting('allow_dice_rolls')) {
            $this->rollDice($post);
        }
    }

    private function processName(string $name, Post $post): void
    {
        $enable_name = $post->getData('op') ? $this->domain->setting('enable_op_name_field') : $this->domain->setting(
            'enable_reply_name_field');
        $staff_post = $this->staffPost();

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

            if ($this->domain->setting('require_tripcode') && nel_true_empty($post->getData('tripcode')) &&
                nel_true_empty($post->getData('secure_tripcode'))) {
                nel_derp(41, _gettext('A tripcode or secure tripcode is required to post.'));
            }
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
    }

    private function staffPost(): bool
    {
        $valid = boolval($_POST['post_as_staff'] ?? false);

        if (!$valid || !$this->session->isActive()) {
            return false;
        }

        $user = $this->session->user();

        if (!$user->checkPermission($this->domain, 'perm_post_as_staff')) {
            return false;
        }

        return true;
    }

    private function tripcode(string $key): string
    {
        $tripcode = '';
        $trip_key = $this->tripcodeCharsetConvert($key, 'SHIFT_JIS', 'UTF-8');
        $salt = utf8_substr($trip_key . 'H..', 1, 2);
        $salt = preg_replace('/[^\.-z]/', '.', $salt);
        $salt = strtr($salt, ':;<=>?@[\\]^_`', 'ABCDEFGabcdef');
        $tripcode = utf8_substr(crypt($trip_key, $salt), -10);
        return $tripcode;
    }

    private function secureTripcode(string $key): string
    {
        $secure_tripcode = '';
        $trip_code = hash_hmac(nel_get_cached_domain(Domain::SITE)->setting('secure_tripcode_algorithm'), $key,
            NEL_TRIPCODE_PEPPER);
        $trip_code = base64_encode(pack("H*", $trip_code));
        $secure_tripcode = utf8_substr($trip_code, 2, 10);
        return $secure_tripcode;
    }

    private function capcode(string $key): string
    {
        $role = $this->session->user()->getDomainRole($this->domain);

        if ($role->getData('capcode') !== $key &&
            !$this->session->user()->checkPermission($this->domain, 'perm_custom_capcode')) {
            return '';
        }

        return $key;
    }

    private function tripcodeCharsetConvert($text, $to, $from)
    {
        if (function_exists('iconv')) {
            return iconv($from, $to . '//IGNORE', $text);
        } else if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($text, $to, $from);
        } else {
            return $text;
        }
    }

    private function fieldLengthCheck(string $field_name, ?string $text)
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

    private function checkR9K(string $comment, string $poster_hash): void
    {
        $r9k = new ROBOT9000();
        $comment_hash = $r9k->hashContent($this->domain, $comment);
        $unoriginal = $r9k->checkForHash($this->domain, $comment_hash);

        if ($this->domain->setting('r9k_unoriginal_mute')) {
            $mute_count = $r9k->muteCount($this->domain, $poster_hash);

            if ($mute_count > 0) {
                $last_mute_time = $r9k->getLastMuteTime($this->domain, $poster_hash);
                $mute_time = $r9k->calculateMuteTime($this->domain, $mute_count);
                $current_time = time();

                if ($current_time - $mute_time < $last_mute_time) {
                    nel_derp(53,
                        sprintf(__('You are currently muted. Mute expires in %d seconds.'),
                            $last_mute_time + $mute_time - $current_time));
                }
            }
        }

        if (!$unoriginal) {
            $r9k->addHash($this->domain, $comment_hash, time());
            return;
        }

        if (!$this->domain->setting('r9k_unoriginal_mute')) {
            nel_derp(54, __('Unoriginal content detected!'));
        } else {
            $r9k->addMute($this->domain, $poster_hash);
            $mute_time = $r9k->calculateMuteTime($this->domain, $mute_count + 1);
            nel_derp(55, sprintf(__('Unoriginal content detected! You have been muted for %d seconds.'), $mute_time));
        }
    }

    private function rollDice(Post $post): void
    {
        $fgsfds = new FGSFDS();
        $dice_instance = new Dice();
        $matches = array();

        foreach ($fgsfds->commandList() as $command => $value) {

            if (preg_match(Dice::DICE_REGEX, $command, $matches) !== 1) {
                continue;
            }

            $dice = intval($matches[1] ?? 1);

            if ($dice > $this->domain->setting('max_dice')) {
                $dice = $this->domain->setting('max_dice');
            }

            $sides = intval($matches[2] ?? 6);

            if ($sides > $this->domain->setting('max_dice_sides')) {
                $sides = $this->domain->setting('max_dice_sides');
            }

            $modifier = intval($matches[3] ?? 0);

            // If multiple rolls, only the last one is used
            $post->getMoar()->changeSectionData('nelliel', 'dice_roll', $dice_instance->roll($dice, $sides, $modifier));
        }
    }

    private function processFGSFDS(Post $post)
    {
        $fgsfds = new FGSFDS();
        $post_fgsfds = $post->getData('fgsfds') ?? '';

        $fgsfds->addFromString($post_fgsfds, true);
        $post_email = $post->getData('email') ?? '';

        // If there are duplicates, the FGSFDS field takes precedence
        if ($this->domain->setting('allow_email_commands')) {
            $email_parts = explode(' ', $post_email);

            if (is_array($email_parts) && count($email_parts) > 0 &&
                preg_match('/[^@]@[^@\s]+(?:\.|\:)/', $email_parts[0]) !== 1) {
                $fgsfds->addFromString($post_email, false);

                if (!$this->domain->setting('keep_email_commands')) {
                    $post->changeData('email', null);
                }
            }
        }
    }
}