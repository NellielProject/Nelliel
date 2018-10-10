<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class ContentThread extends ContentBase
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
                'SELECT * FROM "' . $board_references['thread_table'] . '" WHERE "thread_id" = ?');
        $result = $database->executePreparedFetch($prepared, [$this->content_id->thread_id], PDO::FETCH_ASSOC);

        if (empty($result))
        {
            return false;
        }

        $this->content_data = $result;
        return true;
    }

    public function removeFromDatabase($temp_database = null)
    {
        if (empty($this->content_id->thread_id))
        {
            return false;
        }

        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $prepared = $database->prepare('DELETE FROM "' . $board_references['thread_table'] . '" WHERE "thread_id" = ?');
        $database->executePrepared($prepared, [$this->content_id->thread_id]);
        return true;
    }

    public function writeToDatabase($temp_database = null)
    {
        if (empty($this->content_data) || empty($this->content_id->thread_id))
        {
            return false;
        }

        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $prepared = $database->prepare(
                'SELECT "thread_id" FROM "' . $board_references['thread_table'] . '" WHERE "thread_id" = ?');
        $result = $database->executePreparedFetch($prepared, [$this->content_id->thread_id], PDO::FETCH_COLUMN);

        if ($result)
        {
            $prepared = $database->prepare(
                    'UPDATE "' . $board_references['thread_table'] . '" SET "first_post" = :first_post,
                    "last_post" = :last_post, "last_bump_time" = :last_bump_time,
                    "total_files" = :total_files, "last_update" = :last_update, "post_count" = :post_count,
                    "thread_sage" = :thread_sage, "sticky" = :sticky, "archive_status" = :archive_status,
                    "locked" = :locked WHERE "thread_id" = :thread_id');
        }
        else
        {
            $prepared = $database->prepare(
                    'INSERT INTO "' . $board_references['thread_table'] . '" ("thread_id", "first_post", "last_post", "last_bump_time", "total_files", "last_update",
                    "post_count", "thread_sage", "sticky", "archive_status", "locked") VALUES
                    (:thread_id, :first_post, :last_post, :last_bump_time, :total_files, :last_update, :post_count,
                    :thread_sage, :sticky, :archive_status, :locked)');
        }

        $prepared->bindValue(':thread_id', $this->content_id->thread_id, PDO::PARAM_INT);
        $prepared->bindValue(':first_post', $this->contentDataOrDefault('first_post', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_post', $this->contentDataOrDefault('last_post', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_bump_time', $this->contentDataOrDefault('last_bump_time', 0), PDO::PARAM_INT);
        $prepared->bindValue(':total_files', $this->contentDataOrDefault('total_files', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_update', $this->contentDataOrDefault('last_update', 0), PDO::PARAM_INT);
        $prepared->bindValue(':post_count', $this->contentDataOrDefault('post_count', 0), PDO::PARAM_INT);
        $prepared->bindValue(':thread_sage', $this->contentDataOrDefault('thread_sage', 0), PDO::PARAM_INT);
        $prepared->bindValue(':sticky', $this->contentDataOrDefault('sticky', 0), PDO::PARAM_INT);
        $prepared->bindValue(':archive_status', $this->contentDataOrDefault('archive_status', 0), PDO::PARAM_INT);
        $prepared->bindValue(':locked', $this->contentDataOrDefault('locked', 0), PDO::PARAM_INT);
        $database->executePrepared($prepared);
        return true;
    }

    public function createDirectories()
    {
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $file_handler = new \Nelliel\FileHandler();
        $file_handler->createDirectory($board_references['src_path'] . $this->content_id->thread_id, DIRECTORY_PERM);
        $file_handler->createDirectory($board_references['thumb_path'] . $this->content_id->thread_id, DIRECTORY_PERM);
        $file_handler->createDirectory($board_references['page_path'] . $this->content_id->thread_id, DIRECTORY_PERM);
    }

    public function removeDirectories()
    {
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $file_handler = new \Nelliel\FileHandler();
        $file_handler->eraserGun($file_handler->pathJoin($board_references['src_path'], $this->content_id->thread_id),
                null, true);
        $file_handler->eraserGun($file_handler->pathJoin($board_references['thumb_path'], $this->content_id->thread_id),
                null, true);
        $file_handler->eraserGun($file_handler->pathJoin($board_references['page_path'], $this->content_id->thread_id),
                null, true);
    }
}