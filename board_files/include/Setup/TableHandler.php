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
    protected $columns_data;
    protected $schema_version;

    public abstract function setup();

    public abstract function createTable(array $other_tables = null);

    public abstract function insertDefaults();

    protected function insertDefaultRow(array $values)
    {
        $check_values = array();
        $check_columns = array();
        $check_pdo_types = array();
        $index = -1;

        foreach ($this->columns_data as $column_name => $info)
        {
            if ($info['row_check'] && isset($values[$index]))
            {
                $check_values[] = $values[$index];
                $check_columns[] = $column_name;
                $check_pdo_types[] = $info['pdo_type'];
            }

            ++$index;
        }

        if ($this->database->rowExists($this->table_name, $check_columns, $check_values, $check_pdo_types))
        {
            return false;
        }

        $insert_values = array();
        $insert_columns = array();
        $insert_pdo_types = array();
        $index = 0;

        foreach ($this->columns_data as $column_name => $info)
        {
            if ($info['auto_inc'])
            {
                continue;
            }

            $insert_values[] = $values[$index];
            $insert_columns[] = $column_name;
            $insert_pdo_types[] = $info['pdo_type'];
            ++$index;
        }

        $this->sql_helpers->compileExecuteInsert($this->table_name, $insert_columns, $insert_values, $insert_pdo_types);
    }

    public function verifyStructure()
    {
        $missing_columns = array();

        foreach ($this->columns as $column)
        {
            if (!$this->database->columnExists($this->table_name, $column))
            {
                $missing_columns[] = $column;
            }
        }

        return $missing_columns;
    }

    public function checkAndRepair()
    {
        if (!$this->database->tableExists($this->table_name))
        {
            $this->createTable();
            $this->insertDefaults();
            return true;
        }

        $missing = $this->verifyStructure();

        if (!empty($missing))
        {
            ;
        }
    }

    public function tableName(string $new_name = null)
    {
        if (!is_null($new_name))
        {
            $this->table_name = $new_name;
        }

        return $this->table_name;
    }

    public function copyFrom($source_table_name)
    {
        if ($this->database->executeFetch('SELECT 1 FROM "' . $this->table_name . '"', PDO::FETCH_COLUMN) === false)
        {
            $insert_query = 'INSERT INTO "' . $this->table_name . '" SELECT * FROM "' . $source_table_name . '"';
            $prepared = $this->database->prepare($insert_query);
            $this->database->executePrepared($prepared);
        }
    }

    public function schemaVersion()
    {
        return $this->schema_version;
    }
}