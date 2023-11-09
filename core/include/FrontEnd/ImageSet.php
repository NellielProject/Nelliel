<?php
declare(strict_types = 1);

namespace Nelliel\FrontEnd;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;
use PDO;
use Nelliel\INIParser;

class ImageSet
{
    private $database;
    private $image_set_id = '';
    private $enabled = false;
    private $data = array();
    private $info = array();
    private $directory = '';
    private $front_end_data;

    function __construct(NellielPDO $database, FrontEndData $front_end_data, string $image_set_id)
    {
        $this->database = $database;
        $this->image_set_id = $image_set_id;
        $this->front_end_data = $front_end_data;
        $this->load();
    }

    public function id(): string
    {
        return $this->image_set_id;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function info(string $key): string
    {
        return $this->info[$key] ?? '';
    }

    public function data(string $section, string $key): string
    {
        return $this->data[$section][$key] ?? '';
    }

    public function directory(): string
    {
        return $this->directory;
    }

    public function getFile(string $section, string $key, bool $fallback): string
    {
        if ($this->data($section, $key) === '' && $fallback) {
            return $this->front_end_data->getBaseImageSet()->getFile($section, $key, false);
        }

        return $this->data($section, $key) ?? '';
    }

    public function getFilePath(string $section, string $key, bool $fallback): string
    {
        if ($this->getFile($section, $key, false) === '' && $fallback) {
            return $this->front_end_data->getBaseImageSet()->getFilePath($section, $key, false);
        }

        $file = $this->data($section, $key);

        if ($file !== '') {
            return NEL_IMAGE_SETS_FILES_PATH . $this->directory . '/' . $file;
        }

        return '';
    }

    public function getWebPath(string $section, string $key, bool $fallback): string
    {
        if ($this->getFile($section, $key, false) === '' && $fallback) {
            return $this->front_end_data->getBaseImageSet()->getWebPath($section, $key, false);
        }

        $file = $this->data($section, $key);

        if ($file !== '') {
            return NEL_IMAGE_SETS_WEB_PATH . $this->directory . '/' . $file;
        }

        return '';
    }

    public function install(bool $overwrite = false): void
    {
        $ini_parser = new INIParser(nel_utilities()->fileHandler());
        $ini_files = $ini_parser->parseDirectories(NEL_IMAGE_SETS_FILES_PATH, 'set_info.ini', true);
        $encoded_ini = '';
        $directory = $this->front_end_data->imageSetIsCore($this->id()) ? 'core/' : 'custom/';

        foreach ($ini_files as $ini_file) {
            if ($ini_file->parsed()['info']['id'] === $this->id()) {
                $encoded_ini = json_encode($ini_file->parsed());
                $directory .= basename(dirname($ini_file->fileInfo()->getRealPath()));
                break;
            }
        }

        if ($this->database->rowExists(NEL_IMAGE_SETS_TABLE, ['set_id'], [$this->id()],
            [PDO::PARAM_STR, PDO::PARAM_STR])) {
            if (!$overwrite) {
                return;
            }

            $this->uninstall();
        }

        $prepared = $this->database->prepare(
            'INSERT INTO "' . NEL_IMAGE_SETS_TABLE .
            '" ("set_id", "directory", "parsed_ini", "enabled") VALUES (?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$this->id(), $directory, $encoded_ini, 1]);
        $this->load();
    }

    public function uninstall(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_IMAGE_SETS_TABLE . '" WHERE "set_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function load(bool $original_ini = false): void
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_IMAGE_SETS_TABLE . '" WHERE "set_id" = ?');
        $data = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC);

        if ($data === false) {
            return;
        }

        $this->directory = $data['directory'] ?? '';
        $this->enabled = boolval($data['enabled'] ?? 0);

        if (nel_true_empty($data['parsed_ini']) || $original_ini) {
            $file = NEL_ASSETS_FILES_PATH . $this->directory . '/set_info.ini';

            if (file_exists($file)) {
                $ini = parse_ini_file($file, true);
            }
        } else {
            $ini = json_decode($data['parsed_ini'], true);
        }

        $this->data = $ini ?? array();
        $this->info = $ini['info'] ?? array();
    }

    public function enable(): void
    {
        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_IMAGE_SETS_TABLE . '" SET "enabled" = 1 WHERE "set_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function disable(): void
    {
        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_IMAGE_SETS_TABLE . '" SET "enabled" = 0 WHERE "set_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }
}
