<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class DomainAllBoards extends Domain
{

    public function __construct($database)
    {
        $this->domain_id = '_all_boards';
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

    public function regenCache()
    {
    }
}