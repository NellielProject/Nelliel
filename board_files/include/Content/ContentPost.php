<?php

namespace Nelliel\Content;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class ContentPost extends ContentBase
{

    function __construct($database, $content_id, $board_id, $db_load = false)
    {
        $this->database = $database;
        $this->content_id = $content_id;
        $this->board_id = $board_id;

        if($db_load)
        {
            $this->loadFromDatabase();
        }
    }

    public function loadFromDatabase($temp_database = null)
    {
        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $prepared = $database->prepare(
                'SELECT * FROM "' . $board_references['post_table'] . '" WHERE "post_number" = ?');
        $result = $database->executePreparedFetch($prepared, [$this->content_id->post_id], PDO::FETCH_ASSOC);

        if (empty($result))
        {
            return false;
        }

        $this->content_data = $result;
        return true;
    }

    public function writeToDatabase($temp_database = null)
    {
        if (empty($this->content_data) || empty($this->content_id->post_id))
        {
            return false;
        }

        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $prepared = $database->prepare(
                'SELECT "post_number" FROM "' . $board_references['post_table'] . '" WHERE "post_number" = ?');
        $result = $database->executePreparedFetch($prepared, [$this->content_id->post_id], PDO::FETCH_COLUMN);

        if ($result)
        {
            $prepared = $database->prepare(
                    'UPDATE "' . $board_references['post_table'] . '" SET "parent_thread" = :parent_thread,
                    "poster_name" = :poster_name, "post_password" = :post_password,
                    "tripcode" = :tripcode, "secure_tripcode" = :secure_tripcode, "email" = :email,
                    "subject" = :subject, "comment" = :comment, "ip_address" = :ip_address,
                    "post_time" = :post_time, "post_time_milli" = :post_time_milli, "has_file" = :has_file, "file_count" = :file_count,
                    "op" = :op, "sage" = :sage, "mod_post" = :mod_post, "mod_comment" = :mod_comment
                    WHERE "post_number" = :post_number');
            $prepared->bindValue(':post_number', $this->content_id->post_id, PDO::PARAM_INT);
        }
        else
        {
            $prepared = $database->prepare(
                    'INSERT INTO "' . $board_references['post_table'] . '" ("parent_thread", "poster_name", "post_password", "tripcode", "secure_tripcode", "email",
                    "subject", "comment", "ip_address", "post_time", "post_time_milli", "has_file", "file_count", "op", "sage", "mod_post", "mod_comment") VALUES
                    (:parent_thread, :poster_name, :tripcode, :secure_tripcode, :email, :subject, :comment, :ip_address, :post_time, :post_time_milli, :has_file, :file_count,
                    :op, :sage, :mod_post, :mod_comment)');
        }

        $prepared->bindValue(':parent_thread',
                $this->contentDataOrDefault('parent_thread', $this->content_id->thread_id), PDO::PARAM_INT);
        $prepared->bindValue(':poster_name', $this->contentDataOrDefault('poster_name', null), PDO::PARAM_STR);
        $prepared->bindValue(':post_password', $this->contentDataOrDefault('post_password', null), PDO::PARAM_STR);
        $prepared->bindValue(':tripcode', $this->contentDataOrDefault('tripcode', null), PDO::PARAM_STR);
        $prepared->bindValue(':secure_tripcode', $this->contentDataOrDefault('secure_tripcode', null), PDO::PARAM_STR);
        $prepared->bindValue(':email', $this->contentDataOrDefault('email', null), PDO::PARAM_STR);
        $prepared->bindValue(':subject', $this->contentDataOrDefault('subject', null), PDO::PARAM_STR);
        $prepared->bindValue(':comment', $this->contentDataOrDefault('comment', null), PDO::PARAM_STR);
        $prepared->bindValue(':ip_address', $this->contentDataOrDefault('ip_address', null), PDO::PARAM_LOB);
        $prepared->bindValue(':post_time', $this->contentDataOrDefault('post_time', 0), PDO::PARAM_INT);
        $prepared->bindValue(':post_time_milli', $this->contentDataOrDefault('post_time_milli', 0), PDO::PARAM_INT);
        $prepared->bindValue(':has_file', $this->contentDataOrDefault('has_file', 0), PDO::PARAM_INT);
        $prepared->bindValue(':file_count', $this->contentDataOrDefault('file_count', 0), PDO::PARAM_INT);
        $prepared->bindValue(':op', $this->contentDataOrDefault('op', 0), PDO::PARAM_INT);
        $prepared->bindValue(':sage', $this->contentDataOrDefault('sage', 0), PDO::PARAM_INT);
        $prepared->bindValue(':mod_post', $this->contentDataOrDefault('mod_post', null), PDO::PARAM_STR);
        $prepared->bindValue(':mod_comment', $this->contentDataOrDefault('mod_comment', null), PDO::PARAM_STR);
        $database->executePrepared($prepared);
        return true;
    }

    public function reserveDatabaseRow($post_time, $post_time_milli, $temp_database = null)
    {
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $parent_thread = $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $prepared = $database->prepare(
                'INSERT INTO "' . $board_references['post_table'] . '" ("post_time", "post_time_milli") VALUES (?, ?)');
        $database->executePrepared($prepared, [$post_time, $post_time_milli]);
        $prepared = $database->prepare(
                'SELECT "post_number" FROM "' . $board_references['post_table'] .
                '" WHERE "post_time" = ? AND post_time_milli = ? LIMIT 1');
        $result = $database->executePreparedFetch($prepared, [$post_time, $post_time_milli],
                PDO::FETCH_COLUMN, true);
        $this->content_id->thread_id = ($this->content_id->thread_id == 0) ? $result : $this->content_id->thread_id;
        $this->content_data['parent_thread'] = ($this->content_data['parent_thread'] == 0) ? $result : $this->content_data['parent_thread'];
        $this->content_id->post_id = $result;
    }

    public function createDirectories()
    {
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $file_handler = new \Nelliel\FileHandler();
        $file_handler->createDirectory(
                $board_references['src_path'] . $this->content_id->thread_id . '/' . $this->content_id->post_id,
                DIRECTORY_PERM);
        $file_handler->createDirectory(
                $board_references['thumb_path'] . $this->content_id->thread_id . '/' . $this->content_id->post_id,
                DIRECTORY_PERM);
    }

    public function remove($perm_override = false)
    {
        if (!$perm_override && !$this->verifyModifyPerms())
        {
            return false;
        }

        $this->removeFromDatabase();
        $this->removeFromDisk();
        $thread = new ContentThread($this->database, $this->content_id, $this->board_id);
        $thread->updateCounts();
        return true;
    }

    public function removeFromDatabase($temp_database = null)
    {
        if (empty($this->content_id->post_id))
        {
            return false;
        }

        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $prepared = $database->prepare('DELETE FROM "' . $board_references['post_table'] . '" WHERE "post_number" = ?');
        $database->executePrepared($prepared, [$this->content_id->post_id]);
        return true;
    }

    public function removeFromDisk()
    {
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $file_handler = new \Nelliel\FileHandler();
        $file_handler->eraserGun(
                $board_references['src_path'] . $this->content_id->thread_id . '/' . $this->content_id->post_id, null,
                true);
        $file_handler->eraserGun(
                $board_references['thumb_path'] . $this->content_id->thread_id . '/' . $this->content_id->post_id, null,
                true);
    }

    public function updateCounts()
    {
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $prepared = $this->database->prepare(
                'SELECT COUNT("entry") FROM "' . $board_references['content_table'] . '" WHERE "post_ref" = ?');
        $file_count = $this->database->executePreparedFetch($prepared, array($this->content_id->post_id),
                PDO::FETCH_COLUMN, true);

        $prepared = $this->database->prepare(
                'UPDATE "' . $board_references['post_table'] . '" SET "file_count" = ? WHERE "post_number" = ?');
        $this->database->executePrepared($prepared, array($file_count, $this->content_id->post_id));
    }

    public function verifyModifyPerms()
    {
        $session = new \Nelliel\Session(new \Nelliel\Auth\Authorization($this->database));
        $user = $session->sessionUser();

        if (empty($this->content_data))
        {
            $this->loadFromDatabase();
        }

        $flag = false;

        if (!empty($this->content_data['mod_post']) && $session->isActive())
        {
            $mod_post_user = $authorization->getUser($this->content_data['mod_post']);
            $flag = $authorization->roleLevelCheck($user->boardRole($this->board_id),
                    $mod_post_user->boardRole($this->board_id));
        }
        else
        {
            if($session->isActive())
            {
                if ($user->boardPerm($this->board_id, 'perm_post_delete'))
                {
                    $flag = true;
                }
            }
        }

        if (!$flag)
        {
            if (!nel_verify_salted_hash($_POST['update_sekrit'], $this->content_data['post_password']))
            {
                nel_derp(50, _gettext('Password is wrong or you are not allowed to delete that.'));
            }
        }

        return true;
    }

    public function convertToThread()
    {
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $time = nel_get_microtime();
        $new_content_id = new \Nelliel\ContentID();
        $new_content_id->thread_id = $this->content_id->post_id;
        $new_content_id->post_id = $this->content_id->post_id;
        $new_thread = new ContentThread($this->database, $new_content_id, $this->board_id);
        $new_thread->content_data['thread_id'] = $this->content_id->post_id;
        $new_thread->content_data['first_post'] = $this->content_id->post_id;
        $new_thread->content_data['last_post'] = $this->content_id->post_id;
        $new_thread->content_data['last_bump_time'] = $time['time'];
        $new_thread->content_data['last_bump_time_milli'] = $time['milli'];
        $new_thread->content_data['last_update'] = $time['time'];
        $new_thread->content_data['last_update_milli'] = $time['milli'];
        $new_thread->writeToDatabase();
        $new_thread->loadFromDatabase();
        $file_handler = new \Nelliel\FileHandler();
        $new_thread->createDirectories();
        $file_handler->moveDirectory(
                $board_references['src_path'] . $this->content_id->thread_id . '/' . $this->content_id->post_id,
                $board_references['src_path'] . '/' . $new_thread->content_id->thread_id . '/' .
                $this->content_id->post_id, true);
        $file_handler->moveDirectory(
                $board_references['thumb_path'] . $this->content_id->thread_id . '/' . $this->content_id->post_id,
                $board_references['thumb_path'] . '/' . $new_thread->content_id->thread_id . '/' .
                $this->content_id->post_id, true);

        $prepared = $this->database->prepare(
                'SELECT entry FROM "' . $board_references['content_table'] . '" WHERE "post_ref" = ?');
        $files = $this->database->executePreparedFetchAll($prepared, array($this->content_id->post_id), PDO::FETCH_ASSOC);

        foreach ($files as $file)
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . $board_references['content_table'] . '" SET "parent_thread" = ? WHERE "post_ref" = ?');
            $this->database->executePrepared($prepared,
                    [$new_thread->content_id->thread_id, $this->content_id->post_id]);
        }

        $this->loadFromDatabase();
        $this->content_id->thread_id = $new_thread->content_id->thread_id;
        $this->content_data['parent_thread'] = $new_thread->content_id->thread_id;
        $this->content_data['op'] = 1;
        $this->writeToDatabase();
        $new_thread->updateCounts();
    }
}