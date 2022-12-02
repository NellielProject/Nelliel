<?php
declare(strict_types = 1);

namespace Nelliel\Domains;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NellielCacheInterface;
use Nelliel\Database\NellielPDO;
use PDO;

class DomainGlobal extends Domain implements NellielCacheInterface
{

    public function __construct(NellielPDO $database)
    {
        $this->domain_id = Domain::GLOBAL;
        $this->database = $database;
        $this->utilitySetup();
        $this->locale();
        $this->templatePath($this->front_end_data->getTemplate(nel_site_domain()->setting('template_id'))->getPath());
    }

    protected function loadSettings(): void
    {
        ;
    }

    protected function loadReferences(): void
    {
        ;
    }

    protected function loadSettingsFromDatabase(): array
    {
        return array();
    }

    public function updateStatistics(): void
    {}

    public function exists()
    {
        true;
    }

    public function regenCache()
    {
        $query = 'SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"';
        $board_ids = $this->database->executeFetchAll($query, PDO::FETCH_COLUMN);

        foreach ($board_ids as $board_id) {
            $board = new DomainBoard($board_id, $this->database);
            $board->regenCache();
        }
    }

    public function deleteCache()
    {
        $query = 'SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"';
        $board_ids = $this->database->executeFetchAll($query, PDO::FETCH_COLUMN);

        foreach ($board_ids as $board_id) {
            $board = new DomainBoard($board_id, $this->database);
            $board->deleteCache();
        }
    }
}