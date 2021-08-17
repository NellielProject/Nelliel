<?php
declare(strict_types = 1);

namespace Nelliel\Content;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Moar;
use Nelliel\SQLCompatibility;
use Nelliel\API\JSON\UploadJSON;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Setup\TableUploads;
use PDO;

class Upload
{
    protected $content_id;
    protected $database;
    protected $domain;
    protected $content_data = array();
    protected $content_moar;
    protected $authorization;
    protected $main_table;
    protected $parent;
    protected $json;

    function __construct(ContentID $content_id, Domain $domain, Post $parent = null, bool $load = true)
    {
        $this->database = $domain->database();
        $this->content_id = $content_id;
        $this->domain = $domain;
        $this->authorization = new Authorization($this->database);
        $this->storeMoar(new Moar());
        $this->main_table = new TableUploads($this->database, new SQLCompatibility($this->database));
        $this->parent = $parent;
        $this->json = new UploadJSON($this, nel_utilities()->fileHandler());

        if ($load)
        {
            $this->loadFromDatabase();
        }
    }

    public function loadFromDatabase()
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . $this->domain->reference('upload_table') .
                '" WHERE "post_ref" = ? AND "upload_order" = ?');
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
        $column_types = $this->main_table->columnTypes();

        foreach ($result as $name => $value)
        {
            $this->content_data[$name] = nel_cast_to_datatype($value, $column_types[$name]['php_type'] ?? '');
        }

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
                'SELECT "entry" FROM "' . $this->domain->reference('upload_table') .
                '" WHERE "post_ref" = ? AND "upload_order" = ?');
        $result = $this->database->executePreparedFetch($prepared,
                [$this->content_id->postID(), $this->content_id->orderID()], PDO::FETCH_COLUMN);

        if ($result)
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . $this->domain->reference('upload_table') .
                    '" SET "parent_thread" = :parent_thread,
                    "post_ref" = :post_ref, "upload_order" = :upload_order,
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
                    'INSERT INTO "' . $this->domain->reference('upload_table') .
                    '" ("parent_thread", "post_ref", "upload_order", "type", "format", "mime",
                    "filename", "extension", "display_width", "display_height", "preview_name", "preview_extension", "preview_width", "preview_height",
                    "filesize", "md5", "sha1", "sha256", "sha512", "embed_url", "spoiler", "deleted", "exif", "moar") VALUES
                    (:parent_thread, :post_ref, :upload_order, :type, :format, :mime, :filename, :extension, :display_width, :display_height,
                    :preview_name, :preview_extension, :preview_width, :preview_height, :filesize, :md5, :sha1, :sha256, :sha512, :embed_url, :spoiler,
                    :deleted, :exif, :moar)');
        }

        $prepared->bindValue(':parent_thread', $this->contentDataOrDefault('parent_thread', 0), PDO::PARAM_INT);
        $prepared->bindValue(':post_ref', $this->contentDataOrDefault('post_ref', null), PDO::PARAM_INT);
        $prepared->bindValue(':upload_order', $this->contentDataOrDefault('upload_order', 1), PDO::PARAM_INT);
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
        $post = new Post($this->content_id, $this->domain);
        $post->updateCounts();
        $thread = new Thread($this->content_id, $this->domain);
        $thread->updateCounts();
    }

    protected function removeFromDatabase()
    {
        if (empty($this->content_id->orderID()))
        {
            return false;
        }

        if ($this->domain->setting('deleted_upload_placeholder'))
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . $this->domain->reference('upload_table') .
                    '" SET "preview_name" = null, "preview_extension" = null, "preview_width" = null, "preview_height" = null,
                    "deleted" = 1 WHERE "post_ref" = ? AND "upload_order" = ?');
            $this->database->executePrepared($prepared, [$this->content_id->postID(), $this->content_id->orderID()]);
        }
        else
        {
            $prepared = $this->database->prepare(
                    'DELETE FROM "' . $this->domain->reference('upload_table') .
                    '" WHERE "post_ref" = ? AND "upload_order" = ?');
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
        $file_handler->eraserGun($this->srcPath(),
                $this->content_data['filename'] . '.' . $this->content_data['extension']);
        $file_handler->eraserGun($this->previewPath(),
                $this->content_data['preview_name'] . '.' . $this->content_data['preview_extension']);
    }

    protected function verifyModifyPerms()
    {
        $post = new Post($this->content_id, $this->domain);
        return $post->verifyModifyPerms();
    }

    public function getParent(): Post
    {
        if (is_null($this->parent))
        {
            $content_id = new ContentID();
            $content_id->changeThreadID($this->content_id->threadID());
            $content_id->changePostID($this->content_id->postID());
            $this->parent = new Post($content_id, $this->domain);
        }

        return $this->parent;
    }

    public function createDirectories()
    {
        $file_handler = nel_utilities()->fileHandler();
        $file_handler->createDirectory($this->srcPath(), NEL_DIRECTORY_PERM);
        $file_handler->createDirectory($this->previewPath(), NEL_DIRECTORY_PERM);
    }

    public function srcPath()
    {
        return $this->domain->reference('src_path') . $this->content_id->threadID() . '/' . $this->content_id->postID() .
                '/';
    }

    public function previewPath()
    {
        return $this->domain->reference('preview_path') . $this->content_id->threadID() . '/' .
                $this->content_id->postID() . '/';
    }

    public function storeMoar(Moar $moar)
    {
        $this->content_moar = $moar;
    }

    public function getMoar()
    {
        return $this->content_moar;
    }

    protected function contentDataOrDefault(string $data_name, $default)
    {
        if (isset($this->content_data[$data_name]))
        {
            return $this->content_data[$data_name];
        }

        return $default;
    }

    public function data(string $key)
    {
        return $this->content_data[$key] ?? null;
    }

    public function changeData(string $key, $new_data)
    {
        $column_types = $this->main_table->columnTypes();
        $type = $column_types[$key]['php_type'] ?? '';
        $new_data = nel_cast_to_datatype($new_data, $type);
        $old_data = $this->data($key);
        $this->content_data[$key] = $new_data;
        return $old_data;
    }

    public function contentID()
    {
        return $this->content_id;
    }

    public function domain()
    {
        return $this->domain;
    }

    public function isLoaded()
    {
        return !empty($this->content_data);
    }

    public function getJSON(): UploadJSON
    {
        return $this->json;
    }
}