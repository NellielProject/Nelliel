<?php
declare(strict_types = 1);

namespace Nelliel\FrontEnd;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\INIParser;
use Nelliel\Database\NellielPDO;
use Nelliel\Interfaces\SelfPersisting;
use Nelliel\Tables\TableTemplates;
use PDO;

class Template implements SelfPersisting
{
    private NellielPDO $database;
    private $template_id = '';
    private $enabled = false;
    private $data = array();
    private $info = array();
    private $directory = '';
    private $front_end_data;

    function __construct(NellielPDO $database, FrontEndData $front_end_data, string $template_id)
    {
        $this->database = $database;
        $this->template_id = $template_id;
        $this->front_end_data = $front_end_data;
        $this->load();
    }

    public function id(): string
    {
        return $this->template_id ?? '';
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

    public function getPath(): string
    {
        $directory = $this->directory;

        if ($directory !== '') {
            return NEL_TEMPLATES_FILES_PATH . $directory . '/';
        }

        return $directory;
    }

    public function install(bool $overwrite = false): void
    {
        $ini_parser = new INIParser(nel_utilities()->fileHandler());
        $ini_files = $ini_parser->parseDirectories(NEL_TEMPLATES_FILES_PATH, 'template_info.ini', true);
        $directory = $this->front_end_data->templateIsCore($this->id()) ? 'core/' : 'custom/';

        foreach ($ini_files as $ini_file) {
            if ($ini_file->parsed()['info']['id'] === $this->id()) {
                $directory .= basename(dirname($ini_file->fileInfo()->getRealPath()));
                $this->directory = $directory;
                $this->data = $ini_file->parsed();
                break;
            }
        }

        $this->enabled = true;
        $this->save();
    }

    public function uninstall(): void
    {
        $this->delete();
    }

    public function load(): void
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_TEMPLATES_TABLE . '" WHERE "template_id" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC);

        if ($result === false) {
            return;
        }

        $data = TableTemplates::typeCastData($result);
        $this->directory = $data['directory'] ?? '';
        $this->enabled = boolval($data['enabled'] ?? 0);

        if (nel_true_empty($data['parsed_ini'])) {
            $file = NEL_TEMPLATES_FILES_PATH . $this->directory . '/template_info.ini';

            if (file_exists($file)) {
                $ini = parse_ini_file($file, true);
            }
        } else {
            $ini = json_decode($data['parsed_ini'], true);
        }

        $this->data = $ini ?? array();
        $this->info = $ini['info'] ?? array();
    }

    public function save(): void
    {
        if ($this->database->rowExists(NEL_TEMPLATES_TABLE, ['template_id'], [$this->id()],
            [PDO::PARAM_STR, PDO::PARAM_STR])) {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_TEMPLATES_TABLE .
                '" SET "directory" = :directory, "parsed_ini" = :parsed_ini, "enabled" = :enabled WHERE "template_id" = :template_id');
        } else {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_TEMPLATES_TABLE .
                '" ("template_id", "directory", "parsed_ini", "enabled") VALUES (:template_id, :directory, :parsed_ini, :enabled)');
        }

        $prepared->bindValue(':template_id', $this->template_id, PDO::PARAM_STR);
        $prepared->bindValue(':directory', $this->directory ?? '', PDO::PARAM_STR);
        $prepared->bindValue(':parsed_ini', json_encode($this->data) ?? '', PDO::PARAM_STR);
        $prepared->bindValue(':enabled', $this->enabled ?? false, PDO::PARAM_INT);
        $this->database->executePrepared($prepared);
    }

    public function delete(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_TEMPLATES_TABLE . '" WHERE "template_id" = :template_id');
        $prepared->bindValue(':template_id', $this->template_id, PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
    }

    public function enable(): void
    {
        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_TEMPLATES_TABLE . '" SET "enabled" = 1 WHERE "template_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function disable(): void
    {
        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_TEMPLATES_TABLE . '" SET "enabled" = 0 WHERE "template_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }
}
