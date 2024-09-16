<?php
declare(strict_types = 1);

namespace Nelliel\NewPost;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\ArchiveAndPrune;
use Nelliel\Cites;
use Nelliel\FGSFDS;
use Nelliel\GlobalRecents;
use Nelliel\Overboard;
use Nelliel\Regen;
use Nelliel\Account\Session;
use Nelliel\AntiSpam\CAPTCHA;
use Nelliel\Auth\Authorization;
use Nelliel\Checkpoints\Checkpoint;
use Nelliel\Content\ContentID;
use Nelliel\Content\Post;
use Nelliel\Content\Thread;
use Nelliel\Domains\Domain;
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
        $site_domain = Domain::getDomainFromID(Domain::SITE);
        $error_data = ['board_id' => $this->domain->id()];
        $captcha = new CAPTCHA($this->domain);

        if (nel_get_cached_domain(Domain::SITE)->setting('enable_captchas') &&
            $this->domain->setting('use_post_captcha')) {
            $captcha_key = $_COOKIE['captcha-key'] ?? '';
            $captcha_answer = $_POST['new_post']['captcha_answer'] ?? '';
            $captcha->verify($captcha_key, $captcha_answer);
        }

        if ($this->domain->reference('locked') &&
            !$this->session->user()->checkPermission($this->domain, 'perm_post_locked_board')) {
            nel_derp(11, _gettext('Board is locked. Cannot make new post.'), 403, $error_data);
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
        $uploads = $uploads_handler->process($post);
        $spoon = !empty($uploads);
        $post->changeData('total_uploads', count($uploads));

        if (!$spoon) {
            if (!$post->getData('comment')) {
                nel_derp(9, _gettext('Post contains zero content. What was the point of this?'), 0, $error_data);
            }
        } else {
            $renzoku_setting = $time['time'] - $this->domain->setting('upload_renzoku');
            $prepared = $this->database->prepare(
                'SELECT 1 FROM "' . $this->domain->reference('posts_table') .
                '" WHERE "post_time" > ? AND "hashed_ip_address" = ? AND "total_uploads" > 0 LIMIT 1');
            $prepared->bindValue(1, $renzoku_setting, PDO::PARAM_INT);
            $prepared->bindValue(2, nel_request_ip_address(true), PDO::PARAM_STR);
            $upload_renzoku = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);

            if ($upload_renzoku !== false &&
                !$this->session->user()->checkPermission($this->domain, 'perm_bypass_renzoku')) {
                nel_derp(57, _gettext("Flood detected! You're making posts with uploads too fast."), 0, $error_data);
            }
        }

        if (utf8_strlen($post->getData('comment')) > $this->domain->setting('max_comment_length')) {
            nel_derp(10, _gettext('Post is too long. Try looking up the word concise.'), 0, $error_data);
        }

        if (!is_null($post->getData('password'))) {
            $post->changeData('password',
                nel_password_hash($post->getData('password'), nel_crypt_config()->postPasswordAlgorithm(),
                    nel_crypt_config()->postPasswordOptions()));
        }

        // Checkpoint for new post
        $checkpoint = new Checkpoint(new ConditionsPost($post, $uploads), new ActionsPost($post, $uploads));
        $checkpoint->process('new_post');

        $post->reserveDatabaseRow();
        $thread_id = ($post->getData('op')) ? $post->contentID()->postID() : $post->getData('parent_thread');
        $thread = new Thread(new ContentID(ContentID::createIDString($thread_id, 0, 0)), $this->domain);
        $thread->addPost($post);
        $post->writeToDatabase();
        $cites = new Cites($this->database);
        $cites->addCitesFromPost($post);
        $post->storeCache();
        $post->createDirectories();
        $fgsfds = new FGSFDS();

        if ($fgsfds->commandIsSet('noko')) {
            $fgsfds->updateCommandData('noko', 'topic', $thread->contentID()->threadID());
        }

        clearstatcache();

        // Add preview, file data and move uploads to final location if applicable
        if ($spoon) {
            // Make previews and do final file processing
            if ($this->domain->setting('create_static_preview') || $this->domain->setting('create_animated_preview')) {
                $gen_previews = new Previews($this->domain);
                $uploads = $gen_previews->generate($uploads, $post->previewFilePath());
            }

            $order = 1;

            foreach ($uploads as $upload) {
                $upload->contentID()->changeThreadID($thread->contentID()->threadID());
                $upload->changeData('parent_thread', $thread->contentID()->threadID());
                $upload->contentID()->changePostID($post->contentID()->postID());
                $upload->changeData('post_ref', $post->contentID()->postID());
                $upload->contentID()->changeOrderID($order);
                $upload->changeData('upload_order', $order);

                if ($upload->getData('category') !== 'embed' && !$upload->getData('use_existing')) {
                    $file_handler->moveFile($upload->getData('location'),
                        $post->srcFilePath() . $upload->getData('fullname'));
                    chmod($post->srcFilePath() . $upload->getData('fullname'), octdec(NEL_FILES_PERM));
                    $upload->changeData('location', $post->srcFilePath() . $upload->getData('fullname'));
                }

                $upload->writeToDatabase();
                $order ++;
            }
        }

        $thread->writeToDatabase();
        $thread->updateCounts();
        $thread->loadFromDatabase(); // Make sure we have any expected defaults set
        $automatic_gets = json_decode($this->domain->setting('automatic_gets'), true);

        if (is_array($automatic_gets) && in_array($post->contentID()->postID(), $automatic_gets)) {
            $get_thread = $post->convertToThread(true);
            $get_thread->toggleSticky();
        }

        if ($thread->getData('cyclic') == 1) {
            $thread->cycle();
        }

        $archive_and_prune = new ArchiveAndPrune($thread->domain());
        $archive_and_prune->updateThreads();
        $update_overboard = new Overboard($this->database);
        $update_overboard->addThread($thread);
        $update_global_recents = new GlobalRecents($this->database);
        $update_global_recents->addPost($post);
        $this->domain->updateStatistics();

        // Generate thread page if it doesn't exist, otherwise update
        $regen = new Regen();
        $regen->threads($this->domain, [$thread->contentID()->threadID()]);
        $regen->index($this->domain);

        if ($site_domain->setting('overboard_active') || $site_domain->setting('sfw_overboard_active')) {
            $regen->overboard($site_domain);
        }

        return $thread->contentID()->threadID();
    }

    private function isPostOk(Post $post, $time)
    {
        $error_data = ['board_id' => $this->domain->id()];

        // Check for flood
        if (!$this->session->user()->checkPermission($this->domain, 'perm_bypass_renzoku')) {
            if ($post->getData('op')) {
                $renzoku_setting = $time - $this->domain->setting('thread_renzoku');
                $op_value = 1;
            } else {
                $renzoku_setting = $time - $this->domain->setting('reply_renzoku');
                $op_value = 0;
            }

            $prepared = $this->database->prepare(
                'SELECT 1 FROM "' . $this->domain->reference('posts_table') .
                '" WHERE "post_time" > ? AND "op" = ? AND "hashed_ip_address" = ? LIMIT 1');
            $prepared->bindValue(1, $renzoku_setting, PDO::PARAM_INT);
            $prepared->bindValue(2, $op_value, PDO::PARAM_INT);
            $prepared->bindValue(3, nel_request_ip_address(true), PDO::PARAM_STR);
            $renzoku = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);

            if ($renzoku !== false) {
                nel_derp(3, _gettext("Flood detected! You're posting too fast, slow down."), 0, $error_data);
            }
        }

        $thread = $post->getParent();

        if (is_null($thread->getData('thread_id'))) {
            nel_derp(6, _gettext('The thread you tried posting in could not be found.'), 404, $error_data);
        }

        if ($thread->getData('locked') &&
            !$this->session->user()->checkPermission($this->domain, 'perm_post_locked_thread')) {
            nel_derp(4, _gettext('This thread is locked, you cannot post in it.'), 0, $error_data);
        }

        if ($thread->getData('old') && !$this->session->user()->checkPermission($this->domain, 'perm_post_locked_thread')) {
            nel_derp(5, _gettext('The thread you tried posting in is currently inaccessible or archived.'), 0,
                $error_data);
        }

        if ($post->getData('op')) {
            if ($this->domain->setting('limit_post_count') && $thread->getData('cyclic') != 1 &&
                $thread->getData('post_count') >= $this->domain->setting('max_posts')) {
                nel_derp(7, _gettext('The thread has reached maximum posts.'), 0, $error_data);
            }
        } else {
            if ($this->domain->setting('threads_per_hour_limit') > 0 &&
                !$this->session->user()->checkPermission($this->domain, 'perm_bypass_renzoku')) {
                $prepared = $this->database->prepare(
                    'SELECT COUNT("post_number") FROM "' . $this->domain->reference('posts_table') .
                    '" WHERE "post_time" > ? AND "op" = 1');
                $prepared->bindValue(1, (time() - 3600), PDO::PARAM_INT);
                $thread_count = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);

                if ($thread_count >= $this->domain->setting('threads_per_hour_limit')) {
                    nel_derp(37, _gettext('The maximum threads per hour has been reached.'), 0, $error_data);
                }
            }
        }
    }
}