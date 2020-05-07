<?php

namespace Nelliel\Post;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;

class NewPost
{
    private $domain;
    private $database;

    function __construct(Domain $domain)
    {
        $this->database = $domain->database();
        $this->domain = $domain;
    }

    public function processPost()
    {
        $site_domain = new \Nelliel\DomainSite($this->database);
        $error_data = ['board_id' => $this->domain->id()];

        if ($this->domain->setting('use_post_captcha') || $this->domain->setting('use_post_recaptcha'))
        {
            $captcha = new \Nelliel\CAPTCHA($this->domain);

            if ($this->domain->setting('use_post_captcha'))
            {
                $captcha_key = $_COOKIE['captcha-key'] ?? '';
                $captcha_answer = $_POST['new_post']['captcha_answer'] ?? '';
                $captcha_result = $captcha->verify($captcha_key, $captcha_answer);
            }
            else
            {
                $captcha_result = true;
            }

            if ($this->domain->setting('use_post_recaptcha'))
            {
                $recaptcha_result = $captcha->verifyReCAPTCHA();
            }
            else
            {
                $recaptcha_result = true;
            }

            if (!$captcha_result || !$recaptcha_result)
            {
                nel_derp(24, _gettext('CAPTCHA test failed or you appear to be a spambot.'), $error_data);
            }
        }

        if ($this->domain->reference('locked'))
        {
            nel_derp(23, _gettext('Board is locked. Cannot make new post.'), $error_data);
        }

        $authorization = new \Nelliel\Auth\Authorization($this->database);
        $archive = new \Nelliel\ArchiveAndPrune($this->database, $this->domain, new \Nelliel\FileHandler());
        $file_handler = new \Nelliel\FileHandler();
        $file_upload = new FilesUpload($this->domain, $_FILES, $authorization);
        $data_handler = new PostData($this->domain, $authorization);
        $post = new \Nelliel\Content\ContentPost(new \Nelliel\ContentID(), $this->domain);
        $data_handler->processPostData($post);
        $time = nel_get_microtime();
        $post->content_data['post_time'] = $time['time'];
        $post->content_data['post_time_milli'] = $time['milli'];

        // Check if post is ok
        $this->isPostOk($post->content_data, $time['time']);

        // Process FGSFDS
        $fgsfds = new \Nelliel\FGSFDS($post->content_data['fgsfds']);

        if (!empty($post->content_data['fgsfds']))
        {
            if ($fgsfds->getCommand('sage') !== false)
            {
                $fgsfds->modifyCommandData('sage', 'value', true);
            }
        }

        $post->content_data['sage'] = $fgsfds->getCommandData('sage', 'value');
        $files = $file_upload->processFiles($post);
        $spoon = !empty($files);
        $post->content_data['content_count'] = count($files);

        if (!$spoon)
        {
            if (!$post->content_data['comment'])
            {
                nel_derp(7, _gettext('Post contains no content or file. Dumbass.'), $error_data);
            }

            if ($this->domain->setting('require_content_always'))
            {
                nel_derp(8, _gettext('Image or file required when making a new post.'), $error_data);
            }

            if ($this->domain->setting('require_content_start') && $post->content_data['response_to'] == 0)
            {
                nel_derp(9, _gettext('Image or file required to make new thread.'), $error_data);
            }
        }

        if (utf8_strlen($post->content_data['comment']) > $this->domain->setting('max_comment_length'))
        {
            nel_derp(10, _gettext('Post is too long. Try looking up the word concise.'), $error_data);
        }

        if (isset($post->content_data['post_password']))
        {
            $poster_password = $post->content_data['post_password'];
            $post->content_data['post_password'] = nel_generate_salted_hash(
                    $site_domain->setting('post_password_algorithm'),
                    POST_PASSWORD_PEPPER . $post->content_data['post_password']);
        }
        else
        {
            $poster_password = utf8_substr(rand(), 0, 8);
        }

        // Cookies OM NOM NOM NOM
        $cookie_password = '';

        if (isset($_COOKIE['pwd-' . $this->domain->id()]))
        {
            $cookie_password = $_COOKIE['pwd-' . $this->domain->id()];
        }

        if (empty($cookie_password) ||
                (isset($post->content_data['post_password']) && $cookie_password !== $poster_password))
        {
            setrawcookie('pwd-' . $this->domain->id(), $poster_password, time() + 9001 * 24 * 3600, '/');
        }

        setrawcookie('name-' . $this->domain->id(), $post->content_data['poster_name'], time() + 30 * 24 * 3600, '/'); // 1 month cookie expiration

        // Go ahead and put post into database
        $post->content_data['op'] = ($post->content_data['parent_thread'] == 0) ? 1 : 0;
        $post->content_data['has_content'] = ($post->content_data['content_count'] > 0) ? 1 : 0;
        $post->reserveDatabaseRow($time['time'], $time['milli']);
        $thread = new \Nelliel\Content\ContentThread(new \Nelliel\ContentID(), $this->domain);

        if ($post->content_data['response_to'] == 0)
        {
            $thread->content_id->thread_id = $post->content_id->post_id;
            $thread->content_data['first_post'] = $post->content_id->post_id;
            $thread->content_data['last_post'] = $post->content_id->post_id;
            $thread->content_data['last_bump_time'] = $time['time'];
            $thread->content_data['last_bump_time_milli'] = $time['milli'];
            $thread->content_data['content_count'] = $post->content_data['content_count'];
            $thread->content_data['last_update'] = $time['time'];
            $thread->content_data['last_update_milli'] = $time['milli'];
            $thread->content_data['post_count'] = 1;
            $thread->writeToDatabase();
            $thread->createDirectories();
        }
        else
        {
            $thread->content_id->thread_id = $post->content_data['parent_thread'];
            $thread->loadFromDatabase();
            $thread->content_data['content_count'] = $thread->content_data['content_count'] +
                    $post->content_data['content_count'];
            $thread->content_data['last_update'] = $time['time'];
            $thread->content_data['last_update_milli'] = $time['milli'];
            $thread->content_data['post_count'] = $thread->content_data['post_count'] + 1;

            if ($thread->content_data['post_count'] <= $this->domain->setting('max_bumps') &&
                    !$fgsfds->getCommandData('sage', 'value'))
            {
                $thread->content_data['last_bump_time'] = $time['time'];
                $thread->content_data['last_bump_time_milli'] = $time['milli'];
            }

            $thread->writeToDatabase();
        }

        $post->writeToDatabase();
        $post->createDirectories();
        $fgsfds->modifyCommandData('noko', 'topic', $thread->content_id->thread_id);
        $src_path = $this->domain->reference('src_path') . $thread->content_id->thread_id . '/' .
                $post->content_id->post_id . '/';

        clearstatcache();

        // Add preview, file data and move uploads to final location if applicable
        if ($spoon)
        {
            // Make previews and do final file processing
            if ($this->domain->setting('use_preview'))
            {
                $preview_path = $this->domain->reference('preview_path') . $thread->content_id->thread_id . '/' .
                        $post->content_id->post_id . '/';
                $gen_previews = new Previews($this->domain);
                $files = $gen_previews->generate($files, $preview_path);
            }

            $order = 1;

            foreach ($files as $file)
            {
                $file->content_id->thread_id = $thread->content_id->thread_id;
                $file->content_data['parent_thread'] = $thread->content_id->thread_id;
                $file->content_id->post_id = $post->content_id->post_id;
                $file->content_data['post_ref'] = $post->content_id->post_id;
                $file->content_id->order_id = $order;
                $file->content_data['content_order'] = $order;
                $file_handler->moveFile($file->content_data['location'], $src_path . $file->content_data['fullname'],
                        false);
                chmod($src_path . $file->content_data['fullname'], octdec(FILE_PERM));
                $file->writeToDatabase();
                ++ $order;
            }
        }

        $archive->updateThreads();

        // Generate response page if it doesn't exist, otherwise update
        $regen = new \Nelliel\Regen();
        $regen->threads($this->domain, true, [$thread->content_id->thread_id]);
        $regen->index($this->domain);
        return $thread->content_id->thread_id;
    }

    private function isPostOk($post_data, $time)
    {
        $error_data = ['board_id' => $this->domain->id()];

        // Check for flood
        // If post is a reply, also check if the thread still exists

        if ($post_data['parent_thread'] == 0)
        {
            $thread_cooldown = $time - $this->domain->setting('thread_cooldown');
            $prepared = $this->database->prepare(
                    'SELECT COUNT(*) FROM "' . $this->domain->reference('posts_table') .
                    '" WHERE "post_time" > ? AND "ip_address" = ?');
            $prepared->bindValue(1, $thread_cooldown, PDO::PARAM_STR);
            $prepared->bindValue(2, @inet_pton($_SERVER['REMOTE_ADDR']), PDO::PARAM_LOB);
            $renzoku = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
        }
        else
        {
            $reply_cooldown = $time - $this->domain->setting('reply_cooldown');
            $prepared = $this->database->prepare(
                    'SELECT COUNT(*) FROM "' . $this->domain->reference('posts_table') .
                    '" WHERE "parent_thread" = ? AND "post_time" > ? AND "ip_address" = ?');
            $prepared->bindValue(1, $post_data['parent_thread'], PDO::PARAM_INT);
            $prepared->bindValue(2, $reply_cooldown, PDO::PARAM_STR);
            $prepared->bindValue(3, @inet_pton($_SERVER['REMOTE_ADDR']), PDO::PARAM_LOB);
            $renzoku = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
        }

        if ($renzoku > 0)
        {
            nel_derp(1, _gettext('Flood detected! You\'re posting too fast, slow the fuck down.'), $error_data);
        }

        if ($post_data['parent_thread'] != 0)
        {
            $prepared = $this->database->prepare(
                    'SELECT "post_count", "archive_status", "locked" FROM "' . $this->domain->reference('threads_table') .
                    '" WHERE "thread_id" = ?');
            $thread_info = $this->database->executePreparedFetch($prepared, [$post_data['parent_thread']],
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

            if ($thread_info['post_count'] >= $this->domain->setting('max_posts'))
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