<?php

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
        $this->domain_id = Domain::ALL_BOARDS;
        $this->database = $database;
        $this->utilitySetup();
        $this->locale();
    }

    public function exists()
    {
        return true;
    }

    protected function loadSettings()
    {
        return array();
    }

    protected function loadReferences()
    {
        return array();
    }

    protected function loadSettingsFromDatabase()
    {
        return array();
    }

    public function globalVariation()
    {
        return false;
    }
}