<?php
declare(strict_types = 1);

namespace Nelliel\FrontEnd;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NellielPDO;
use PDO;

class Style
{
    private $database;
    private $style_id;
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
        return $this->style_id ?? '';
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

        if ($file_string !== '')
        {
            return NEL_STYLES_FILES_PATH . $this->info('directory') . '/' . $file_string;
        }

        return $file_string;
    }

    public function getMainFileWebPath(): string
    {
        $file_string = $this->getMainFile();

        if ($file_string !== '')
        {
            return NEL_STYLES_WEB_PATH . $this->info('directory') . '/' . $file_string;
        }

        return $file_string;
    }

    public function install(bool $overwrite = false): void
    {
        $style_inis = $this->front_end_data->getStyleInis();
        $directory = '';

        foreach ($style_inis as $ini)
        {
            if ($ini['style-info']['id'] === $this->id())
            {
                $directory = $ini['style-info']['directory'];
                break;
            }
        }

        if ($this->database->rowExists(NEL_STYLES_TABLE, ['style_id'], [$this->id()], [PDO::PARAM_STR, PDO::PARAM_STR]))
        {
            if (!$overwrite)
            {
                return;
            }

            $this->uninstall();
        }

        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_STYLES_TABLE . '" ("style_id", "directory") VALUES (?, ?)');
        $this->database->executePrepared($prepared, [$this->id(), $directory]);
        $this->load();
    }

    public function uninstall(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_STYLES_TABLE . '" WHERE "style_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function load(): void
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_STYLES_TABLE . '" WHERE "style_id" = ?');
        $data = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC);
        $directory = $data['directory'] ?? '';
        $file = NEL_STYLES_FILES_PATH . $directory . '/style_info.ini';

        if (file_exists($file))
        {
            $ini = parse_ini_file($file, true);
            $this->data = $ini ?? array();
            $this->info = $ini['style-info'] ?? array();
        }
    }
}
