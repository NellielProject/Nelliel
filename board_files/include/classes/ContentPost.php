<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class ContentPost extends ContentBase
{

    function __construct($database, $content_id, $board_id)
    {
        $this->database = $database;
        $this->content_id = $content_id;
        $this->board_id = $board_id;
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
                    "post_time" = :post_time, "has_file" = :has_file, "file_count" = :file_count,
                    "op" = :op, "sage" = :sage, "mod_post" = :mod_post, "mod_comment" = :mod_comment
                    WHERE "post_number" = :post_number');
            $prepared->bindValue(':post_number', $this->content_id->post_id, PDO::PARAM_INT);
        }
        else
        {
            $prepared = $database->prepare(
                    'INSERT INTO "' . $board_references['post_table'] . '" ("parent_thread", "poster_name", "post_password", "tripcode", "secure_tripcode", "email",
                    "subject", "comment", "ip_address", "post_time", "has_file", "file_count", "op", "sage", "mod_post", "mod_comment") VALUES
                    (:parent_thread, :poster_name, :tripcode, :secure_tripcode, :email, :subject, :comment, :ip_address, :post_time, :has_file, :file_count,
                    :op, :sage, :mod_post, :mod_comment)');
        }

        $prepared->bindValue(':parent_thread', $this->contentDataOrDefault('parent_thread', null), PDO::PARAM_INT);
        $prepared->bindValue(':poster_name', $this->contentDataOrDefault('poster_name', null), PDO::PARAM_STR);
        $prepared->bindValue(':post_password', $this->contentDataOrDefault('password', null), PDO::PARAM_STR);
        $prepared->bindValue(':tripcode', $this->contentDataOrDefault('tripcode', null), PDO::PARAM_STR);
        $prepared->bindValue(':secure_tripcode', $this->contentDataOrDefault('secure_tripcode', null), PDO::PARAM_STR);
        $prepared->bindValue(':email', $this->contentDataOrDefault('email', null), PDO::PARAM_STR);
        $prepared->bindValue(':subject', $this->contentDataOrDefault('subject', null), PDO::PARAM_STR);
        $prepared->bindValue(':comment', $this->contentDataOrDefault('comment', null), PDO::PARAM_STR);
        $prepared->bindValue(':ip_address', $this->contentDataOrDefault('ip_address', null), PDO::PARAM_LOB);
        $prepared->bindValue(':post_time', $this->contentDataOrDefault('post_time', 0), PDO::PARAM_INT);
        $prepared->bindValue(':has_file', $this->contentDataOrDefault('has_file', 0), PDO::PARAM_INT);
        $prepared->bindValue(':file_count', $this->contentDataOrDefault('file_count', 0), PDO::PARAM_INT);
        $prepared->bindValue(':op', $this->contentDataOrDefault('op', 0), PDO::PARAM_INT);
        $prepared->bindValue(':sage', $this->contentDataOrDefault('sage', 0), PDO::PARAM_INT);
        $prepared->bindValue(':mod_post', $this->contentDataOrDefault('mod_post', null), PDO::PARAM_STR);
        $prepared->bindValue(':mod_comment', $this->contentDataOrDefault('mod_comment', null), PDO::PARAM_STR);
        $database->executePrepared($prepared);
        return true;
    }

    public function reserveDatabaseRow($post_time, $temp_database = null)
    {
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);

        $parent_thread = $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $prepared = $database->prepare('INSERT INTO "' . $board_references['post_table'] . '" ("post_time") VALUES (?)');
        $database->executePrepared($prepared, [$post_time]);
        $prepared = $database->prepare(
                'SELECT "post_number" FROM "' . $board_references['post_table'] . '" WHERE "post_time" = ? LIMIT 1');
        $result = $database->executePreparedFetch($prepared, [$post_time], PDO::FETCH_COLUMN, true);
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

    public function remove()
    {
        $this->removeFromDatabase();
        $this->removeFromDisk();
        $thread = new \Nelliel\ContentThread($this->database, $this->content_id, $this->board_id);
        $thread->updateCounts();
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
        $file_handler->eraserGun($board_references['src_path'],
                $this->content_id->thread_id . '/' . $this->content_id->post_id, true);
        $file_handler->eraserGun($board_references['thumb_path'],
                $this->content_id->thread_id . '/' . $this->content_id->post_id, true);
    }

    public function updateCounts()
    {
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $prepared = $this->database->prepare(
                'SELECT COUNT("entry") FROM "' . $board_references['file_table'] . '" WHERE "post_ref" = ?');
        $file_count = $this->database->executePreparedFetch($prepared, array($this->content_id->post_id),
                PDO::FETCH_COLUMN, true);

        $prepared = $this->database->prepare(
                'UPDATE "' . $board_references['post_table'] . '" SET "file_count" = ? WHERE "post_number" = ?');
        $this->database->executePrepared($prepared, array($file_count, $this->content_id->post_id));
    }
}