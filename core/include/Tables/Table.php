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
    protected $column_checks = array();

    // Stores info for table and row check functions
    public abstract function buildSchema(array $other_tables = null);

    public abstract function postCreate(array $other_tables = null);

    public abstract function insertDefaults();

    public function createTable(array $other_tables = null)
    {
        $schema = $this->buildSchema($other_tables);

        if ($this->database->tableExists($this->table_name)) {
            return false;
        }

        $result = $this->database->query($schema);

        if (!$result) {
            nel_derp(103,
                sprintf(
                    _gettext(
                        'Creation of table %s failed! Check database settings and config.php then retry installation.'),
                    $this->table_name));
        }

        $this->postCreate($other_tables);
        $this->updateVersionsTable();
        $this->insertDefaults();
    }

    protected function updateVersionsTable(): void
    {
        if ($this->database->rowExists(NEL_VERSIONS_TABLE, ['id'], [$this->table_name])) {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_VERSIONS_TABLE . '" SET "current" = ? WHERE "id" = ? AND "type" = ?');
            $this->database->executePrepared($prepared, [static::SCHEMA_VERSION, $this->table_name, 'table']);
        } else {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_VERSIONS_TABLE . '" ("id", "type", "original", "current") VALUES
                    (?, ?, ?, ?)');
            $this->database->executePrepared($prepared,
                [$this->table_name, 'table', static::SCHEMA_VERSION, static::SCHEMA_VERSION]);
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
                $check_pdo_types[] = static::PDO_TYPES[$column_name];
            }
        }

        return $this->database->rowExists($this->table_name, $check_columns, $check_values, $check_pdo_types);
    }

    public function insertDefaultRow(array $values): void
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
                $where_pdo_types[] = static::PDO_TYPES[$column_name];
            }

            if (isset($values[$index])) {
                $data[$column_name] = $values[$index];
            }

            if ($info['update'] ?? false) {
                $update_columns[] = $column_name;
                $update_values[] = $values[$index];
                $update_pdo_types[] = static::PDO_TYPES[$column_name];
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

        foreach (static::PDO_TYPES as $column_name => $pdo_type) {
            if ($this->column_checks[$column_name]['auto_inc']) {
                continue;
            }

            if (!isset($values[$index])) {
                $index ++;
                continue;
            }

            $insert_values[] = $values[$index];
            $insert_columns[] = $column_name;
            $insert_pdo_types[] = static::PDO_TYPES[$column_name];
            $index ++;
        }

        $prepared = $sql_helpers->buildPreparedInsert($this->table_name, $insert_columns);
        $sql_helpers->bindToPrepared($prepared, array_keys($insert_columns), $insert_values, $insert_pdo_types);
        $this->database->executePrepared($prepared);
    }

    public function tableName(string $new_name = null): string
    {
        if (!is_null($new_name)) {
            $this->table_name = $new_name;
        }

        return $this->table_name;
    }

    public function copyFrom($source_table_name, array $columns = array()): void
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

    public static function getPHPTypesForData(array $data): array
    {
        return array_values(array_intersect_key(static::PHP_TYPES, $data));
    }

    public static function getPDOTypesForData(array $data): array
    {
        return array_values(array_replace($data, array_intersect_key(static::PDO_TYPES, $data)));
    }

    public static function typeCastValue(string $column_name, $value)
    {
        if (isset(static::PDO_TYPES[$column_name])) {
            return nel_typecast($value, static::PHP_TYPES[$column_name]);
        }

        return $value;
    }

    public static function typeCastData(array $data, bool $filter = false): array
    {
        $typed_data = array();

        if ($filter) {
            $data = static::filterData($data);
        }

        foreach ($data as $column_name => $value) {
            $typed_data[$column_name] = static::typeCastValue($column_name, $value);
        }

        return $typed_data;
    }

    public static function filterData(array $data): array
    {
        return array_intersect_key($data, static::PHP_TYPES);
    }
}