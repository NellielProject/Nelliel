<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class DomainAllBoards extends Domain
{

    public function __construct(NellielPDO $database)
    {
        $this->domain_id = '_allboards_';
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