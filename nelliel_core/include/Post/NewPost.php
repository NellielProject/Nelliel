<?php

namespace Nelliel\Post;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Account\Session;
use Nelliel\Domains\Domain;
use Nelliel\IfThens\IfThen;

class NewPost
{
    private $domain;
    private $database;
    private $session;

    function __construct(Domain $domain, Session $session)
    {
        $this->database = $domain->database();
        $this->domain = $domain;
        $this->session = $session;
    }

    public function processPost()
    {
        $site_domain = new \Nelliel\Domains\DomainSite($this->database);
        $error_data = ['board_id' => $this->domain->id()];
        $captcha = new \Nelliel\CAPTCHA($this->domain);

        if ($this->domain->setting('use_post_captcha'))
        {
            $captcha_key = $_COOKIE['captcha-key'] ?? '';
            $captcha_answer = $_POST['new_post']['captcha_answer'] ?? '';
            $captcha->verify($captcha_key, $captcha_answer);
        }

        if ($this->domain->setting('use_post_recaptcha'))
        {
            $captcha->verifyReCAPTCHA();
        }

        if ($this->domain->reference('locked'))
        {
            nel_derp(23, _gettext('Board is locked. Cannot make new post.'), $error_data);
        }

        $authorization = new \Nelliel\Auth\Authorization($this->database);
        $file_handler = nel_utilities()->fileHandler();
        $uploads_handler = new Uploads($this->domain, $_FILES, [$_POST['embed_url'] ?? ''], $authorization,
                $this->session);
        $data_handler = new PostData($this->domain, $authorization, $this->session);
        $post = new \Nelliel\Content\ContentPost(new \Nelliel\Content\ContentID(), $this->domain);
        $data_handler->processPostData($post);
        $time = nel_get_microtime();
        $post->changeData('post_time', $time['time']);
        $post->changeData('post_time_milli', $time['milli']);

        // Check if post is ok
        $this->isPostOk($post, $time['time']);

        // Process FGSFDS
        $fgsfds = new \Nelliel\FGSFDS($post->data('fgsfds'));

        if (!empty($post->data('fgsfds')))
        {
            if ($fgsfds->getCommand('sage') !== false)
            {
                $fgsfds->modifyCommandData('sage', 'value', true);
            }
        }

        $post->changeData('sage', $fgsfds->getCommandData('sage', 'value'));
        $uploads = $uploads_handler->process($post);
        $spoon = !empty($uploads);
        $post->changeData('content_count', count($uploads));

        if (!$spoon)
        {
            if (!$post->data('comment'))
            {
                nel_derp(7, _gettext('Post contains zero content. What was the point of this?'), $error_data);
            }
        }

        if (utf8_strlen($post->data('comment')) > $this->domain->setting('max_comment_length'))
        {
            nel_derp(10, _gettext('Post is too long. Try looking up the word concise.'), $error_data);
        }

        if (!is_null($post->data('post_password')))
        {
            $post->changeData('post_password', nel_post_password_hash($post->data('post_password')));
        }

        // Go ahead and put post into database
        $post->changeData('op', ($post->data('parent_thread') == 0) ? 1 : 0);
        $post->changeData('has_content', ($post->data('content_count') > 0) ? 1 : 0);

        // Process if-thens for new post here
        $if_then = new IfThen($this->domain->database(), new ConditionsPost($post, $uploads),
                new ActionsPost($post, $uploads));
        $if_then->process($this->domain->id());

        $post->reserveDatabaseRow($time['time'], $time['milli'], nel_request_ip_address(true));
        $post->storeCache();
        $thread = new \Nelliel\Content\ContentThread(new \Nelliel\Content\ContentID(), $this->domain);

        if ($post->data('response_to') == 0)
        {
            $thread->contentID()->changeThreadID($post->contentID()->postID());
            $thread->changeData('first_post', $post->contentID()->postID());
            $thread->changeData('last_post', $post->contentID()->postID());
            $thread->changeData('last_bump_time', $time['time']);
            $thread->changeData('last_bump_time_milli', $time['milli']);
            $thread->changeData('content_count', $post->data('content_count'));
            $thread->changeData('last_update', $time['time']);
            $thread->changeData('last_update_milli', $time['milli']);
            $thread->changeData('post_count', 1);
            $thread->writeToDatabase();
            $thread->createDirectories();
        }
        else
        {
            $thread->contentID()->changeThreadID($post->data('parent_thread'));
            $thread->loadFromDatabase();
            $thread->changeData('content_count', $thread->data('content_count') + $post->data('content_count'));
            $thread->changeData('last_update', $time['time']);
            $thread->changeData('last_update_milli', $time['milli']);
            $thread->changeData('post_count', $thread->data('post_count') + 1);

            if ((!$this->domain->setting('limit_bump_count') ||
                    $thread->data('post_count') <= $this->domain->setting('max_bumps')) &&
                    !$fgsfds->getCommandData('sage', 'value') && !$thread->data('permasage'))
            {
                $thread->changeData('last_bump_time', $time['time']);
                $thread->changeData('last_bump_time_milli', $time['milli']);
            }

            $thread->writeToDatabase();
        }

        $post->writeToDatabase();
        $post->createDirectories();
        $fgsfds->modifyCommandData('noko', 'topic', $thread->contentID()->threadID());
        $src_path = $this->domain->reference('src_path') . $thread->contentID()->threadID() . '/' .
                $post->contentID()->postID() . '/';

        clearstatcache();

        // Add preview, file data and move uploads to final location if applicable
        if ($spoon)
        {
            // Make previews and do final file processing
            if ($this->domain->setting('generate_preview'))
            {
                $preview_path = $this->domain->reference('preview_path') . $thread->contentID()->threadID() . '/' .
                        $post->contentID()->postID() . '/';
                $gen_previews = new Previews($this->domain);
                $uploads = $gen_previews->generate($uploads, $preview_path);
            }

            $order = 1;

            foreach ($uploads as $upload)
            {
                $upload->contentID()->changeThreadID($thread->contentID()->threadID());
                $upload->changeData('parent_thread', $thread->contentID()->threadID());
                $upload->contentID()->changePostID($post->contentID()->postID());
                $upload->changeData('post_ref', $post->contentID()->postID());
                $upload->contentID()->changeOrderID($order);
                $upload->changeData('content_order', $order);

                if ($upload->data('type') !== 'embed')
                {
                    $file_handler->moveFile($upload->data('location'), $src_path . $upload->data('fullname'), false);
                    chmod($src_path . $upload->data('fullname'), octdec(NEL_FILES_PERM));
                }

                $upload->writeToDatabase();
                $order ++;
            }
        }

        $update_overboard = new \Nelliel\Overboard($this->database);
        $thread->loadFromDatabase(); // Make sure we have any expected defaults set
        $update_overboard->addThread($thread);

        // Generate response page if it doesn't exist, otherwise update
        $regen = new \Nelliel\Regen();
        $regen->threads($this->domain, true, [$thread->contentID()->threadID()]);
        $regen->index($this->domain);

        if ($site_domain->setting('overboard_active') || $site_domain->setting('sfw_overboard_active'))
        {
            $regen->overboard($site_domain);
        }

        return $thread->contentID()->threadID();
    }

    private function isPostOk($post, $time)
    {
        $error_data = ['board_id' => $this->domain->id()];

        // Check for flood
        // If post is a reply, also check if the thread still exists

        if ($post->data('parent_thread') == 0)
        {
            $thread_renzoku = $time - $this->domain->setting('thread_renzoku');
            $prepared = $this->database->prepare(
                    'SELECT 1 FROM "' . $this->domain->reference('posts_table') .
                    '" WHERE "post_time" > ? AND "hashed_ip_address" = ?');
            $prepared->bindValue(1, $thread_renzoku, PDO::PARAM_STR);
            $prepared->bindValue(2, nel_prepare_hash_for_storage(nel_request_ip_address(true)), PDO::PARAM_LOB);
            $renzoku = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
        }
        else
        {
            $reply_renzoku = $time - $this->domain->setting('reply_renzoku');
            $prepared = $this->database->prepare(
                    'SELECT 1 FROM "' . $this->domain->reference('posts_table') .
                    '" WHERE "parent_thread" = ? AND "post_time" > ? AND "hashed_ip_address" = ?');
            $prepared->bindValue(1, $post->data('parent_thread'), PDO::PARAM_INT);
            $prepared->bindValue(2, $reply_renzoku, PDO::PARAM_STR);
            $prepared->bindValue(3, nel_prepare_hash_for_storage(nel_request_ip_address(true)), PDO::PARAM_LOB);
            $renzoku = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
        }

        if ($renzoku > 0)
        {
            nel_derp(1, _gettext("Flood detected! You're posting too fast, slow down."), $error_data);
        }

        if ($post->data('parent_thread') != 0)
        {
            $prepared = $this->database->prepare(
                    'SELECT "post_count", "archive_status", "locked" FROM "' . $this->domain->reference('threads_table') .
                    '" WHERE "thread_id" = ?');
            $thread_info = $this->database->executePreparedFetch($prepared, [$post->data('parent_thread')],
                    PDO::FETCH_ASSOC, true);

            if (!empty($thread_info))
            {
                if ($thread_info['locked'] == 1)
                {
                    nel_derp(2, _gettext('This thread is locked.'), $error_data);
                }

                if ($thread_info['archive_status'] != 0)
                {
                    nel_derp(3, _gettext('The thread you have tried posting in is currently inaccessible or archived.'),
                            $error_data);
                }
            }
            else
            {
                nel_derp(4, _gettext('The thread you have tried posting in could not be found.'), $error_data);
            }

            if ($this->domain->setting('limit_post_count') &&
                    $thread_info['post_count'] >= $this->domain->setting('max_posts'))
            {
                nel_derp(5, _gettext('The thread has reached maximum posts.'), $error_data);
            }

            if ($thread_info['archive_status'] != 0)
            {
                nel_derp(6, _gettext('The thread is archived or buffered and cannot be posted to.'), $error_data);
            }
        }
    }
}