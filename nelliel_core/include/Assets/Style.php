<?php
declare(strict_types = 1);

namespace Nelliel\Assets;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\FrontEndData;
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
        $this->loadFromDB();
        $this->front_end_data = $front_end_data;
    }

    public function id(): string
    {
        return $this->style_id ?? '';
    }

    public function info(string $key): string
    {
        return $this->info['style-info'][$key] ?? '';
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

        foreach ($style_inis as $ini)
        {
            if ($ini['style-info']['id'] === $this->id())
            {
                $info = json_encode($ini);
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
                'INSERT INTO "' . NEL_STYLES_TABLE . '" ("style_id", "info") VALUES (?, ?)');
        $this->database->executePrepared($prepared, [$this->id(), $info]);
        $this->loadFromDB();
    }

    public function uninstall(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_STYLES_TABLE . '" WHERE "style_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function loadFromDB(): void
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_STYLES_TABLE . '" WHERE "style_id" = ?');
        $data = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC);
        $this->data = $data;
        $this->info = json_decode($data['info'] ?? '', true);
    }
}
