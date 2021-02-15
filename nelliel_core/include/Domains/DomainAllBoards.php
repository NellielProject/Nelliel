<?php

declare(strict_types=1);

namespace Nelliel\Domains;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\NellielPDO;

class DomainAllBoards extends Domain
{

    public function __construct(NellielPDO $database)
    {
        $this->id = Domain::ALL_BOARDS;
        $this->database = $database;
        $this->utilitySetup();
        $this->locale();
    }

    public function exists()
    {
        return true;
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

    public function globalVariation()
    {
        return false;
    }
}