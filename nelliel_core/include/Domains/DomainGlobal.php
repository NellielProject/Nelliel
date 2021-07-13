<?php
declare(strict_types = 1);

namespace Nelliel\Domains;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\NellielCacheInterface;
use Nelliel\NellielPDO;

class DomainGlobal extends Domain implements NellielCacheInterface
{

    public function __construct(NellielPDO $database)
    {
        $this->id = Domain::GLOBAL;
        $this->database = $database;
        $this->utilitySetup();
        $this->locale();
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

    public function exists()
    {
        true;
    }

    public function regenCache()
    {
        ;
    }

    public function deleteCache()
    {
        ;
    }
}