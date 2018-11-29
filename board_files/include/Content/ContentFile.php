<?php

namespace Nelliel\Content;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class ContentFile extends ContentBase
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
                'SELECT * FROM "' . $board_references['content_table'] . '" WHERE "post_ref" = ? AND "content_order" = ?');
        $result = $database->executePreparedFetch($prepared,
                [$this->content_id->post_id, $this->content_id->order_id], PDO::FETCH_ASSOC);

        if (empty($result))
        {
            return false;
        }

        $this->content_data = $result;
        return true;
    }

    public function writeToDatabase($temp_database = null)
    {
        if (empty($this->content_data) || empty($this->content_id->order_id))
        {
            return false;
        }

        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $prepared = $database->prepare(
                'SELECT "entry" FROM "' . $board_references['content_table'] . '" WHERE "post_ref" = ? AND "content_order" = ?');
        $result = $database->executePreparedFetch($prepared,
                [$this->content_id->post_id, $this->content_id->order_id], PDO::FETCH_COLUMN);

        if ($result)
        {
            $prepared = $database->prepare(
                    'UPDATE "' . $board_references['content_table'] . '" SET "parent_thread" = :parent_thread,
                    "post_ref" = :post_ref, "content_order" = :content_order,
                    "type" = :type, "format" = :format, "mime" = :mime,
                    "filename" = :filename, "extension" = :extension,
                    "display_width" = :display_width, "display_height" = :display_height, "preview_name" = :preview_name,
                    "preview_extension" = :preview_extension, "preview_width" = :preview_width, "preview_height" = :preview_height,
                    "filesize" = :filesize, "md5" = :md5, "sha1" = :sha1, "sha256" = :sha256, "sha512" = :sha512,
                    "source" = :source, "license" = :license, "alt_text" = :alt_text, "exif" = :exif
                    WHERE "post_number" = :post_number');
            $prepared->bindValue(':post_number', $this->content_id->post_id, PDO::PARAM_INT);
        }
        else
        {
            $prepared = $database->prepare(
                    'INSERT INTO "' . $board_references['content_table'] . '" ("parent_thread", "post_ref", "content_order", "type", "format", "mime",
                    "filename", "extension", "display_width", "display_height", "preview_name", "preview_extension", "preview_width", "preview_height",
                    "filesize", "md5", "sha1", "sha256", "sha512", "source", "license", "alt_text", "exif") VALUES
                    (:parent_thread, :post_ref, :content_order, :type, :format, :mime, :filename, :extension, :display_width, :display_height,
                    :preview_name, :preview_extension, :preview_width, :preview_height, :filesize, :md5, :sha1, :sha256, :sha512,
                    :source, :license, :alt_text, :exif)');
        }

        $prepared->bindValue(':parent_thread', $this->contentDataOrDefault('parent_thread', 0), PDO::PARAM_INT);
        $prepared->bindValue(':post_ref', $this->contentDataOrDefault('post_ref', null), PDO::PARAM_INT);
        $prepared->bindValue(':content_order', $this->contentDataOrDefault('content_order', 1), PDO::PARAM_INT);
        $prepared->bindValue(':type', $this->contentDataOrDefault('type', null), PDO::PARAM_STR);
        $prepared->bindValue(':format', $this->contentDataOrDefault('format', null), PDO::PARAM_STR);
        $prepared->bindValue(':mime', $this->contentDataOrDefault('mime', null), PDO::PARAM_STR);
        $prepared->bindValue(':filename', $this->contentDataOrDefault('filename', null), PDO::PARAM_STR);
        $prepared->bindValue(':extension', $this->contentDataOrDefault('extension', null), PDO::PARAM_STR);
        $prepared->bindValue(':display_width', $this->contentDataOrDefault('display_width', null), PDO::PARAM_INT);
        $prepared->bindValue(':display_height', $this->contentDataOrDefault('display_height', null), PDO::PARAM_INT);
        $prepared->bindValue(':preview_name', $this->contentDataOrDefault('preview_name', null), PDO::PARAM_STR);
        $prepared->bindValue(':preview_extension', $this->contentDataOrDefault('preview_extension', null),
                PDO::PARAM_STR);
        $prepared->bindValue(':preview_width', $this->contentDataOrDefault('preview_width', null), PDO::PARAM_INT);
        $prepared->bindValue(':preview_height', $this->contentDataOrDefault('preview_height', null), PDO::PARAM_INT);
        $prepared->bindValue(':filesize', $this->contentDataOrDefault('filesize', null), PDO::PARAM_INT);
        $prepared->bindValue(':md5', $this->contentDataOrDefault('md5', null), PDO::PARAM_LOB);
        $prepared->bindValue(':sha1', $this->contentDataOrDefault('sha1', null), PDO::PARAM_LOB);
        $prepared->bindValue(':sha256', $this->contentDataOrDefault('sha256', null), PDO::PARAM_LOB);
        $prepared->bindValue(':sha512', $this->contentDataOrDefault('sha512', null), PDO::PARAM_LOB);
        $prepared->bindValue(':source', $this->contentDataOrDefault('source', null), PDO::PARAM_STR);
        $prepared->bindValue(':license', $this->contentDataOrDefault('license', null), PDO::PARAM_STR);
        $prepared->bindValue(':alt_text', $this->contentDataOrDefault('alt_text', null), PDO::PARAM_STR);
        $prepared->bindValue(':exif', $this->contentDataOrDefault('exif', null), PDO::PARAM_STR);
        $database->executePrepared($prepared);
        return true;
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

        $this->removeFromDisk();
        $this->removeFromDatabase();
        $post = new ContentPost($this->database, $this->content_id, $this->board_id);
        $post->updateCounts();
        $thread = new ContentThread($this->database, $this->content_id, $this->board_id);
        $thread->updateCounts();
    }

    public function removeFromDatabase($temp_database = null)
    {
        if (empty($this->content_id->order_id))
        {
            return false;
        }

        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $prepared = $database->prepare(
                'DELETE FROM "' . $board_references['content_table'] . '" WHERE "post_ref" = ? AND "content_order" = ?');
        $database->executePrepared($prepared, [$this->content_id->post_id, $this->content_id->order_id]);
        return true;
    }

    public function removeFromDisk()
    {
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);

        if (empty($this->content_data))
        {
            $this->loadFromDatabase();
        }

        $file_handler = new \Nelliel\FileHandler();
        $file_handler->eraserGun($board_references['src_path'],
                $this->content_id->thread_id . '/' . $this->content_id->post_id . '/' . $this->content_data['filename'] .
                '.' . $this->content_data['extension']);
        $file_handler->eraserGun($board_references['thumb_path'],
                $this->content_id->thread_id . '/' . $this->content_id->post_id . '/' .
                $this->content_data['preview_name'] . '.' . $this->content_data['preview_extension']);
    }

    public function updateCounts()
    {
    }

    public function verifyModifyPerms()
    {
        $post = new ContentPost($this->database, $this->content_id, $this->board_id);
        return $post->verifyModifyPerms();
    }
}