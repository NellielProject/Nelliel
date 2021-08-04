<?php
declare(strict_types = 1);

namespace Nelliel\Assets;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\FrontEndData;
use Nelliel\NellielPDO;
use PDO;

class IconSet
{
    private $database;
    private $icon_set_id;
    private $data = array();
    private $info = array();
    private $front_end_data;

    function __construct(NellielPDO $database, FrontEndData $front_end_data, string $icon_set_id)
    {
        $this->database = $database;
        $this->icon_set_id = $icon_set_id;
        $this->loadFromDB();
        $this->front_end_data = $front_end_data;
    }

    public function id(): string
    {
        return $this->icon_set_id ?? '';
    }

    public function info(string $key): string
    {
        return $this->info['set-info'][$key] ?? '';
    }

    public function getSection(string $section): array
    {
        return $this->info[$section] ?? array();
    }

    public function getFile(string $section, string $icon, bool $fallback): string
    {
        if (!isset($this->info[$section][$icon]) && $fallback)
        {
            return $this->front_end_data->getBaseIconSet()->getFile($section, $icon, false);
        }

        return $this->info[$section][$icon] ?? '';
    }

    public function getFilePath(string $section, string $icon, bool $fallback): string
    {
        if ($this->getFile($section, $icon, false) === '' && $fallback)
        {
            return $this->front_end_data->getBaseIconSet()->getFilePath($section, $icon, false);
        }

        $icon_file = $this->info[$section][$icon] ?? '';

        if ($icon_file !== '')
        {
            return NEL_ICON_SETS_FILES_PATH . $this->info('directory') . '/' . $section . '/' . $icon_file;
        }

        return '';
    }

    public function getWebPath(string $section, string $icon, bool $fallback): string
    {
        if ($this->getFile($section, $icon, false) === '' && $fallback)
        {
            return $this->front_end_data->getBaseIconSet()->getWebPath($section, $icon, false);
        }

        $icon_file = $this->info[$section][$icon] ?? '';

        if ($icon_file !== '')
        {
            return NEL_ICON_SETS_WEB_PATH . $this->info('directory') . '/' . $section . '/' . $icon_file;
        }

        return '';
    }

    public function install(bool $overwrite = false): void
    {
        $icon_set_inis = $this->front_end_data->getIconSetInis();

        foreach ($icon_set_inis as $ini)
        {
            if ($ini['set-info']['id'] === $this->id())
            {
                $info = json_encode($ini);
                break;
            }
        }

        if ($this->database->rowExists(NEL_ICON_SETS_TABLE, ['set_id'], [$this->id()],
                [PDO::PARAM_STR, PDO::PARAM_STR]))
        {
            if (!$overwrite)
            {
                return;
            }

            $this->uninstall();
        }

        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_ICON_SETS_TABLE . '" ("set_id", "info") VALUES (?, ?)');
        $this->database->executePrepared($prepared, [$this->id(), $info]);
        $this->loadFromDB();
    }

    public function uninstall(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_ICON_SETS_TABLE . '" WHERE "set_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function loadFromDB(): void
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_ICON_SETS_TABLE . '" WHERE "set_id" = ?');
        $data = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC);
        $this->data = $data;
        $this->info = json_decode($data['info'] ?? '', true);
    }
}
