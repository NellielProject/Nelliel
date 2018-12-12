<?php

namespace Nelliel\Post;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class NewPost
{
    private $domain;
    private $database;

    function __construct($database, $domain)
    {
        $this->database = $database;
        $this->board = $domain;
    }

    public function processPost()
    {
        $error_data = array('board_id' => $this->board->id());

        if($this->board->reference('locked'))
        {
                nel_derp(23, _gettext('Board is locked. Cannot make new post.'), $error_data);
        }

        $authorization = new \Nelliel\Auth\Authorization($this->database);
        $archive = new \Nelliel\ArchiveAndPrune($this->database, $this->board, new \Nelliel\FileHandler());
        $file_handler = new \Nelliel\FileHandler();
        $file_upload = new FilesUpload($this->board, $_FILES, $authorization);
        $data_handler = new PostData($this->board, $authorization);
        $post = new \Nelliel\Content\ContentPost($this->database, new \Nelliel\ContentID(), $this->board->id());
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
            if($fgsfds->getCommand('sage') !== false)
            {
                $fgsfds->modifyCommandData('sage', 'value', true);
            }
        }
        else


            $post->content_data['sage'] = $fgsfds->getCommandData('sage', 'value');
            $files = $file_upload->processFiles($post->content_data['response_to']);
            $spoon = !empty($files);
            $post->content_data['file_count'] = count($files);

            if (!$spoon)
            {
                if (!$post->content_data['comment'])
                {
                    nel_derp(7, _gettext('Post contains no content or file. Dumbass.'), $error_data);
                }

                if ($this->board->setting('require_image_always'))
                {
                    nel_derp(8, _gettext('Image or file required when making a new post.'), $error_data);
                }

                if ($this->board->setting('require_image_start') && $post->content_data['response_to'] == 0)
                {
                    nel_derp(9, _gettext('Image or file required to make new thread.'), $error_data);
                }
            }

            if (utf8_strlen($post->content_data['comment']) > $this->board->setting('max_comment_length'))
            {
                nel_derp(10, _gettext('Post is too long. Try looking up the word concise.'), $error_data);
            }

            if (isset($post->content_data['post_password']))
            {
                $cpass = $post->content_data['post_password'];
                $post->content_data['post_password'] = nel_generate_salted_hash(
                        nel_parameters_and_data()->siteSettings('post_password_algorithm'), $post->content_data['post_password']);
            }
            else
            {
                $cpass = utf8_substr(rand(), 0, 8);
            }

            // Cookies OM NOM NOM NOM
            setrawcookie('pwd-' . $this->board->id(), $cpass, time() + 30 * 24 * 3600, '/'); // 1 month cookie expiration
            setrawcookie('name-' . $this->board->id(), $post->content_data['poster_name'], time() + 30 * 24 * 3600, '/'); // 1 month cookie expiration

            // Go ahead and put post into database
            $post->content_data['op'] = ($post->content_data['parent_thread'] == 0) ? 1 : 0;
            $post->content_data['has_file'] = ($post->content_data['file_count'] > 0) ? 1 : 0;
            $post->reserveDatabaseRow($time['time'], $time['milli']);
            $thread = new \Nelliel\Content\ContentThread($this->database, new \Nelliel\ContentID(), $this->board->id());

            if ($post->content_data['response_to'] == 0)
            {
                $thread->content_id->thread_id = $post->content_id->post_id;
                $thread->content_data['first_post'] = $post->content_id->post_id;
                $thread->content_data['last_post'] = $post->content_id->post_id;
                $thread->content_data['last_bump_time'] = $time['time'];
                $thread->content_data['last_bump_time_milli'] = $time['milli'];
                $thread->content_data['total_files'] = $post->content_data['file_count'];
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
                $thread->content_data['total_files'] = $thread->content_data['total_files'] + $post->content_data['file_count'];
                $thread->content_data['last_update'] = $time['time'];
                $thread->content_data['last_update_milli'] = $time['milli'];
                $thread->content_data['post_count'] = $thread->content_data['post_count'] + 1;

                if ($thread->content_data['post_count'] <= $this->board->setting('max_bumps') && !$fgsfds->getCommandData('sage', 'value'))
                {
                    $thread->content_data['last_bump_time'] = $time['time'];
                    $thread->content_data['last_bump_time_milli'] = $time['milli'];
                }

                $thread->writeToDatabase();
            }

            $post->writeToDatabase();
            $post->createDirectories();
            $fgsfds->modifyCommandData('noko', 'topic', $thread->content_id->thread_id);
            $src_path = $this->board->reference('src_path') . $thread->content_id->thread_id . '/' . $post->content_id->post_id . '/';
            $preview_path = $this->board->reference('thumb_path') . $thread->content_id->thread_id . '/' . $post->content_id->post_id .
            '/';

            // Make thumbnails and do final file processing
            $gen_previews = new GeneratePreviews($this->board);
            $files = $gen_previews->generate($files, $preview_path);
            clearstatcache();

            // Add file data and move uploads to final location if applicable
            if ($spoon)
            {
                $order = 1;

                foreach ($files as $file)
                {
                    $file->content_id->thread_id = $thread->content_id->thread_id;
                    $file->content_data['parent_thread'] = $thread->content_id->thread_id;
                    $file->content_id->post_id = $post->content_id->post_id;
                    $file->content_data['post_ref'] = $post->content_id->post_id;
                    $file->content_id->order_id = $order;
                    $file->content_data['content_order'] = $order;
                    $file_handler->moveFile($file->content_data['location'], $src_path . $file->content_data['fullname'], true,
                            DIRECTORY_PERM);
                    chmod($src_path . $file->content_data['fullname'], octdec(FILE_PERM));
                    $file->writeToDatabase();
                    ++ $order;
                }
            }

            $archive->updateThreads();

            // Generate response page if it doesn't exist, otherwise update
            $regen = new \Nelliel\Regen();
            $regen->threads($this->board, true, array($thread->content_id->thread_id));
            $regen->index($this->board);
            return $thread->content_id->thread_id;
    }

    private function isPostOk($post_data, $time)
    {
        $error_data = array('board_id' => $this->board->id());

        // Check for flood
        // If post is a reply, also check if the thread still exists

        if ($post_data['parent_thread'] === 0) // TODO: Update this, doesn't look right
        {
            $thread_delay = $time - $this->board->setting('thread_delay');
            $prepared = $this->database->prepare(
                    'SELECT COUNT(*) FROM "' . $this->board->reference('post_table') . '" WHERE "post_time" > ? AND "ip_address" = ?');
            $prepared->bindValue(1, $thread_delay, PDO::PARAM_STR);
            $prepared->bindValue(2, @inet_pton($_SERVER['REMOTE_ADDR']), PDO::PARAM_LOB);
            $renzoku = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
        }
        else
        {
            $reply_delay = $time - $this->board->setting('reply_delay');
            $prepared = $this->database->prepare(
                    'SELECT COUNT(*) FROM "' . $this->board->reference('post_table') .
                    '" WHERE "parent_thread" = ? AND "post_time" > ? AND "ip_address" = ?');
            $prepared->bindValue(1, $post_data['parent_thread'], PDO::PARAM_INT);
            $prepared->bindValue(2, $reply_delay, PDO::PARAM_STR);
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
                    'SELECT "post_count", "archive_status", "locked" FROM "' . $this->board->reference('thread_table') .
                    '" WHERE "thread_id" = ? LIMIT 1');
            $thread_info = $this->database->executePreparedFetch($prepared, array($post_data['parent_thread']), PDO::FETCH_ASSOC, true);

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

            if ($thread_info['post_count'] >= $this->board->setting('max_posts'))
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