<?php

namespace Nelliel\Content;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class ContentPost extends ContentHandler
{
    protected $posts_table;
    protected $content_table;
    protected $src_path;
    protected $preview_path;
    protected $archived;

    function __construct(ContentID $content_id, Domain $domain, bool $archived = false)
    {
        $this->database = $domain->database();
        $this->content_id = $content_id;
        $this->domain = $domain;
        $this->archived = $archived;
        $this->posts_table = $this->domain->reference('posts_table');
        $this->content_table = $this->domain->reference('content_table');

        if ($archived)
        {
            $this->src_path = $this->domain->reference('archive_src_path');
            $this->preview_path = $this->domain->reference('archive_preview_path');
        }
        else
        {
            $this->src_path = $this->domain->reference('src_path');
            $this->preview_path = $this->domain->reference('preview_path');
        }

        $this->storeMeta(new Meta());
    }

    public function loadFromDatabase()
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . $this->posts_table . '" WHERE "post_number" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->content_id->postID()], PDO::FETCH_ASSOC);

        if (empty($result))
        {
            return false;
        }

        $this->content_data = $result;
        $meta = $result['meta'] ?? '';
        $this->getMeta()->storeFromJSON($meta);
        return true;
    }

    public function writeToDatabase($temp_database = null)
    {
        if (empty($this->content_data) || empty($this->content_id->postID()))
        {
            return false;
        }

        $prepared = $this->database->prepare('SELECT "post_number" FROM "' . $this->posts_table . '" WHERE "post_number" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->content_id->postID()], PDO::FETCH_COLUMN);

        if ($result)
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . $this->posts_table .
                    '" SET "parent_thread" = :parent_thread,
                    "poster_name" = :poster_name, "reply_to" = :reply_to, "post_password" = :post_password,
                    "tripcode" = :tripcode, "secure_tripcode" = :secure_tripcode, "email" = :email,
                    "subject" = :subject, "comment" = :comment, "ip_address" = :ip_address,
                    "post_time" = :post_time, "post_time_milli" = :post_time_milli, "has_content" = :has_content, "content_count" = :content_count,
                    "op" = :op, "sage" = :sage, "mod_post_id" = :mod_post_id, "mod_comment" = :mod_comment
                    WHERE "post_number" = :post_number');
            $prepared->bindValue(':post_number', $this->content_id->postID(), PDO::PARAM_INT);
        }
        else
        {
            $prepared = $this->database->prepare(
                    'INSERT INTO "' . $this->posts_table .
                    '" ("parent_thread", "poster_name", "reply_to", "post_password", "tripcode", "secure_tripcode", "email",
                    "subject", "comment", "ip_address", "post_time", "post_time_milli", "has_content", "content_count", "op", "sage", "mod_post_id", "mod_comment") VALUES
                    (:parent_thread, :poster_name, :tripcode, :secure_tripcode, :email, :subject, :comment, :ip_address, :post_time, :post_time_milli, :has_content, :content_count,
                    :op, :sage, :mod_post_id, :mod_comment)');
        }

        $prepared->bindValue(':parent_thread',
                $this->contentDataOrDefault('parent_thread', $this->content_id->threadID()), PDO::PARAM_INT);
        $prepared->bindValue(':reply_to', $this->contentDataOrDefault('reply_to', $this->content_id->threadID()),
                PDO::PARAM_INT);
        $prepared->bindValue(':poster_name', $this->contentDataOrDefault('poster_name', null), PDO::PARAM_STR);
        $prepared->bindValue(':post_password', $this->contentDataOrDefault('post_password', null), PDO::PARAM_STR);
        $prepared->bindValue(':tripcode', $this->contentDataOrDefault('tripcode', null), PDO::PARAM_STR);
        $prepared->bindValue(':secure_tripcode', $this->contentDataOrDefault('secure_tripcode', null), PDO::PARAM_STR);
        $prepared->bindValue(':email', $this->contentDataOrDefault('email', null), PDO::PARAM_STR);
        $prepared->bindValue(':subject', $this->contentDataOrDefault('subject', null), PDO::PARAM_STR);
        $prepared->bindValue(':comment', $this->contentDataOrDefault('comment', null), PDO::PARAM_STR);
        $prepared->bindValue(':ip_address', $this->contentDataOrDefault('ip_address', '0.0.0.0'), PDO::PARAM_LOB);
        $prepared->bindValue(':post_time', $this->contentDataOrDefault('post_time', 0), PDO::PARAM_INT);
        $prepared->bindValue(':post_time_milli', $this->contentDataOrDefault('post_time_milli', 0), PDO::PARAM_INT);
        $prepared->bindValue(':has_content', $this->contentDataOrDefault('has_content', 0), PDO::PARAM_INT);
        $prepared->bindValue(':content_count', $this->contentDataOrDefault('content_count', 0), PDO::PARAM_INT);
        $prepared->bindValue(':op', $this->contentDataOrDefault('op', 0), PDO::PARAM_INT);
        $prepared->bindValue(':sage', $this->contentDataOrDefault('sage', 0), PDO::PARAM_INT);
        $prepared->bindValue(':mod_post_id', $this->contentDataOrDefault('mod_post_id', null), PDO::PARAM_STR);
        $prepared->bindValue(':mod_comment', $this->contentDataOrDefault('mod_comment', null), PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
        return true;
    }

    public function reserveDatabaseRow($post_time, $post_time_milli, $ip_address, $temp_database = null)
    {
        $parent_thread = $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $prepared = $database->prepare(
                'INSERT INTO "' . $this->posts_table .
                '" ("post_time", "post_time_milli", "ip_address") VALUES (?, ?, ?)');
        $database->executePrepared($prepared, [$post_time, $post_time_milli, @inet_pton($ip_address)]);
        $prepared = $database->prepare(
                'SELECT "post_number" FROM "' . $this->posts_table .
                '" WHERE "post_time" = ? AND "post_time_milli" = ? AND "ip_address" = ?');
        $result = $database->executePreparedFetch($prepared, [$post_time, $post_time_milli, @inet_pton($ip_address)],
                PDO::FETCH_COLUMN, true);
        $this->content_id->changeThreadID(
                ($this->content_id->threadID() == 0) ? $result : $this->content_id->threadID());
        $this->content_data['parent_thread'] = ($this->content_data['parent_thread'] == 0) ? $result : $this->content_data['parent_thread'];
        $this->content_id->changePostID($result);
    }

    public function createDirectories()
    {
        $file_handler = new \Nelliel\Utility\FileHandler();
        $file_handler->createDirectory(
                $this->src_path . $this->content_id->threadID() . '/' . $this->content_id->postID(), NEL_DIRECTORY_PERM);
        $file_handler->createDirectory(
                $this->preview_path . $this->content_id->threadID() . '/' . $this->content_id->postID(),
                NEL_DIRECTORY_PERM);
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
                nel_derp(52, _gettext('Cannot remove post. Board is locked.'));
            }
        }

        $this->removeFromDatabase();
        $this->removeFromDisk();

        $query = 'SELECT "entry" FROM "' . $this->content_table . '" WHERE "post_ref" = ?';
        $prepared = $this->database->prepare($query);
        $content_entries = $this->database->executePreparedFetchAll($prepared, [$this->content_id->postID()],
                PDO::FETCH_COLUMN);

        foreach ($content_entries as $entry)
        {
            $content = new ContentFile($this->content_id, $this->domain, $this->archived);
            $content->remove();
        }

        $thread = new ContentThread($this->content_id, $this->domain, $this->archived);

        if ($thread->postCount() <= 0)
        {
            $thread->remove(true);
        }
        else
        {
            $thread->updateCounts();
        }

        return true;
    }

    protected function removeFromDatabase()
    {
        if (empty($this->content_id->postID()))
        {
            return false;
        }

        $prepared = $this->database->prepare('DELETE FROM "' . $this->posts_table . '" WHERE "post_number" = ?');
        $this->database->executePrepared($prepared, [$this->content_id->postID()]);
        $prepared = $this->database->prepare(
                'DELETE FROM "' . NEL_CITES_TABLE . '" WHERE "source_post" = ? OR "target_post" = ?');
        $this->database->executePrepared($prepared, [$this->content_id->postID(), $this->content_id->postID()]);
        return true;
    }

    protected function removeFromDisk()
    {
        $file_handler = new \Nelliel\Utility\FileHandler();
        $file_handler->eraserGun($this->src_path . $this->content_id->threadID() . '/' . $this->content_id->postID());
        $file_handler->eraserGun(
                $this->preview_path . $this->content_id->threadID() . '/' . $this->content_id->postID());
    }

    public function updateCounts()
    {
        $prepared = $this->database->prepare(
                'SELECT COUNT("entry") FROM "' . $this->content_table . '" WHERE "post_ref" = ?');
        $content_count = $this->database->executePreparedFetch($prepared, [$this->content_id->postID()],
                PDO::FETCH_COLUMN, true);

        $prepared = $this->database->prepare(
                'UPDATE "' . $this->posts_table . '" SET "content_count" = ? WHERE "post_number" = ?');
        $this->database->executePrepared($prepared, [$content_count, $this->content_id->postID()]);
    }

    public function verifyModifyPerms()
    {
        $session = new \Nelliel\Account\Session();
        $user = $session->sessionUser();

        if (empty($this->content_data))
        {
            $this->loadFromDatabase();
        }

        $flag = false;

        if ($session->isActive())
        {
            if ($user->checkPermission($this->domain, 'perm_board_modify_posts'))
            {
                if (!empty($this->content_data['mod_post_id']))
                {
                    $mod_post_user = $authorization->getUser($this->content_data['mod_post_id']);
                    $flag = $authorization->roleLevelCheck($user->checkRole($this->domain),
                            $mod_post_user->checkRole($this->domain));
                }
                else
                {
                    $flag = true;
                }
            }
        }

        if (!$flag)
        {
            if (!isset($this->content_data['post_password']) ||
                    !nel_verify_salted_hash(NEL_POST_PASSWORD_PEPPER . $_POST['update_sekrit'],
                            $this->content_data['post_password']) || !$this->domain->setting('user_delete_own'))
            {
                nel_derp(50, _gettext('Password is wrong or you are not allowed to delete that.'));
            }
        }

        return true;
    }

    public function convertToThread()
    {
        $time = nel_get_microtime();
        $new_content_id = new \Nelliel\Content\ContentID();
        $new_content_id->changeThreadID($this->content_id->postID());
        $new_content_id->changePostID($this->content_id->postID());
        $new_thread = new ContentThread($new_content_id, $this->domain);
        $new_thread->content_data['thread_id'] = $this->content_id->postID();
        $new_thread->content_data['first_post'] = $this->content_id->postID();
        $new_thread->content_data['last_post'] = $this->content_id->postID();
        $new_thread->content_data['last_bump_time'] = $time['time'];
        $new_thread->content_data['last_bump_time_milli'] = $time['milli'];
        $new_thread->content_data['last_update'] = $time['time'];
        $new_thread->content_data['last_update_milli'] = $time['milli'];
        $new_thread->writeToDatabase();
        $new_thread->loadFromDatabase();
        $file_handler = new \Nelliel\Utility\FileHandler();
        $new_thread->createDirectories();
        $file_handler->moveDirectory(
                $this->src_path . $this->content_id->threadID() . '/' . $this->content_id->postID(),
                $this->src_path . '/' . $new_thread->content_id->threadID() . '/' . $this->content_id->postID(), true);
        $file_handler->moveDirectory(
                $this->preview_path . $this->content_id->threadID() . '/' . $this->content_id->postID(),
                $this->preview_path . '/' . $new_thread->content_id->threadID() . '/' . $this->content_id->postID(),
                true);

        $prepared = $this->database->prepare('SELECT entry FROM "' . $this->content_table . '" WHERE "post_ref" = ?');
        $files = $this->database->executePreparedFetchAll($prepared, [$this->content_id->postID()], PDO::FETCH_ASSOC);

        foreach ($files as $file)
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . $this->content_table . '" SET "parent_thread" = ? WHERE "post_ref" = ?');
            $this->database->executePrepared($prepared,
                    [$new_thread->content_id->threadID(), $this->content_id->postID()]);
        }

        $this->loadFromDatabase();
        $this->content_id->changeThreadID($new_thread->content_id->threadID());
        $this->content_data['parent_thread'] = $new_thread->content_id->threadID();
        $this->content_data['op'] = 1;
        $this->writeToDatabase();
        $new_thread->updateCounts();
        return $new_thread;
    }

    public function isArchived()
    {
        return $this->archived;
    }

    public function sticky()
    {
        $new_thread = $this->convertToThread();
        $new_thread->sticky();
    }
}