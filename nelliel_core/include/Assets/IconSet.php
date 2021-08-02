<?php
declare(strict_types = 1);

namespace Nelliel\Assets;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\FrontEndData;
use Nelliel\NellielPDO;
use PDO;

class IconSet
{
    protected $database;
    protected $icon_set_id;
    protected $info = array();
    protected $front_end_data;

    function __construct(NellielPDO $database, FrontEndData $front_end_data, string $icon_set_id)
    {
        $this->database = $database;
        $this->icon_set_id = $icon_set_id;
        $this->loadFromDB();
        $this->front_end_data = $front_end_data;
    }

    public function id(): string
    {
        return $this->icon_set_id;
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

    private function loadFromDB(): void
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_ASSETS_TABLE . '" WHERE "type" = \'icon-set\' AND "asset_id" = ?');
        $data = $this->database->executePreparedFetch($prepared, [$this->icon_set_id], PDO::FETCH_ASSOC);
        $this->info = json_decode($data['info'], true);
    }
}
