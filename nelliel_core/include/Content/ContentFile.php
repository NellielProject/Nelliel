<?php
declare(strict_types = 1);

namespace Nelliel\Content;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use Nelliel\Moar;
use PDO;

class ContentFile extends ContentHandler
{
    protected $content_table;
    protected $src_path;
    protected $preview_path;

    function __construct(ContentID $content_id, Domain $domain)
    {
        $this->database = $domain->database();
        $this->content_id = $content_id;
        $this->domain = $domain;
        $this->content_table = $this->domain->reference('content_table');
        $this->src_path = $this->domain->reference('src_path');
        $this->preview_path = $this->domain->reference('preview_path');
        $this->storeMoar(new Moar());
    }

    public function loadFromDatabase()
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . $this->content_table . '" WHERE "post_ref" = ? AND "content_order" = ?');
        $result = $this->database->executePreparedFetch($prepared,
                [$this->content_id->postID(), $this->content_id->orderID()], PDO::FETCH_ASSOC);

        if (empty($result))
        {
            return false;
        }

        $result['md5'] = nel_convert_hash_from_storage($result['md5']);
        $result['sha1'] = nel_convert_hash_from_storage($result['sha1']);
        $result['sha256'] = nel_convert_hash_from_storage($result['sha256']);
        $result['sha512'] = nel_convert_hash_from_storage($result['sha512']);
        $this->content_data = $result;
        $moar = $result['moar'] ?? '';
        $this->getMoar()->storeFromJSON($moar);
        return true;
    }

    public function writeToDatabase()
    {
        if (!$this->isLoaded() || empty($this->content_id->orderID()))
        {
            return false;
        }

        $prepared = $this->database->prepare(
                'SELECT "entry" FROM "' . $this->content_table . '" WHERE "post_ref" = ? AND "content_order" = ?');
        $result = $this->database->executePreparedFetch($prepared,
                [$this->content_id->postID(), $this->content_id->orderID()], PDO::FETCH_COLUMN);

        if ($result)
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . $this->content_table .
                    '" SET "parent_thread" = :parent_thread,
                    "post_ref" = :post_ref, "content_order" = :content_order,
                    "type" = :type, "format" = :format, "mime" = :mime,
                    "filename" = :filename, "extension" = :extension,
                    "display_width" = :display_width, "display_height" = :display_height, "preview_name" = :preview_name,
                    "preview_extension" = :preview_extension, "preview_width" = :preview_width, "preview_height" = :preview_height,
                    "filesize" = :filesize, "md5" = :md5, "sha1" = :sha1, "sha256" = :sha256, "sha512" = :sha512, "embed_url" = :embed_url,
                    "spoiler" = :spoiler, "deleted" = :deleted, "exif" = :exif, "moar" = :moar
                    WHERE "post_number" = :post_number');
            $prepared->bindValue(':post_number', $this->content_id->postID(), PDO::PARAM_INT);
        }
        else
        {
            $prepared = $this->database->prepare(
                    'INSERT INTO "' . $this->content_table .
                    '" ("parent_thread", "post_ref", "content_order", "type", "format", "mime",
                    "filename", "extension", "display_width", "display_height", "preview_name", "preview_extension", "preview_width", "preview_height",
                    "filesize", "md5", "sha1", "sha256", "sha512", "embed_url", "spoiler", "deleted", "exif", "moar") VALUES
                    (:parent_thread, :post_ref, :content_order, :type, :format, :mime, :filename, :extension, :display_width, :display_height,
                    :preview_name, :preview_extension, :preview_width, :preview_height, :filesize, :md5, :sha1, :sha256, :sha512, :embed_url, :spoiler,
                    :deleted, :exif, :moar)');
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
        $prepared->bindValue(':md5', nel_prepare_hash_for_storage($this->contentDataOrDefault('md5', null)),
                PDO::PARAM_LOB);
        $prepared->bindValue(':sha1', nel_prepare_hash_for_storage($this->contentDataOrDefault('sha1', null)),
                PDO::PARAM_LOB);
        $prepared->bindValue(':sha256', nel_prepare_hash_for_storage($this->contentDataOrDefault('sha256', null)),
                PDO::PARAM_LOB);
        $prepared->bindValue(':sha512', nel_prepare_hash_for_storage($this->contentDataOrDefault('sha512', null)),
                PDO::PARAM_LOB);
        $prepared->bindValue(':embed_url', $this->contentDataOrDefault('embed_url', null), PDO::PARAM_STR);
        $prepared->bindValue(':spoiler', $this->contentDataOrDefault('spoiler', 0), PDO::PARAM_INT);
        $prepared->bindValue(':deleted', $this->contentDataOrDefault('deleted', 0), PDO::PARAM_INT);
        $prepared->bindValue(':exif', json_encode($this->contentDataOrDefault('exif', array()), JSON_UNESCAPED_UNICODE),
                PDO::PARAM_STR);
        $prepared->bindValue(':moar', $this->getMoar()->getJSON(), PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
        return true;
    }

    public function createDirectories()
    {
        $file_handler = nel_utilities()->fileHandler();
        $file_handler->createDirectory($this->src_path . $this->content_id->postID(), NEL_DIRECTORY_PERM);
        $file_handler->createDirectory($this->preview_path . $this->content_id->postID(), NEL_DIRECTORY_PERM);
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
                nel_derp(61, _gettext('Cannot remove file. Board is locked.'));
            }
        }

        $this->removeFromDisk();
        $this->removeFromDatabase();
        $post = new ContentPost($this->content_id, $this->domain);
        $post->updateCounts();
        $thread = new ContentThread($this->content_id, $this->domain);
        $thread->updateCounts();
    }

    protected function removeFromDatabase()
    {
        if (empty($this->content_id->orderID()))
        {
            return false;
        }

        if ($this->domain->setting('deleted_content_placeholder'))
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . $this->content_table .
                    '" SET "preview_name" = null, "preview_extension" = null, "preview_width" = null, "preview_height" = null,
                    "deleted" = 1 WHERE "post_ref" = ? AND "content_order" = ?');
            $this->database->executePrepared($prepared, [$this->content_id->postID(), $this->content_id->orderID()]);
        }
        else
        {
            $prepared = $this->database->prepare(
                    'DELETE FROM "' . $this->content_table . '" WHERE "post_ref" = ? AND "content_order" = ?');
            $this->database->executePrepared($prepared, [$this->content_id->postID(), $this->content_id->orderID()]);
            return true;
        }
    }

    protected function removeFromDisk()
    {
        if (!$this->isLoaded())
        {
            $this->loadFromDatabase();
        }

        $file_handler = nel_utilities()->fileHandler();
        $file_handler->eraserGun($this->src_path,
                $this->content_id->threadID() . '/' . $this->content_id->postID() . '/' . $this->content_data['filename'] .
                '.' . $this->content_data['extension']);
        $file_handler->eraserGun($this->preview_path,
                $this->content_id->threadID() . '/' . $this->content_id->postID() . '/' .
                $this->content_data['preview_name'] . '.' . $this->content_data['preview_extension']);
    }

    public function updateCounts()
    {
    }

    protected function verifyModifyPerms()
    {
        $post = new ContentPost($this->content_id, $this->domain);
        return $post->verifyModifyPerms();
    }

    public function getParent()
    {
        $content_id = new \Nelliel\Content\ContentID();
        $content_id->changeThreadID($this->content_id->threadID());
        $content_id->changePostID($this->content_id->postID());
        $parent_post = new ContentPost($content_id, $this->domain);
        return $parent_post;
    }
}