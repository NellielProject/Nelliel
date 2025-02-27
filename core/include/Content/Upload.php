<?php
declare(strict_types = 1);

namespace Nelliel\Content;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Moar;
use Nelliel\API\JSON\UploadJSON;
use Nelliel\Account\Authorization;
use Nelliel\Database\NellielPDO;
use Nelliel\Domains\Domain;
use Nelliel\Interfaces\MutableData;
use Nelliel\Tables\TableUploads;
use PDO;

class Upload implements MutableData
{
    protected $content_id;
    protected NellielPDO $database;
    protected $domain;
    protected $content_data = array();
    protected $content_moar;
    protected $authorization;
    protected $main_table;
    protected $parent = null;
    protected $json;
    protected $sql_helpers;

    function __construct(ContentID $content_id, Domain $domain, bool $load = true)
    {
        $this->database = $domain->database();
        $this->content_id = $content_id;
        $this->domain = $domain;
        $this->authorization = new Authorization($this->database);
        $this->content_moar = new Moar();
        $this->main_table = new TableUploads($this->database, nel_utilities()->sqlCompatibility());
        $this->main_table->tableName($domain->reference('uploads_table'));
        $this->json = new UploadJSON($this);
        $this->sql_helpers = nel_utilities()->sqlHelpers();

        if ($load) {
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

        if (empty($result)) {
            return false;
        }

        if (!$populate) {
            return true;
        }

        $this->content_data = TableUploads::typeCastData($result);
        $moar = strval($result['moar'] ?? '');
        $this->content_moar = new Moar($moar);
        return true;
    }

    public function writeToDatabase(): bool
    {
        if (!$this->isLoaded() || empty($this->content_id->orderID())) {
            return false;
        }

        $filtered_data = TableUploads::filterData($this->content_data);
        $filtered_data['moar'] = json_encode($this->content_moar->getData());
        $pdo_types = TableUploads::getPDOTypesForData($filtered_data);
        $column_list = array_keys($filtered_data);
        $values = array_values($filtered_data);

        if ($this->main_table->rowExists($filtered_data)) {
            $where_columns = ['upload_id'];
            $where_keys = ['where_upload_id'];
            $where_values = [$this->getData('upload_id')];
            $prepared = $this->sql_helpers->buildPreparedUpdate($this->main_table->tableName(), $column_list,
                $where_columns, $where_keys, $this->sql_helpers->parameterize($column_list),
                $this->sql_helpers->parameterize($where_keys));
            $this->sql_helpers->bindToPrepared($prepared, $column_list, $values, $pdo_types);
            $this->sql_helpers->bindToPrepared($prepared, $where_keys, $where_values);
        } else {
            $prepared = $this->sql_helpers->buildPreparedInsert($this->main_table->tableName(), $column_list,
                $this->sql_helpers->parameterize($column_list));
            $this->sql_helpers->bindToPrepared($prepared, $column_list, $values, $pdo_types);
        }

        return $this->database->executePrepared($prepared);
    }

    public function delete(bool $perm_override = false, bool $parent_delete = false, bool $absolute = false): bool
    {
        if (!$perm_override) {
            if (!$this->verifyModifyPerms()) {
                return false;
            }

            if ($this->domain->reference('locked')) {
                nel_derp(61, _gettext('Cannot remove file. Board is locked.'));
            }
        }

        $this->deleteFromDisk($parent_delete);
        $this->deleteFromDatabase($parent_delete, $absolute);
        $this->domain->updateStatistics();

        if (!$parent_delete) {
            $post = $this->getParent();
            $post->updateCounts();
            $post->getParent()->updateCounts();
        }

        return true;
    }

    protected function deleteFromDatabase(bool $parent_delete, bool $absolute = false): bool
    {
        if (empty($this->content_id->orderID()) || $parent_delete) {
            return false;
        }

        if (!$absolute && $this->domain->setting('keep_deleted_upload_entry')) {
            $prepared = $this->database->prepare(
                'UPDATE "' . $this->domain->reference('uploads_table') .
                '" SET "deleted" = 1 WHERE "post_ref" = ? AND "upload_order" = ?');
            $this->database->executePrepared($prepared, [$this->content_id->postID(), $this->content_id->orderID()]);
        } else {
            $prepared = $this->database->prepare(
                'DELETE FROM "' . $this->domain->reference('uploads_table') .
                '" WHERE "post_ref" = ? AND "upload_order" = ?');
            $this->database->executePrepared($prepared, [$this->content_id->postID(), $this->content_id->orderID()]);
        }

        return true;
    }

    protected function deleteFromDisk(bool $parent_delete): bool
    {
        if (!$this->isLoaded()) {
            $this->loadFromDatabase();
        }

        if (!nel_true_empty($this->getData('embed_url'))) {
            return false;
        }

        $file_handler = nel_utilities()->fileHandler();
        $removed = false;

        if (!$this->fileDeduplicated()) {
            $removed = $file_handler->eraserGun($this->srcFilePath(),
                $this->content_data['filename'] . '.' . $this->content_data['extension']);
        }

        if (!nel_true_empty($this->content_data['static_preview_name']) && !$this->staticPreviewDeduplicated()) {
            $removed = $file_handler->eraserGun($this->previewFilePath(), $this->content_data['static_preview_name']);
        }

        if (!nel_true_empty($this->content_data['animated_preview_name']) && $this->animatedPreviewDeduplicated()) {
            $removed = $file_handler->eraserGun($this->previewFilePath(), $this->content_data['animated_preview_name']);
        }

        return $removed;
    }

    private function fileDeduplicated()
    {
        $prepared = $this->database->prepare(
            'SELECT COUNT(*) FROM "' . $this->domain->reference('uploads_table') .
            '" WHERE "filename" = ? AND "extension" = ?');
        $filename_count = $this->database->executePreparedFetch($prepared,
            [$this->content_data['filename'], $this->content_data['extension']], PDO::FETCH_COLUMN);
        return $filename_count > 1;
    }

    private function staticPreviewDeduplicated()
    {
        if (!nel_true_empty($this->content_data['static_preview_name'])) {
            $prepared = $this->database->prepare(
                'SELECT COUNT(*) FROM "' . $this->domain->reference('uploads_table') .
                '" WHERE "static_preview_name" = ?');
            $static_preview_count = $this->database->executePreparedFetch($prepared,
                [$this->content_data['static_preview_name']], PDO::FETCH_COLUMN);
            return $static_preview_count > 1;
        }

        return false;
    }

    private function animatedPreviewDeduplicated()
    {
        if (!nel_true_empty($this->content_data['animated_preview_name'])) {
            $prepared = $this->database->prepare(
                'SELECT COUNT(*) FROM "' . $this->domain->reference('uploads_table') .
                '" WHERE "animated_preview_name" = ?');
            $static_preview_count = $this->database->executePreparedFetch($prepared,
                [$this->content_data['animated_preview_name']], PDO::FETCH_COLUMN);

            return $static_preview_count <= 1;
        }

        return false;
    }

    public function archive(): bool
    {
        if (!$this->isLoaded()) {
            $this->loadFromDatabase();
        }

        if (!nel_true_empty($this->getData('embed_url'))) {
            return true;
        }

        $file_handler = nel_utilities()->fileHandler();

        $file_handler->copyFile($this->srcFilePath() . $this->getData('filename') . '.' . $this->getData('extension'),
            $this->domain->reference('archive_src_path') . $this->content_id->threadID() . '/' .
            $this->getData('filename') . '.' . $this->getData('extension'), true);

        if (!nel_true_empty($this->getData('static_preview_name'))) {
            $file_handler->copyFile($this->previewFilePath() . $this->getData('static_preview_name'),
                $this->domain->reference('archive_preview_path') . $this->content_id->threadID() . '/' .
                $this->getData('static_preview_name'), true);
        }

        if (!nel_true_empty($this->getData('animated_preview_name'))) {
            $file_handler->copyFile($this->previewFilePath() . $this->getData('animated_preview_name'),
                $this->domain->reference('archive_preview_path') . $this->content_id->threadID() . '/' .
                $this->getData('animated_preview_name'), true);
        }

        return true;
    }

    public function toggleSpoiler(): void
    {
        $this->changeData('spoiler', !$this->getData('spoiler'));
        $this->writeToDatabase();
    }

    public function verifyModifyPerms(): bool
    {
        return $this->getParent()->verifyModifyPerms();
    }

    public function getURL(bool $dynamic): string
    {
        $full_filename = $this->getData('filename') . '.' . $this->getData('extension');
        return $this->srcWebPath() . rawurlencode($full_filename);
    }

    public function getParent(): Post
    {
        if (is_null($this->parent)) {
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
        $file_handler->createDirectory($this->srcFilePath());
        $file_handler->createDirectory($this->previewFilePath());
    }

    public function srcFilePath()
    {
        return $this->domain->reference('src_path');
    }

    public function previewFilePath()
    {
        return $this->domain->reference('preview_path');
    }

    public function srcWebPath()
    {
        return $this->domain->reference('src_web_path');
    }

    public function previewWebPath()
    {
        return $this->domain->reference('preview_web_path');
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
        if (isset($this->content_data[$data_name])) {
            return $this->content_data[$data_name];
        }

        return $default;
    }

    public function getData(string $key = null)
    {
        if (is_null($key)) {
            return $this->content_data;
        }

        return $this->content_data[$key] ?? null;
    }

    public function transferData(array $new_data = null): array
    {
        if (!is_null($new_data)) {
            $this->content_data = $new_data;
        }

        return $this->content_data;
    }

    public function changeData(string $key, $new_data): void
    {
        $this->content_data[$key] = TableUploads::typeCastValue($key, $new_data);
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

    public function parseEmbedURL(string $url, bool $error): string
    {
        $embed_regexes = $this->database->executeFetchAll(
            'SELECT * FROM "' . NEL_EMBEDS_TABLE . '" WHERE "enabled" = 1', PDO::FETCH_ASSOC);

        if ($embed_regexes !== false) {
            foreach ($embed_regexes as $regex) {
                if (preg_match($regex['regex'], $url) === 1) {
                    $embed_url = preg_replace($regex['regex'], $regex['url'], $url);

                    if (is_string($embed_url)) {
                        return $embed_url;
                    }
                }
            }
        }

        if ($error) {
            nel_derp(67, _gettext('Embed URL is malformed or not supported.'));
        }

        return '';
    }

    public function move(Post $new_post, bool $is_shadow): Upload
    {
        $new_board = $new_post->domain()->id() !== $this->domain()->id();
        $parent = $this->getParent();

        if ($is_shadow) {
            $this->changeData('shadow', true);
            $this->writeToDatabase();
        }

        if ($new_board) {
            $new_content_id = $new_post->contentID();
            $new_content_id->changeOrderID($this->content_id->orderID());
            $new_upload = new Upload($new_content_id, $new_post->domain());
            unset($this->content_data['upload_id']);
            $new_upload->transferData($this->transferData());
            $new_upload->storeMoar($this->content_moar);
            $new_upload->changeData('parent_thread', $new_post->contentID()->threadID());
            $new_upload->changeData('post_ref', $new_post->contentID()->postID());
            // $new_upload->changeData('upload_id', null);
        } else {
            $new_upload = $this;
            $new_upload->contentID()->changePostID($new_post->contentID()->postID());
            $new_upload->changeData('parent_thread', $new_post->getParent()->contentID()->threadID());
            $new_upload->changeData('post_ref', $new_post->contentID()->postID());
        }

        $next_order = $new_post->getLastUploadOrder() + 1;
        $new_upload->contentID()->changeOrderID($next_order);
        $new_upload->changeData('upload_order', $next_order);
        $new_upload->writeToDatabase();

        if ($new_board) {
            $file_handler = nel_utilities()->fileHandler();

            if (nel_true_empty($this->getData('embed_url'))) {
                if ($this->fileDeduplicated() || $is_shadow) {
                    $file_handler->copyFile(
                        $this->srcFilePath() . $this->getData('filename') . '.' . $this->getData('extension'),
                        $new_upload->srcFilePath() . $new_upload->getData('filename') . '.' .
                        $new_upload->getData('extension'));
                } else {
                    $file_handler->moveFile(
                        $this->srcFilePath() . $this->getData('filename') . '.' . $this->getData('extension'),
                        $new_upload->srcFilePath() . $new_upload->getData('filename') . '.' .
                        $new_upload->getData('extension'));
                }
            }

            if (!nel_true_empty($this->getData('static_preview_name'))) {
                if ($this->staticPreviewDeduplicated() || $is_shadow) {
                    $file_handler->copyFile($this->previewFilePath() . $this->getData('static_preview_name'),
                        $new_upload->previewFilePath() . $this->getData('static_preview_name'));
                } else {
                    $file_handler->moveFile($this->previewFilePath() . $this->getData('static_preview_name'),
                        $new_upload->previewFilePath() . $this->getData('static_preview_name'));
                }
            }

            if (!nel_true_empty($this->getData('animated_preview_name'))) {
                if ($this->animatedPreviewDeduplicated() || $is_shadow) {
                    $file_handler->copyFile($this->previewFilePath() . $this->getData('animated_preview_name'),
                        $new_upload->previewFilePath() . $this->getData('animated_preview_name'));
                } else {
                    $file_handler->moveFile($this->previewFilePath() . $this->getData('animated_preview_name'),
                        $new_upload->previewFilePath() . $this->getData('animated_preview_name'));
                }
            }

            if (!$is_shadow) {
                $this->delete(true, true, true);
            }
        }

        $parent->updateCounts();
        $new_post->updateCounts();
        return $new_upload;
    }
}