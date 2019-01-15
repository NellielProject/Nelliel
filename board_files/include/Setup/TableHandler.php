<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

abstract class TableHandler
{
    protected $database;
    protected $sql_helpers;
    protected $table_name;
    protected $columns;
    protected $pdo_types;

    public abstract function createTable();

    public abstract function insertDefaults();

    public function insertDefaultRow($values)
    {
        $this->sql_helpers->insertIfNotExists($this->table_name, $this->columns, $values, $this->pdo_types);
    }
}