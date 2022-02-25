<?php
declare(strict_types = 1);

namespace Nelliel\FrontEnd;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NellielPDO;
use PDO;

class Style
{
    private $database;
    private $style_id = '';
    private $enabled = false;
    private $data = array();
    private $info = array();
    private $front_end_data;

    function __construct(NellielPDO $database, FrontEndData $front_end_data, string $style_id)
    {
        $this->database = $database;
        $this->style_id = $style_id;
        $this->front_end_data = $front_end_data;
        $this->load();
    }

    public function id(): string
    {
        return $this->style_id;
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

    public function getMainFile(): string
    {
        return $this->info('main_file');
    }

    public function getMainFilePath(): string
    {
        $file_string = $this->getMainFile();

        if ($file_string !== '') {
            return NEL_STYLES_FILES_PATH . $this->info('directory') . '/' . $file_string;
        }

        return $file_string;
    }

    public function getMainFileWebPath(): string
    {
        $file_string = $this->getMainFile();

        if ($file_string !== '') {
            return NEL_STYLES_WEB_PATH . $this->info('directory') . '/' . $file_string;
        }

        return $file_string;
    }

    public function install(bool $overwrite = false): void
    {
        $style_inis = $this->front_end_data->getStyleInis();
        $directory = '';
        $encoded_ini = '';

        foreach ($style_inis as $ini) {
            if ($ini['info']['id'] === $this->id()) {
                $encoded_ini = json_encode($ini);
                $directory = $ini['info']['directory'];
                break;
            }
        }

        if ($this->database->rowExists(NEL_STYLES_TABLE, ['style_id'], [$this->id()], [PDO::PARAM_STR, PDO::PARAM_STR])) {
            if (!$overwrite) {
                return;
            }

            $this->uninstall();
        }

        $prepared = $this->database->prepare(
            'INSERT INTO "' . NEL_STYLES_TABLE .
            '" ("style_id", "directory", "parsed_ini", "enabled") VALUES (?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$this->id(), $directory, $encoded_ini, 1]);
        $this->load();
    }

    public function uninstall(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_STYLES_TABLE . '" WHERE "style_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function load(bool $original_ini = false): void
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_STYLES_TABLE . '" WHERE "style_id" = ?');
        $data = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC);
        $directory = $data['directory'] ?? '';
        $this->enabled = boolval($data['enabled'] ?? 0);

        if (nel_true_empty($data['parsed_ini']) || $original_ini) {
            $file = NEL_STYLES_FILES_PATH . $directory . '/style_info.ini';

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
        $prepared = $this->database->prepare('UPDATE "' . NEL_STYLES_TABLE . '" SET "enabled" = 1 WHERE "style_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function disable(): void
    {
        $prepared = $this->database->prepare('UPDATE "' . NEL_STYLES_TABLE . '" SET "enabled" = 0 WHERE "style_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }
}
