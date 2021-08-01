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
    private $info = array();
    private $front_end_data;

    function __construct(NellielPDO $database, FrontEndData $front_end_data, string $icon_set_id)
    {
        $this->database = $database;
        $this->icon_set_id = $icon_set_id;
        $this->loadFromDB();
    }

    public function id(): string
    {
        return $this->icon_set_id;
    }

    public function getSection(string $type): array
    {
        return $this->info[$type] ?? array();
    }

    public function getInfo(string $key): string
    {
        return $this->info['set-info'][$key] ?? '';
    }

    public function getFile(string $type, string $icon, bool $fallback = false): string
    {
        if ($fallback && !isset($this->info[$type][$icon]))
        {
            return $this->front_end_data->getDefaultIconSet()->getFile($type, $icon, false);
        }

        return $this->info[$type][$icon] ?? '';
    }

    public function getWebPath(string $type, string $icon, bool $fallback = false): string
    {
        if ($fallback && !isset($this->info[$type][$icon]))
        {
            return $this->front_end_data->getDefaultIconSet()->getWebPath($type, $icon, false);
        }

        $icon_file = $this->info[$type][$icon] ?? '';
        return NEL_ICON_SETS_WEB_PATH . $this->getInfo('directory') . '/' . $icon_file;
    }

    private function loadFromDB(): void
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_ASSETS_TABLE . '" WHERE "type" = \'icon-set\' AND "asset_id" = ?');
        $data = $this->database->executePreparedFetch($prepared, [$this->icon_set_id], PDO::FETCH_ASSOC);
        $this->info = json_decode($data['info'], true);
    }
}
