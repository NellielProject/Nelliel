<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class ContentFile extends ContentBase
{
    public $file_data = array();

    function __construct($database, $content_id, $board_id)
    {
        $this->database = $database;
        $this->content_id = $content_id;
        $this->board_id = $board_id;
    }

    private function validFileData($data_name, $default)
    {
        if (isset($this->file_data[$data_name]))
        {
            return $this->file_data[$data_name];
        }

        return $default;
    }

    public function loadFromDatabase($temp_database = null)
    {
        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $prepared = $database->prepare(
                'SELECT * FROM "' . $board_references['file_table'] . '" WHERE "post_ref" = ? AND "file_order" = ?');
        $result = $database->executePreparedFetch($prepared, [$this->content_id->post_id, $this->content_id->order_id], PDO::FETCH_ASSOC);

        if (empty($result))
        {
            return false;
        }

        $this->file_data = $result;
        return true;
    }

    public function removeFromDatabase($temp_database = null)
    {
        if (empty($this->content_id->order_id))
        {
            return false;
        }

        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $prepared = $database->prepare('DELETE FROM "' . $board_references['file_table'] . '" WHERE "post_ref" = ? AND "file_order" = ?');
        $database->executePrepared($prepared, [$this->content_id->post_id, $this->content_id->order_id]);
        return true;
    }

    public function writeToDatabase($temp_database = null)
    {
        if (empty($this->file_data) || empty($this->content_id->order_id))
        {
            return false;
        }

        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $prepared = $database->prepare(
                'SELECT "entry" FROM "' . $board_references['file_table'] . '" WHERE "post_ref" = ? AND "file_order" = ?');
        $result = $database->executePreparedFetch($prepared, [$this->content_id->post_id, $this->content_id->order_id], PDO::FETCH_COLUMN);

        if ($result)
        {
            $prepared = $database->prepare(
                    'UPDATE "' . $board_references['file_table'] . '" SET "parent_thread" = :parent_thread,
                    "post_ref" = :post_ref, "file_order" = :file_order,
                    "type" = :type, "format" = :format, "mime" = :mime,
                    "url" = :url, "filename" = :filename, "extension" = :extension,
                    "image_width" = :image_width, "image_height" = :image_height, "preview_name" = :preview_name,
                    "preview_extension" = :preview_extension, "preview_width" = :preview_width, "preview_height" = :preview_height,
                    "filesize" = :filesize, "md5" = :md5, "sha1" = :sha1, "sha256" = :sha256, "sha512" = :sha512,
                    "source" = :source, "license" = :license, "alt_text" = :alt_text, "exif" = :exif
                    WHERE "post_number" = :post_number');
            $prepared->bindValue(':post_number', $this->content_id->post_id, PDO::PARAM_INT);
        }
        else
        {
            $prepared = $database->prepare(
                    'INSERT INTO "' . $board_references['file_table'] . '" ("parent_thread", "post_ref", "file_order", "type", "format", "mime",
                    "url", "filename", "extension", "image_width", "image_height", "preview_name", "preview_extension", "preview_width", "preview_height",
                    "filesize", "md5", "sha1", "sha256", "sha512", "source", "license", "alt_text", "exif") VALUES
                    (:parent_thread, :post_ref, :file_order, :type, :format, :mime, :url, :filename, :extension, :image_width, :image_height,
                    :preview_name, :preview_extension, :preview_width, :preview_height, :filesize, :md5, :sha1, :sha256, :sha512,
                    :source, :license, :alt_text, :exif)');
        }

        $prepared->bindValue(':parent_thread', $this->validFileData('parent_thread', 0), PDO::PARAM_INT);
        $prepared->bindValue(':post_ref', $this->validFileData('post_ref', null), PDO::PARAM_INT);
        $prepared->bindValue(':file_order', $this->validFileData('file_order', 1), PDO::PARAM_INT);
        $prepared->bindValue(':type', $this->validFileData('type', null), PDO::PARAM_STR);
        $prepared->bindValue(':format', $this->validFileData('format', null), PDO::PARAM_STR);
        $prepared->bindValue(':mime', $this->validFileData('mime', null), PDO::PARAM_STR);
        $prepared->bindValue(':url', $this->validFileData('url', null), PDO::PARAM_STR);
        $prepared->bindValue(':filename', $this->validFileData('filename', null), PDO::PARAM_STR);
        $prepared->bindValue(':extension', $this->validFileData('extension', null), PDO::PARAM_STR);
        $prepared->bindValue(':image_width', $this->validFileData('image_width', null), PDO::PARAM_INT);
        $prepared->bindValue(':image_height', $this->validFileData('image_height', null), PDO::PARAM_INT);
        $prepared->bindValue(':preview_name', $this->validFileData('preview_name', null), PDO::PARAM_STR);
        $prepared->bindValue(':preview_extension', $this->validFileData('preview_extension', null), PDO::PARAM_STR);
        $prepared->bindValue(':preview_width', $this->validFileData('preview_width', null), PDO::PARAM_INT);
        $prepared->bindValue(':preview_height', $this->validFileData('preview_height', null), PDO::PARAM_INT);
        $prepared->bindValue(':filesize', $this->validFileData('filesize', null), PDO::PARAM_INT);
        $prepared->bindValue(':md5', $this->validFileData('md5', null), PDO::PARAM_LOB);
        $prepared->bindValue(':sha1', $this->validFileData('sha1', null), PDO::PARAM_LOB);
        $prepared->bindValue(':sha256', $this->validFileData('sha256', null), PDO::PARAM_LOB);
        $prepared->bindValue(':sha512', $this->validFileData('sha512', null), PDO::PARAM_LOB);
        $prepared->bindValue(':source', $this->validFileData('source', null), PDO::PARAM_STR);
        $prepared->bindValue(':license', $this->validFileData('license', null), PDO::PARAM_STR);
        $prepared->bindValue(':alt_text', $this->validFileData('alt_text', null), PDO::PARAM_STR);
        $prepared->bindValue(':exif', $this->validFileData('exif', null), PDO::PARAM_STR);
        $database->executePrepared($prepared);
        return true;
    }

    public function createDirectories()
    {
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $file_handler = new \Nelliel\FileHandler();
        $file_handler->createDirectory($board_references['src_path'] . $this->content_id->thread_id . '/' . $this->content_id->post_id, DIRECTORY_PERM);
        $file_handler->createDirectory($board_references['thumb_path'] . $this->content_id->thread_id . '/' . $this->content_id->post_id, DIRECTORY_PERM);
    }

    public function removeDirectories()
    {
        $board_references = nel_parameters_and_data()->boardReferences($this->board_id);
        $file_handler = new \Nelliel\FileHandler();
        $file_handler->eraserGun(
                $file_handler->pathJoin($board_references['src_path'],
                        $this->content_id->thread_id . '/' . $this->content_id->post_id), null, true);
        $file_handler->eraserGun(
                $file_handler->pathJoin($board_references['thumb_path'],
                        $this->content_id->thread_id . '/' . $this->content_id->post_id), null, true);
    }
}