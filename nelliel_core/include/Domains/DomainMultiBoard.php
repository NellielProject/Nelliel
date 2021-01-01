<?php

namespace Nelliel\Domains;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\NellielPDO;

class DomainMultiBoard extends Domain
{

    public function __construct(NellielPDO $database)
    {
        $this->domain_id = Domain::MULTI_BOARD;
        $this->database = $database;
        $this->utilitySetup();
        $this->locale();
    }

    public function boardExists()
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