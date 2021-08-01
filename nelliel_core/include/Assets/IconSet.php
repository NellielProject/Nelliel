<?php
declare(strict_types = 1);

namespace Nelliel\Assets;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NellielPDO;
use PDO;

class IconSet
{
    private $database;
    private $icon_set_id;
    private $info = array();

    function __construct(NellielPDO $database, string $icon_set_id)
    {
        $this->database = $database;
        $this->icon_set_id = $icon_set_id;
        $this->loadFromDB();
    }

    public function getSection(string $type): array
    {
        return $this->info[$type] ?? array();
    }

    public function getInfo(string $key): string
    {
        return $this->info['set-info'][$key] ?? '';
    }

    public function getFile(string $type, string $icon): string
    {
        return $this->info[$type][$icon] ?? '';
    }

    public function getWebPath(string $type, string $icon): string
    {
        $icon_file = $this->info[$type][$icon] ?? '';
        return NEL_ICON_SETS_WEB_PATH . $this->getInfo('directory') . '/' . $icon_file;
    }

    private function loadFromDB()
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_ASSETS_TABLE . '" WHERE "type" = \'icon-set\' AND "asset_id" = ?');
        $data = $this->database->executePreparedFetch($prepared, [$this->icon_set_id], PDO::FETCH_ASSOC);
        $this->info = json_decode($data['info'], true);
    }
}
