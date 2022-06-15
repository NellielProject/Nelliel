<?php
declare(strict_types = 1);

namespace Nelliel\API\Plugin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NellielPDO;
use Nelliel\Utility\FileHandler;
use PDO;

class Plugin
{
    private $database;
    private $plugin_id = '';
    private $enabled = false;
    private $info = array();
    private $initializer = '';
    private $directory = '';

    function __construct(NellielPDO $database, string $plugin_id)
    {
        $this->database = $database;
        $this->plugin_id = $plugin_id;
        $this->loadData();
    }

    public function id(): string
    {
        return $this->plugin_id;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function info(string $key): string
    {
        return $this->info[$key] ?? '';
    }

    public function initializerFile(): string
    {
        return NEL_PLUGINS_FILES_PATH . $this->directory . '/' . $this->initializer;
    }

    public function install(bool $overwrite = false): void
    {
        $file_handler = new FileHandler();
        $plugin_files = $file_handler->recursiveFileList(NEL_PLUGINS_FILES_PATH);
        $parsed_ini = '';
        $directory = '';
        $initializer_file = '';

        foreach ($plugin_files as $file) {
            if ($file->getFilename() === 'nelliel-plugin.ini') {
                $parsed_ini = parse_ini_file($file->getPathname(), true);

                if ($parsed_ini['id'] !== $this->plugin_id) {
                    continue;
                }

                $directory = basename(dirname($file->getRealPath()));
                $initializer_file = $parsed_ini['initializer'];
            }
        }

        $encoded_ini = json_encode($parsed_ini);

        if ($this->database->rowExists(NEL_PLUGINS_TABLE, ['plugin_id'], [$this->id()],
            [PDO::PARAM_STR, PDO::PARAM_STR])) {
            if (!$overwrite) {
                return;
            }

            $this->uninstall();
        }

        $prepared = $this->database->prepare(
            'INSERT INTO "' . NEL_PLUGINS_TABLE .
            '" ("plugin_id", "directory", "initializer", "parsed_ini", "enabled") VALUES (?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$this->id(), $directory, $initializer_file, $encoded_ini, 1]);
    }

    public function uninstall(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_PLUGINS_TABLE . '" WHERE "plugin_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function loadData(bool $original_ini = false): void
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_PLUGINS_TABLE . '" WHERE "plugin_id" = ?');
        $data = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC);

        if(!is_array($data)) {
            return;
        }

        $this->initializer = $data['initializer'] ?? '';
        $this->directory = $data['directory'] ?? '';
        $this->info = json_decode($data['parsed_ini'] ?? '', true);
        $this->enabled = boolval($data['enabled'] ?? 0);
    }

    public function enable(): void
    {
        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_PLUGINS_TABLE . '" SET "enabled" = 1 WHERE "plugin_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function disable(): void
    {
        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_PLUGINS_TABLE . '" SET "enabled" = 0 WHERE "plugin_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }
}
