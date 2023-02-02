<?php
declare(strict_types = 1);

namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

abstract class Table
{
    protected $database;
    protected $sql_compatibility;
    protected $table_name;
    protected $columns_data = array();
    protected $column_types = array(); // Stores column type data for typecasting and PDO binds
    protected $column_checks = array(); // Stores info for table and row check functions
    protected $schema_version = 1;

    public abstract function buildSchema(array $other_tables = null);

    public abstract function postCreate(array $other_tables = null);

    public abstract function insertDefaults();

    public function createTable(array $other_tables = null)
    {
        $schema = $this->buildSchema($other_tables);
        $created = $this->createTableQuery($schema, $this->table_name);

        if ($created) {
            $this->postCreate($other_tables);
            $this->updateVersionsTable();
            $this->insertDefaults();
        }
    }

    protected function updateVersionsTable()
    {
        if ($this->database->rowExists(NEL_VERSIONS_TABLE, ['id'], [$this->table_name])) {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_VERSIONS_TABLE . '" SET "current" = ? WHERE "id" = ? AND "type" = ?');
            $this->database->executePrepared($prepared, [$this->schema_version, $this->table_name, 'table']);
        } else {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_VERSIONS_TABLE . '" ("id", "type", "original", "current") VALUES
                    (?, ?, ?, ?)');
            $this->database->executePrepared($prepared,
                [$this->table_name, 'table', $this->schema_version, $this->schema_version]);
        }
    }

    public function rowExists(array $data): bool
    {
        $check_values = array();
        $check_columns = array();
        $check_pdo_types = array();

        foreach ($this->column_checks as $column_name => $info) {
            if ($info['row_check'] && isset($data[$column_name])) {
                $check_values[] = $data[$column_name];
                $check_columns[] = $column_name;
                $check_pdo_types[] = $this->column_types[$column_name]['pdo_type'];
            }
        }

        return $this->database->rowExists($this->table_name, $check_columns, $check_values, $check_pdo_types);
    }

    public function insertDefaultRow(array $values)
    {
        $sql_helpers = nel_utilities()->sqlHelpers();
        $data = array();
        $update_columns = array();
        $update_values = array();
        $update_pdo_types = array();
        $where_columns = array();
        $where_keys = array();
        $where_values = array();
        $where_pdo_types = array();
        $index = 0;

        foreach ($this->column_checks as $column_name => $info) {
            if ($info['auto_inc']) {
                continue;
            }

            if ($this->column_checks[$column_name]['row_check'] && isset($values[$index])) {
                $where_columns[] = $column_name;
                $where_keys[] = $column_name;
                $where_values[] = $values[$index];
                $where_pdo_types[] = $this->column_types[$column_name]['pdo_type'];
            }

            if (isset($values[$index])) {
                $data[$column_name] = $values[$index];
            }

            if ($info['update'] ?? false) {
                $update_columns[] = $column_name;
                $update_values[] = $values[$index];
                $update_pdo_types[] = $this->column_types[$column_name]['pdo_type'];
            }

            $index ++;
        }

        if ($this->rowExists($data)) {

            if (empty($update_columns)) {
                return;
            }

            $prepared = $sql_helpers->buildPreparedUpdate($this->table_name, $update_columns, $where_columns,
                $where_keys);
            $sql_helpers->bindToPrepared($prepared, array_keys(array_merge($update_columns, $where_columns)),
                array_merge($update_values, $where_values), array_merge($update_pdo_types, $where_pdo_types));
            $this->database->executePrepared($prepared);
            return;
        }

        $insert_values = array();
        $insert_columns = array();
        $insert_pdo_types = array();
        $index = 0;

        foreach ($this->column_types as $column_name => $info) {
            if ($this->column_checks[$column_name]['auto_inc']) {
                continue;
            }

            if (!isset($values[$index])) {
                $index ++;
                continue;
            }

            $insert_values[] = $values[$index];
            $insert_columns[] = $column_name;
            $insert_pdo_types[] = $this->column_types[$column_name]['pdo_type'];
            $index ++;
        }

        $prepared = $sql_helpers->buildPreparedInsert($this->table_name, $insert_columns);
        $sql_helpers->bindToPrepared($prepared, array_keys($insert_columns), $insert_values, $insert_pdo_types);
        $this->database->executePrepared($prepared);
    }

    public function verifyStructure()
    {
        $missing_columns = array();

        foreach ($this->columns as $column) {
            if (!$this->database->columnExists($this->table_name, $column)) {
                $missing_columns[] = $column;
            }
        }

        return $missing_columns;
    }

    public function checkAndRepair()
    {
        if (!$this->database->tableExists($this->table_name)) {
            $this->createTable();
            $this->insertDefaults();
            return true;
        }

        $missing = $this->verifyStructure();

        if (!empty($missing)) {
            ;
        }
    }

    public function tableName(string $new_name = null)
    {
        if (!is_null($new_name)) {
            $this->table_name = $new_name;
        }

        return $this->table_name;
    }

    public function copyFrom($source_table_name, array $columns = array())
    {
        $column_list = '';

        if (empty($columns)) {
            $column_list = '*';
        } else {
            foreach ($columns as $column) {
                $column_list .= '"' . $column . '",';
            }

            $column_list = rtrim($column_list, ',');
        }

        if ($this->database->executeFetch('SELECT 1 FROM "' . $this->table_name . '"', PDO::FETCH_COLUMN) === false) {
            $insert_query = 'INSERT INTO "' . $this->table_name . '" SELECT ' . $column_list . ' FROM "' .
                $source_table_name . '"';
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
        if ($this->database->tableExists($table_name)) {
            return false;
        }

        $result = $this->database->query($schema);

        if (!$result) {
            nel_derp(103,
                sprintf(
                    _gettext('Creation of table %s failed! Check database settings and config.php then retry installation.'),
                    $table_name));
        }

        return $result;
    }

    public function columnTypes(): array
    {
        return $this->column_types;
    }

    public function getPDOTypes(array $data): array
    {
        $keys = array_keys($data);
        $types = array();

        foreach ($keys as $key) {
            if (isset($this->column_types[$key])) {
                $types[] = $this->column_types[$key]['pdo_type'];
            }
        }

        return $types;
    }

    public function filterColumns(array $data): array
    {
        $keys = array_keys($data);
        $filtered = array();

        foreach ($keys as $key) {
            if (isset($this->column_checks[$key])) {
                $filtered[$key] = $data[$key];
            }
        }

        return $filtered;
    }
}