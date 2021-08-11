<?php
declare(strict_types = 1);

namespace Nelliel\Content;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\ArchiveAndPrune;
use Nelliel\Cites;
use Nelliel\Moar;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPost;
use PDO;
use Nelliel\SQLCompatibility;
use Nelliel\Setup\TablePosts;

class ContentPost extends ContentHandler
{
    protected $archive_prune;

    function __construct(ContentID $content_id, Domain $domain)
    {
        $this->database = $domain->database();
        $this->content_id = $content_id;
        $this->domain = $domain;
        $this->authorization = new Authorization($this->database);
        $this->main_table = new TablePosts($this->database, new SQLCompatibility($this->database));
        $this->archive_prune = new ArchiveAndPrune($this->domain, nel_utilities()->fileHandler());
        $this->storeMoar(new Moar());
    }

    public function loadFromDatabase()
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . $this->domain->reference('posts_table') . '" WHERE "post_number" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->content_id->postID()], PDO::FETCH_ASSOC);

        if (empty($result))
        {
            return false;
        }

        $result['ip_address'] = nel_convert_ip_from_storage($result['ip_address']);
        $result['hashed_ip_address'] = nel_convert_hash_from_storage($result['hashed_ip_address']);
        $column_types = $this->main_table->columnTypes();

        foreach ($result as $name => $value)
        {
            $this->content_data[$name] = nel_cast_to_datatype($value, $column_types[$name]['php_type'] ?? '');
        }

        $moar = $result['moar'] ?? '';
        $this->getMoar()->storeFromJSON($moar);
        return true;
    }

    public function writeToDatabase($temp_database = null)
    {
        if (empty($this->content_data) || empty($this->content_id->postID()))
        {
            return false;
        }

        $prepared = $this->database->prepare(
                'SELECT "post_number" FROM "' . $this->domain->reference('posts_table') . '" WHERE "post_number" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->content_id->postID()], PDO::FETCH_COLUMN);

        if ($result)
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . $this->domain->reference('posts_table') .
                    '" SET "parent_thread" = :parent_thread,
                    "name" = :name, "reply_to" = :reply_to, "post_password" = :post_password,
                    "tripcode" = :tripcode, "secure_tripcode" = :secure_tripcode, "capcode" = :capcode, "email" = :email,
                    "subject" = :subject, "comment" = :comment, "ip_address" = :ip_address, "hashed_ip_address" = :hashed_ip_address,
                    "post_time" = :post_time, "post_time_milli" = :post_time_milli, "has_uploads" = :has_uploads,
                    "total_uploads" = :total_uploads, "file_count" = :file_count, "embed_count" = :embed_count,
                    "op" = :op, "sage" = :sage, "account_id" = :account_id, "mod_comment" = :mod_comment,
                    "content_hash" = :content_hash, "regen_cache" = :regen_cache, "cache" = :cache
                    WHERE "post_number" = :post_number');
            $prepared->bindValue(':post_number', $this->content_id->postID(), PDO::PARAM_INT);
        }
        else
        {
            $prepared = $this->database->prepare(
                    'INSERT INTO "' . $this->domain->reference('posts_table') .
                    '" ("parent_thread", "name", "reply_to", "post_password", "tripcode", "secure_tripcode", "capcode", "email",
                    "subject", "comment", "ip_address", "hashed_ip_address", "post_time", "post_time_milli", "has_uploads",
                    "total_uploads", "file_count", "embed_count", "op", "sage", "account_id", "mod_comment") VALUES
                    (:parent_thread, :name, :tripcode, :secure_tripcode, :capcode, :email, :subject, :comment, :ip_address,
                    :hashed_ip_address, :post_time, :post_time_milli, :has_uploads, :total_uploads, :file_count, :embed_count,
                    :op, :sage, :account_id, :mod_comment, :content_hash, :regen_cache, :cache)');
        }

        $prepared->bindValue(':parent_thread',
                $this->contentDataOrDefault('parent_thread', $this->content_id->threadID()), PDO::PARAM_INT);
        $prepared->bindValue(':reply_to', $this->contentDataOrDefault('reply_to', $this->content_id->threadID()),
                PDO::PARAM_INT);
        $prepared->bindValue(':name', $this->contentDataOrDefault('name', null), PDO::PARAM_STR);
        $prepared->bindValue(':post_password', $this->contentDataOrDefault('post_password', null), PDO::PARAM_STR);
        $prepared->bindValue(':capcode', $this->contentDataOrDefault('capcode', null), PDO::PARAM_STR);
        $prepared->bindValue(':tripcode', $this->contentDataOrDefault('tripcode', null), PDO::PARAM_STR);
        $prepared->bindValue(':secure_tripcode', $this->contentDataOrDefault('secure_tripcode', null), PDO::PARAM_STR);
        $prepared->bindValue(':email', $this->contentDataOrDefault('email', null), PDO::PARAM_STR);
        $prepared->bindValue(':subject', $this->contentDataOrDefault('subject', null), PDO::PARAM_STR);
        $prepared->bindValue(':comment', $this->contentDataOrDefault('comment', null), PDO::PARAM_STR);
        $ip_address = $this->contentDataOrDefault('ip_address', null);
        $prepared->bindValue(':ip_address', nel_prepare_ip_for_storage($ip_address), PDO::PARAM_LOB);
        $prepared->bindValue(':hashed_ip_address',
                nel_prepare_hash_for_storage($this->contentDataOrDefault('hashed_ip_address', null)), PDO::PARAM_LOB);
        $prepared->bindValue(':post_time', $this->contentDataOrDefault('post_time', 0), PDO::PARAM_INT);
        $prepared->bindValue(':post_time_milli', $this->contentDataOrDefault('post_time_milli', 0), PDO::PARAM_INT);
        $prepared->bindValue(':has_uploads', $this->contentDataOrDefault('has_uploads', 0), PDO::PARAM_INT);
        $prepared->bindValue(':total_uploads', $this->contentDataOrDefault('total_uploads', 0), PDO::PARAM_INT);
        $prepared->bindValue(':file_count', $this->contentDataOrDefault('file_count', 0), PDO::PARAM_INT);
        $prepared->bindValue(':embed_count', $this->contentDataOrDefault('embed_count', 0), PDO::PARAM_INT);
        $prepared->bindValue(':op', $this->contentDataOrDefault('op', 0), PDO::PARAM_INT);
        $prepared->bindValue(':sage', $this->contentDataOrDefault('sage', 0), PDO::PARAM_INT);
        $prepared->bindValue(':account_id', $this->contentDataOrDefault('account_id', null), PDO::PARAM_STR);
        $prepared->bindValue(':mod_comment', $this->contentDataOrDefault('mod_comment', null), PDO::PARAM_STR);
        $prepared->bindValue(':content_hash', $this->contentDataOrDefault('content_hash', null), PDO::PARAM_STR);
        $prepared->bindValue(':regen_cache', $this->contentDataOrDefault('regen_cache', 0), PDO::PARAM_INT);
        $prepared->bindValue(':cache', $this->contentDataOrDefault('cache', ''), PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
        $this->archive_prune->updateThreads();
        return true;
    }

    public function remove(bool $perm_override = false)
    {
        if (!$perm_override)
        {
            if (!$this->verifyModifyPerms())
            {
                return false;
            }

            if ($this->domain->reference('locked'))
            {
                nel_derp(62, _gettext('Cannot remove post. Board is locked.'));
            }

            $session = new \Nelliel\Account\Session();
            $user = $session->user();
            $bypass = false;

            if ($user && $session->user()->checkPermission($this->domain, 'perm_bypass_renzoku'))
            {
                $bypass = true;
            }

            $delete_post_renzoku = $this->domain->setting('delete_post_renzoku');

            if (!$bypass && $delete_post_renzoku > 0 && time() - $this->content_data['post_time'] < $delete_post_renzoku)
            {
                nel_derp(64,
                        sprintf(_gettext('You must wait %d seconds after making a post before it can be deleted.'),
                                $delete_post_renzoku));
            }
        }

        $this->removeFromDatabase();
        $this->removeFromDisk();
        $thread = new ContentThread($this->content_id, $this->domain);

        // TODO: This is a (hopefully) temporary thing to keep normal imageboard function when deleting OP post
        if ($thread->postCount() <= 0 || $this->content_id->threadID() == $this->content_id->postID())
        {
            $thread->remove(true);
        }
        else
        {
            $thread->updateCounts();
        }

        $this->archive_prune->updateThreads();
        return true;
    }

    protected function removeFromDatabase()
    {
        if (empty($this->content_id->postID()))
        {
            return false;
        }

        $prepared = $this->database->prepare(
                'DELETE FROM "' . $this->domain->reference('posts_table') . '" WHERE "post_number" = ?');
        $this->database->executePrepared($prepared, [$this->content_id->postID()]);
        $cites = new Cites($this->database);
        $cites->updateForPost($this);
        $cites->removeForPost($this);
        return true;
    }

    protected function removeFromDisk()
    {
        $file_handler = nel_utilities()->fileHandler();
        $file_handler->eraserGun($this->srcPath());
        $file_handler->eraserGun($this->previewPath());
    }

    protected function verifyModifyPerms()
    {
        $session = new \Nelliel\Account\Session();
        $user = $session->user();

        if (empty($this->content_data))
        {
            $this->loadFromDatabase();
        }

        $flag = false;

        if ($session->isActive())
        {
            if ($user->checkPermission($this->domain, 'perm_delete_posts'))
            {
                if (!$user->isSiteOwner() && !empty($this->content_data['account_id']) &&
                        $this->authorization->userExists($this->content_data['account_id']))
                {
                    $mod_post_user = $this->authorization->getUser($this->content_data['account_id']);
                    $flag = $this->authorization->roleLevelCheck($user->getDomainRole($this->domain)->id(),
                            $mod_post_user->getDomainRole($this->domain)->id());
                }
                else
                {
                    $flag = true;
                }
            }
        }

        $update_sekrit = $_POST['update_sekrit'] ?? '';

        if (!$flag)
        {
            if (!isset($this->content_data['post_password']) ||
                    !hash_equals($this->content_data['post_password'], nel_post_password_hash($update_sekrit)) ||
                    !$this->domain->setting('user_delete_own'))
            {
                nel_derp(60, _gettext('Password is wrong or you are not allowed to delete that.'));
            }
        }

        return true;
    }

    public function getParent()
    {
        $content_id = new \Nelliel\Content\ContentID();
        $content_id->changeThreadID($this->content_id->threadID());
        $parent_thread = new ContentThread($content_id, $this->domain);
        return $parent_thread;
    }

    public function reserveDatabaseRow($post_time, $post_time_milli, $hashed_ip_address, $temp_database = null)
    {
        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $prepared = $database->prepare(
                'INSERT INTO "' . $this->domain->reference('posts_table') .
                '" ("post_time", "post_time_milli", "hashed_ip_address") VALUES (?, ?, ?)');
        $database->executePrepared($prepared,
                [$post_time, $post_time_milli, nel_prepare_hash_for_storage($hashed_ip_address)]);
        $prepared = $database->prepare(
                'SELECT "post_number" FROM "' . $this->domain->reference('posts_table') .
                '" WHERE "post_time" = ? AND "post_time_milli" = ? AND "hashed_ip_address" = ?');
        $result = $database->executePreparedFetch($prepared,
                [$post_time, $post_time_milli, nel_prepare_hash_for_storage($hashed_ip_address)], PDO::FETCH_COLUMN,
                true);
        $this->content_id->changeThreadID(
                ($this->content_id->threadID() == 0) ? $result : $this->content_id->threadID());
        $this->content_data['parent_thread'] = $this->content_id->threadID();
        $this->content_id->changePostID($result);
    }

    public function updateCounts()
    {
        $prepared = $this->database->prepare(
                'SELECT COUNT("entry") FROM "' . $this->domain->reference('upload_table') . '" WHERE "post_ref" = ?');
        $total_uploads = $this->database->executePreparedFetch($prepared, [$this->content_id->postID()],
                PDO::FETCH_COLUMN, true);

        $prepared = $this->database->prepare(
                'SELECT COUNT("entry") FROM "' . $this->domain->reference('upload_table') .
                '" WHERE "post_ref" = ? AND "embed_url" IS NULL');
        $file_count = $this->database->executePreparedFetch($prepared, [$this->content_id->postID()], PDO::FETCH_COLUMN,
                true);

        $prepared = $this->database->prepare(
                'SELECT COUNT("entry") FROM "' . $this->domain->reference('upload_table') .
                '" WHERE "post_ref" = ? AND "embed_url" IS NOT NULL');
        $embed_count = $this->database->executePreparedFetch($prepared, [$this->content_id->postID()],
                PDO::FETCH_COLUMN, true);

        $prepared = $this->database->prepare(
                'UPDATE "' . $this->domain->reference('posts_table') .
                '" SET "total_uploads" = ?, "file_count" = ?, "embed_count" = ? WHERE "post_number" = ?');
        $this->database->executePrepared($prepared,
                [$total_uploads, $file_count, $embed_count, $this->content_id->postID()]);
    }

    public function convertToThread()
    {
        $time = nel_get_microtime();
        $new_content_id = new \Nelliel\Content\ContentID();
        $new_content_id->changeThreadID($this->content_id->postID());
        $new_content_id->changePostID($this->content_id->postID());
        $new_thread = new ContentThread($new_content_id, $this->domain);
        $new_thread->content_data['thread_id'] = $this->content_id->postID();
        $new_thread->content_data['last_bump_time'] = $time['time'];
        $new_thread->content_data['last_bump_time_milli'] = $time['milli'];
        $new_thread->content_data['last_update'] = $time['time'];
        $new_thread->content_data['last_update_milli'] = $time['milli'];
        $new_thread->writeToDatabase();
        $new_thread->loadFromDatabase();
        $new_thread->createDirectories();
        $prepared = $this->database->prepare(
                'SELECT entry FROM "' . $this->domain->reference('upload_table') . '" WHERE "post_ref" = ?');
        $prepared = $this->database->prepare(
                'UPDATE "' . $this->domain->reference('upload_table') . '" SET "parent_thread" = ? WHERE "post_ref" = ?');
        $this->database->executePrepared($prepared, [$new_thread->content_id->threadID(), $this->content_id->postID()]);
        $this->loadFromDatabase();
        $this->content_id->changeThreadID($new_thread->content_id->threadID());
        $this->content_data['parent_thread'] = $new_thread->content_id->threadID();
        $this->content_data['op'] = 1;
        $this->writeToDatabase();
        $new_thread->updateCounts();
        $this->archive_prune->updateThreads();
        return $new_thread;
    }

    public function createDirectories()
    {
        $file_handler = nel_utilities()->fileHandler();
        $file_handler->createDirectory($this->srcPath(), NEL_DIRECTORY_PERM, true);
        $file_handler->createDirectory($this->previewPath(), NEL_DIRECTORY_PERM, true);
    }

    public function addCites()
    {
        $cites = new Cites($this->database);

        if (nel_true_empty($this->data('comment')))
        {
            return;
        }

        $cite_list = $cites->getCitesFromText($this->content_data['comment']);

        foreach ($cite_list as $cite)
        {
            $cite_data = $cites->getCiteData($cite, $this->domain, $this->content_id);

            if ($cite_data['exists'] || $cite_data['future'])
            {
                $cites->addCite($cite_data);
            }
        }

        $cites->updateForPost($this);
    }

    public function sticky()
    {
        $new_thread = $this->convertToThread();
        $new_thread->sticky();
        return $new_thread;
    }

    public function getCache(): array
    {
        $prepared = $this->database->prepare(
                'SELECT "cache" FROM "' . $this->domain->reference('posts_table') . '" WHERE "post_number" = ?');
        $cache = $this->database->executePreparedFetch($prepared, [$this->content_id->postID()],
                PDO::FETCH_COLUMN);

        if (is_string($cache))
        {
            return json_decode($cache, true);
        }

        return array();
    }

    public function storeCache(): void
    {
        $cache_array = array();
        $output_post = new OutputPost($this->domain, false);
        $cache_array['comment_data'] = $output_post->parseComment($this->data('comment'), $this->content_id);
        $cache_array['backlink_data'] = $output_post->generateBacklinks($this);
        $encoded_cache = json_encode($cache_array, JSON_UNESCAPED_UNICODE);
        $prepared = $this->database->prepare(
                'UPDATE "' . $this->domain->reference('posts_table') .
                '" SET "cache" = ?, "regen_cache" = 0 WHERE "post_number" = ?');
        $this->database->executePrepared($prepared, [$encoded_cache, $this->content_id->postID()]);
    }

    public function srcPath(): string
    {
        return $this->domain->reference('src_path') . $this->content_id->threadID() . '/' . $this->content_id->postID() .
                '/';
    }

    public function previewPath(): string
    {
        return $this->domain->reference('preview_path') . $this->content_id->threadID() . '/' .
                $this->content_id->postID() . '/';
    }
}