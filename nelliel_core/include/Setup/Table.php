<?php

declare(strict_types=1);


namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

abstract class Table
{
    protected $database;
    protected $sql_compatibility;
    protected $table_name;
    protected $columns_data = array();
    protected $schema_version = 1;

    public abstract function buildSchema(array $other_tables = null);

    public abstract function postCreate(array $other_tables = null);

    public abstract function insertDefaults();

    public function createTable(array $other_tables = null)
    {
        $schema = $this->buildSchema($other_tables);
        $created = $this->createTableQuery($schema, $this->table_name);

        if ($created)
        {
            $this->postCreate($other_tables);
            $this->updateVersionsTable();
            $this->insertDefaults();
        }
    }

    protected function updateVersionsTable()
    {
        if ($this->database->rowExists(NEL_VERSIONS_TABLE, ['id'], [$this->table_name]))
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . NEL_VERSIONS_TABLE . '" SET "current" = ? WHERE "id" = ? AND "type" = ?');
            $this->database->executePrepared($prepared, [$this->schema_version, $this->table_name, 'table']);
        }
        else
        {
            $prepared = $this->database->prepare(
                    'INSERT INTO "' . NEL_VERSIONS_TABLE . '" ("id", "type", "original", "current") VALUES
                    (?, ?, ?, ?)');
            $this->database->executePrepared($prepared,
                    [$this->table_name, 'table', $this->schema_version, $this->schema_version]);
        }
    }

    protected function insertDefaultRow(array $values)
    {
        $check_values = array();
        $check_columns = array();
        $check_pdo_types = array();
        $index = 0;

        foreach ($this->columns_data as $column_name => $info)
        {
            if ($info['auto_inc'])
            {
                continue;
            }

            if (!$info['auto_inc'] && $info['row_check'] && isset($values[$index]))
            {
                $check_values[] = $values[$index];
                $check_columns[] = $column_name;
                $check_pdo_types[] = $info['pdo_type'];
            }

            $index ++;
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

            if (!isset($values[$index]))
            {
                $index ++;
                continue;
            }

            $insert_values[] = $values[$index];
            $insert_columns[] = $column_name;
            $insert_pdo_types[] = $info['pdo_type'];
            $index ++;
        }

        $this->compileExecuteInsert($this->table_name, $insert_columns, $insert_values, $insert_pdo_types);
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

    public function createTableQuery($schema, $table_name)
    {
        if ($this->database->tableExists($table_name))
        {
            return false;
        }

        $result = $this->database->query($schema);

        if (!$result)
        {
            nel_derp(103,
                    sprintf(
                            _gettext(
                                    'Creation of %s failed! Check database settings and config.php then retry installation.'),
                            $table_name));
        }

        return $result;
    }

    public function compileExecuteInsert(string $table_name, array $columns, array $values, array $pdo_types = null)
    {
        $query = 'INSERT INTO "' . $table_name . '" (';

        foreach ($columns as $column)
        {
            $query .= '"' . $column . '", ';
        }

        $query = substr($query, 0, -2) . ') VALUES (';

        foreach ($columns as $column)
        {
            $query .= ':' . $column . ', ';
        }

        $query = substr($query, 0, -2) . ')';

        $prepared = $this->database->prepare($query);
        $count = count($columns);

        for ($i = 0; $i < $count; $i ++)
        {
            if (!is_null($pdo_types))
            {
                $prepared->bindValue(':' . $columns[$i], $values[$i], $pdo_types[$i]);
            }
            else
            {
                $prepared->bindValue(':' . $columns[$i], $values[$i]);
            }
        }

        $result = $this->database->executePrepared($prepared);
        return $result;
    }
}