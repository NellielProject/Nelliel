<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

abstract class TableHandler
{
    protected $database;
    protected $sql_helpers;
    protected $table_name;
    protected $columns;
    protected $column_names;
    protected $pdo_types;
    protected $increment_column;
    protected $other_tables;

    public abstract function setup();

    public abstract function createTable(array $other_tables = null);

    public abstract function insertDefaults();

    protected function splitColumnInfo()
    {
        foreach ($this->columns as $column_name => $info)
        {
            if($info['auto_inc'])
            {
                continue;
            }

            $this->column_names[] = $column_name;
            $this->pdo_types[] = $info['pdo_type'];
        }
    }

    protected function insertDefaultRow(array $values)
    {
        $this->sql_helpers->insertIfNotExists($this->table_name, $this->column_names, $values, $this->pdo_types);
    }

    public function verifyStructure()
    {
        $missing_columns = array();

        foreach($this->columns as $column)
        {
            if(!$this->database->columnExists($this->table_name, $column))
            {
                $missing_columns[] = $column;
            }
        }

        return $missing_columns;
    }

    public function checkAndRepair()
    {
        if(!$this->database->tableExists($this->table_name))
        {
            $this->createTable();
            $this->insertDefaults();
            return true;
        }

        $missing = $this->verifyStructure();

        if(!empty($missing))
        {
            ;
        }
    }

    public function tableName(string $new_name = null)
    {
        if(!is_null($new_name))
        {
            $this->table_name = $new_name;
        }

        return $this->table_name;
    }

    public function copyFrom($source_table_name)
    {
        if($this->database->executeFetch('SELECT 1 FROM "' . $this->table_name . '"', PDO::FETCH_COLUMN) ===  false)
        {
            $insert_query = 'INSERT INTO "' . $this->table_name . '" SELECT * FROM "' . $source_table_name . '"';
            $prepared = $this->database->prepare($insert_query);
            $this->database->executePrepared($prepared);
        }
    }
}