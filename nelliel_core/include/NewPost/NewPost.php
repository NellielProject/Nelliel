<?php
declare(strict_types = 1);

namespace Nelliel\NewPost;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\ArchiveAndPrune;
use Nelliel\Cites;
use Nelliel\FGSFDS;
use Nelliel\Overboard;
use Nelliel\Regen;
use Nelliel\Account\Session;
use Nelliel\AntiSpam\CAPTCHA;
use Nelliel\Auth\Authorization;
use Nelliel\Content\ContentID;
use Nelliel\Content\Post;
use Nelliel\Content\Thread;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainSite;
use Nelliel\IfThens\IfThen;
use PDO;

class NewPost
{
    private $domain;
    private $database;
    private $session;

    function __construct(Domain $domain, Session $session)
    {
        $this->domain = $domain;
        $this->database = $domain->database();
        $this->session = $session;
    }

    public function processPost()
    {
        $site_domain = new DomainSite($this->database);
        $error_data = ['board_id' => $this->domain->id()];
        $captcha = new CAPTCHA($this->domain);

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

        if ($this->domain->reference('locked') &&
                !$this->session->user()->checkPermission($this->domain, 'perm_post_locked_board'))
        {
            nel_derp(11, _gettext('Board is locked. Cannot make new post.'), $error_data);
        }

        $authorization = new Authorization($this->database);
        $file_handler = nel_utilities()->fileHandler();
        $uploads_handler = new Uploads($this->domain, $authorization, $this->session);
        $data_handler = new PostData($this->domain, $authorization, $this->session);
        $post = new Post(new ContentID(), $this->domain);
        $data_handler->processPostData($post);
        $time = nel_get_microtime();

        // Check if post is ok
        $this->isPostOk($post, $time['time']);

        // Process FGSFDS
        if ($this->domain->setting('process_new_post_commands'))
        {
            $fgsfds = new FGSFDS();
            $post_fgsfds = $post->data('fgsfds') ?? '';

            $fgsfds->addFromString($post_fgsfds, true);
            $post_email = $post->data('email') ?? '';

            // If there are duplicates, the FGSFDS field takes precedence
            if ($this->domain->setting('allow_email_commands'))
            {
                $email_parts = explode(' ', $post_email);

                if ($email_parts !== false && count($email_parts) > 1 &&
                        preg_match('/[^@]@[^@\s]+(?:\.|\:)/', $email_parts[0]) !== 1)
                {
                    $fgsfds->addFromString($post_email, false);
                    $post->changeData('email', null);
                }
            }

            if (!$fgsfds->commandIsSet('noko') && $this->domain->setting('always_noko'))
            {
                $fgsfds->addCommand('noko', true);
            }

            $post->changeData('sage', false);

            if ($this->domain->setting('allow_sage'))
            {
                $post->changeData('sage', $fgsfds->commandIsSet('sage'));
            }
        }

        $uploads = $uploads_handler->process($post);
        $spoon = !empty($uploads);
        $post->changeData('total_uploads', count($uploads));

        if (!$spoon)
        {
            if (!$post->data('comment'))
            {
                nel_derp(9, _gettext('Post contains zero content. What was the point of this?'), $error_data);
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

        // Process if-thens for new post here
        $if_then = new IfThen(new ConditionsPost($post, $uploads), new ActionsPost($post, $uploads));
        $if_then->process('new_post');

        $post->reserveDatabaseRow($time['time'], $time['milli'], nel_request_ip_address(true));
        $thread = new Thread(new ContentID(), $this->domain);

        if ($post->data('op'))
        {
            $thread->contentID()->changeThreadID($post->contentID()->postID());
            $thread->changeData('thread_id', $post->contentID()->postID());
            $thread->changeData('last_bump_time', $time['time']);
            $thread->changeData('last_bump_time_milli', $time['milli']);
            $thread->changeData('last_update', $time['time']);
            $thread->changeData('last_update_milli', $time['milli']);
            $thread->changeData('post_count', 1);
            $thread->changeData('slug', $thread->generateSlug($post));
            $thread->writeToDatabase();
            $thread->createDirectories();
        }
        else
        {
            $thread->contentID()->changeThreadID($post->data('parent_thread'));
            $thread->loadFromDatabase();
            $thread->changeData('last_update', $time['time']);
            $thread->changeData('last_update_milli', $time['milli']);
            $thread->changeData('post_count', $thread->data('post_count') + 1);

            if ((!$this->domain->setting('limit_bump_count') ||
                    $thread->data('post_count') <= $this->domain->setting('max_bumps')) && !$fgsfds->commandIsSet(
                            'sage') && !$thread->data('permasage'))
            {
                $thread->changeData('last_bump_time', $time['time']);
                $thread->changeData('last_bump_time_milli', $time['milli']);
            }
        }

        $post->writeToDatabase();
        $this->addCites($post);
        $post->storeCache();
        $post->createDirectories();

        if ($fgsfds->commandIsSet('noko'))
        {
            $fgsfds->updateCommandData('noko', 'topic', $thread->contentID()->threadID());
        }

        $src_path = $this->domain->reference('src_path') . $post->contentID()->threadID() . '/' .
                $post->contentID()->postID() . '/';

        clearstatcache();

        // Add preview, file data and move uploads to final location if applicable
        if ($spoon)
        {
            // Make previews and do final file processing
            if ($this->domain->setting('create_static_preview') || $this->domain->setting('create_animated_preview'))
            {
                $preview_path = $this->domain->reference('preview_path') . $post->contentID()->threadID() . '/' .
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
                $upload->changeData('upload_order', $order);

                if ($upload->data('category') !== 'embed')
                {
                    $file_handler->moveFile($upload->data('location'), $src_path . $upload->data('fullname'), false);
                    chmod($src_path . $upload->data('fullname'), octdec(NEL_FILES_PERM));
                }

                $upload->writeToDatabase();
                $order ++;
            }
        }

        $thread->writeToDatabase();
        $thread->updateCounts();
        $thread->loadFromDatabase(); // Make sure we have any expected defaults set

        if ($thread->data('cyclic') == 1)
        {
            $thread->cycle();
        }

        $archive_and_prune = new ArchiveAndPrune($thread->domain(), $file_handler);
        $archive_and_prune->updateThreads();

        $update_overboard = new Overboard($this->database);
        $update_overboard->addThread($thread);

        // Generate response page if it doesn't exist, otherwise update
        $regen = new Regen();
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
            $renzoku_setting = $time - $this->domain->setting('thread_renzoku');
            $op_value = 1;
        }
        else
        {
            $renzoku_setting = $time - $this->domain->setting('reply_renzoku');
            $op_value = 0;
        }

        $prepared = $this->database->prepare(
                'SELECT 1 FROM "' . $this->domain->reference('posts_table') .
                '" WHERE "post_time" > ? AND "op" = ? AND "hashed_ip_address" = ?');
        $prepared->bindValue(1, $renzoku_setting, PDO::PARAM_STR);
        $prepared->bindValue(2, $op_value, PDO::PARAM_INT);
        $prepared->bindValue(3, nel_request_ip_address(true), PDO::PARAM_STR);
        $renzoku = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);

        if ($renzoku > 0 && !$this->session->user()->checkPermission($this->domain, 'perm_bypass_renzoku'))
        {
            nel_derp(3, _gettext("Flood detected! You're posting too fast, slow down."), $error_data);
        }

        if ($post->data('parent_thread') != 0)
        {
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . $this->domain->reference('threads_table') . '" WHERE "thread_id" = ?');
            $thread_info = $this->database->executePreparedFetch($prepared, [$post->data('parent_thread')],
                    PDO::FETCH_ASSOC, true);

            if (!empty($thread_info))
            {
                if ($thread_info['locked'] == 1 &&
                        !$this->session->user()->checkPermission($this->domain, 'perm_post_locked_thread'))
                {
                    nel_derp(4, _gettext('This thread is locked, you cannot post in it.'), $error_data);
                }

                if ($thread_info['old'] != 0)
                {
                    nel_derp(5, _gettext('The thread you have tried posting in is currently inaccessible or archived.'),
                            $error_data);
                }
            }
            else
            {
                nel_derp(6, _gettext('The thread you have tried posting in could not be found.'), $error_data);
            }

            if ($this->domain->setting('limit_post_count') && $thread_info['cyclic'] != 1 &&
                    $thread_info['post_count'] >= $this->domain->setting('max_posts'))
            {
                nel_derp(7, _gettext('The thread has reached maximum posts.'), $error_data);
            }

            if ($thread_info['old'] != 0)
            {
                nel_derp(8, _gettext('The thread is archived or buffered and cannot be posted to.'), $error_data);
            }
        }
    }

    private function addCites(Post $post)
    {
        $cites = new Cites($this->database);

        if (nel_true_empty($post->data('comment')))
        {
            return;
        }

        $cite_list = $cites->getCitesFromText($post->data('comment'));

        foreach ($cite_list as $cite)
        {
            $cite_data = $cites->getCiteData($cite, $this->domain, $this->content_id);

            if ($cite_data['exists'] || $cite_data['future'])
            {
                $cites->addCite($cite_data);
            }
        }

        $cites->updateForPost($post);
    }
}