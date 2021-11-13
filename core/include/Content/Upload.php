<?php
declare(strict_types = 1);

namespace Nelliel\Content;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Moar;
use Nelliel\API\JSON\UploadJSON;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Tables\TableUploads;
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
    protected $sql_helpers;

    function __construct(ContentID $content_id, Domain $domain, Post $parent = null, bool $load = true)
    {
        $this->database = $domain->database();
        $this->content_id = $content_id;
        $this->domain = $domain;
        $this->authorization = new Authorization($this->database);
        $this->storeMoar(new Moar());
        $this->main_table = new TableUploads($this->database, nel_utilities()->sqlCompatibility());
        $this->main_table->tableName($domain->reference('uploads_table'));
        $this->parent = $parent;
        $this->json = new UploadJSON($this, nel_utilities()->fileHandler());
        $this->sql_helpers = nel_utilities()->sqlHelpers();

        if ($load)
        {
            $this->loadFromDatabase(true);
        }
    }

    public function exists(): bool
    {
        return $this->loadFromDatabase(false);
    }

    public function loadFromDatabase(bool $populate = true): bool
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . $this->domain->reference('uploads_table') .
                '" WHERE "post_ref" = ? AND "upload_order" = ?');
        $result = $this->database->executePreparedFetch($prepared,
                [$this->content_id->postID(), $this->content_id->orderID()], PDO::FETCH_ASSOC);

        if (empty($result))
        {
            return false;
        }

        if (!$populate)
        {
            return true;
        }

        $column_types = $this->main_table->columnTypes();

        foreach ($result as $name => $value)
        {
            $this->content_data[$name] = nel_typecast($value, $column_types[$name]['php_type'] ?? '');
        }

        $moar = $result['moar'] ?? '';
        $this->getMoar()->storeFromJSON($moar);
        return true;
    }

    public function writeToDatabase(): bool
    {
        if (!$this->isLoaded() || empty($this->content_id->orderID()))
        {
            return false;
        }

        $filtered_data = $this->main_table->filterColumns($this->content_data);
        $filtered_data['moar'] = $this->getMoar()->getJSON();
        $pdo_types = $this->main_table->getPDOTypes($filtered_data);
        $column_list = array_keys($filtered_data);
        $values = array_values($filtered_data);

        if ($this->main_table->rowExists($filtered_data))
        {
            $where_columns = ['post_ref', 'upload_order'];
            $where_keys = ['where_post_ref', 'where_upload_order'];
            $where_values = [$this->content_id->postID(), $this->content_id->orderID()];
            $prepared = $this->sql_helpers->buildPreparedUpdate($this->main_table->tableName(), $column_list,
                    $where_columns, $where_keys);
            $this->sql_helpers->bindToPrepared($prepared, $column_list, $values, $pdo_types);
            $this->sql_helpers->bindToPrepared($prepared, $where_keys, $where_values);
            $this->database->executePrepared($prepared);
        }
        else
        {
            $prepared = $this->sql_helpers->buildPreparedInsert($this->main_table->tableName(), $column_list);
            $this->sql_helpers->bindToPrepared($prepared, $column_list, $values, $pdo_types);
            $this->database->executePrepared($prepared);
        }

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
        $post = $this->getParent();
        $post->updateCounts();
        $post->getParent()->updateCounts();
    }

    protected function removeFromDatabase()
    {
        if (empty($this->content_id->orderID()))
        {
            return false;
        }

        if ($this->domain->setting('keep_deleted_upload_entry'))
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . $this->domain->reference('uploads_table') .
                    '" SET "deleted" = 1 WHERE "post_ref" = ? AND "upload_order" = ?');
            $this->database->executePrepared($prepared, [$this->content_id->postID(), $this->content_id->orderID()]);
        }
        else
        {
            $prepared = $this->database->prepare(
                    'DELETE FROM "' . $this->domain->reference('uploads_table') .
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

        if (!nel_true_empty($this->data('embed_url')))
        {
            return;
        }

        $file_handler = nel_utilities()->fileHandler();
        $file_handler->eraserGun($this->srcPath(),
                $this->content_data['filename'] . '.' . $this->content_data['extension']);
        $file_handler->eraserGun($this->previewPath(), $this->content_data['static_preview_name']);
        $file_handler->eraserGun($this->previewPath(), $this->content_data['animated_preview_name']);
    }

    public function verifyModifyPerms()
    {
        return $this->getParent()->verifyModifyPerms();
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
        $new_data = nel_typecast($new_data, $type);
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

    protected function isLoaded()
    {
        return !empty($this->content_data);
    }

    public function getJSON(): UploadJSON
    {
        return $this->json;
    }
}