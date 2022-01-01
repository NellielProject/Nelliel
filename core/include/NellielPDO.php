<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;
use PDOStatement;

class NellielPDO extends PDO
{
    protected $config;

    function __construct(array $config, string $dsn, ?string $username = null, ?string $password = null,
        ?array $options = null)
    {
        parent::__construct($dsn, $username, $password, $options);
        $this->config = $config;
    }

    public function databaseExists(string $database_name): bool
    {
        switch ($this->sql_type) {
            case 'MYSQL':
                $prepared = $this->prepare('SELECT 1 FROM "information_schema"."schemata" WHERE "schema_name" = ?');
                $result = $this->executePreparedFetch($prepared, [$database_name], PDO::FETCH_COLUMN);
                break;

            case 'MARIADB':
                $prepared = $this->prepare('SELECT 1 FROM "information_schema"."schemata" WHERE "schema_name" = ?');
                $result = $this->executePreparedFetch($prepared, [$database_name], PDO::FETCH_COLUMN);
                break;

            case 'POSTGRESQL':
                $prepared = $this->prepare('SELECT 1 FROM "information_schema"."schemata" WHERE "catalog_name" = ?');
                $result = $this->executePreparedFetch($prepared, [$database_name], PDO::FETCH_COLUMN);
                break;

            case 'SQLITE':
                $result = true; // If database didn't exist, there would be no connection
                break;

            default:
                $result = false;
        }

        return $result !== false;
    }

    public function tableExists(string $table_name): bool
    {
        switch ($this->config['sqltype']) {
            case 'MYSQL':
                $prepared = $this->prepare(
                    'SELECT 1 FROM "information_schema"."tables" WHERE "table_schema" = ? AND "table_name" = ?');
                $result = $this->executePreparedFetch($prepared, [$this->config['database'], $table_name],
                    PDO::FETCH_COLUMN);
                break;

            case 'MARIADB':
                $prepared = $this->prepare(
                    'SELECT 1 FROM "information_schema"."tables" WHERE "table_schema" = ? AND "table_name" = ?');
                $result = $this->executePreparedFetch($prepared, [$this->config['database'], $table_name],
                    PDO::FETCH_COLUMN);
                break;

            case 'POSTGRESQL':
                $prepared = $this->prepare(
                    'SELECT 1 FROM "information_schema"."tables" WHERE "table_schema" = ? AND "table_name" = ?');
                $result = $this->executePreparedFetch($prepared, [$this->config['schema'], $table_name],
                    PDO::FETCH_COLUMN);
                break;

            case 'SQLITE':
                $prepared = $this->prepare('SELECT 1 FROM "sqlite_master" WHERE "type" = \'table\' AND "name" = ?');
                $result = $this->executePreparedFetch($prepared, [$table_name], PDO::FETCH_COLUMN);
                break;

            default:
                $result = false;
        }

        return $result !== false;
    }

    public function columnExists(string $table_name, string $column_name): bool
    {
        switch ($this->config['sqltype']) {
            case 'MYSQL':
                $prepared = $this->prepare(
                    'SELECT 1 FROM "information_schema"."columns" WHERE "table_schema" = ? AND "table_name" = ? AND "column_name" = ?');
                $result = $this->executePreparedFetch($prepared, [$this->config['database'], $table_name, $column_name],
                    PDO::FETCH_COLUMN);
                break;

            case 'MARIADB':
                $prepared = $this->prepare(
                    'SELECT 1 FROM "information_schema"."columns" WHERE "table_schema" = ? AND "table_name" = ? AND "column_name" = ?');
                $result = $this->executePreparedFetch($prepared, [$this->config['database'], $table_name, $column_name],
                    PDO::FETCH_COLUMN);
                break;

            case 'POSTGRESQL':
                $prepared = $this->prepare(
                    'SELECT 1 FROM "information_schema"."columns" WHERE "table_schema" = ? AND "table_name" = ? AND "column_name" = ?');
                $result = $this->executePreparedFetch($prepared, [$this->config['schema'], $table_name, $column_name],
                    PDO::FETCH_COLUMN);
                break;

            case 'SQLITE':
                // SQLite being speshul again
                $prepared = $this->prepare('PRAGMA table_info("' . $table_name . '")');
                $result1 = $this->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);
                $result = false;

                foreach ($result1 as $row) {
                    if ($row['name'] == $column_name) {
                        $result = true;
                        break;
                    }
                }

                break;

            default:
                $result = false;
        }

        return $result !== false;
    }

    public function rowExists(string $table_name, array $columns, array $values, array $pdo_types = null): bool
    {
        $query = 'SELECT 1 FROM "' . $table_name . '" WHERE ';
        $count = count($columns);
        $final_values = $values;
        $final_columns = $columns;

        for ($i = 0; $i < $count; $i ++) {
            if (is_null($values[$i])) {
                unset($final_columns[$i]);
                unset($final_values[$i]);
                continue;
            }
        }

        $final_columns = array_values($final_columns);
        $final_values = array_values($final_values);
        $count = count($final_columns);

        for ($i = 0; $i < $count; $i ++) {
            $query .= ' "' . $final_columns[$i] . '" = :' . $final_columns[$i] . ' AND ';
        }

        $query = utf8_substr($query, 0, -5);
        $prepared = $this->prepare($query);

        for ($i = 0; $i < $count; $i ++) {
            if (!is_null($pdo_types)) {
                $prepared->bindValue(':' . $final_columns[$i], $final_values[$i], $pdo_types[$i]);
            } else {
                $prepared->bindValue(':' . $final_columns[$i], $final_values[$i]);
            }
        }

        $result = $this->executePreparedFetch($prepared, $final_values, PDO::FETCH_COLUMN);
        return $result !== false;
    }

    public function executeFetch(string $query, int $fetch_style = PDO::FETCH_BOTH)
    {
        $result = $this->query($query);

        if ($result !== false) {
            if ($fetch_style === PDO::FETCH_COLUMN) {
                $fetched_result = $result->fetchColumn();
            } else {
                $fetched_result = $result->fetch($fetch_style);
            }
        } else {
            $fetched_result = false;
        }

        return $fetched_result;
    }

    public function executeFetchAll(string $query, int $fetch_style = PDO::FETCH_BOTH): array
    {
        $result = $this->query($query);

        if ($result !== false) {
            $fetched_result = $result->fetchAll($fetch_style);
        } else {
            $fetched_result = array();
        }

        return $fetched_result;
    }

    public function executePrepared(PDOStatement $prepared, ?array $parameters = null, bool $close_cursor = true): bool
    {
        if (is_null($parameters)) {
            $result = $prepared->execute();
        } else {
            $result = $prepared->execute($parameters);
        }

        if ($result !== false && $close_cursor) {
            $prepared->closeCursor();
        }

        return $result;
    }

    public function executePreparedFetch(PDOStatement $prepared, ?array $parameters = null,
        int $fetch_style = PDO::FETCH_BOTH, bool $close_cursor = true)
    {
        $result = $this->executePrepared($prepared, $parameters, false);

        if ($result !== false) {
            if ($fetch_style === PDO::FETCH_COLUMN) {
                $fetched_result = $prepared->fetchColumn();
            } else {
                $fetched_result = $prepared->fetch($fetch_style);
            }

            if ($close_cursor) {
                $prepared->closeCursor();
            }
        } else {
            $fetched_result = false;
        }

        return $fetched_result;
    }

    public function executePreparedFetchAll(PDOStatement $prepared, ?array $parameters = null,
        int $fetch_style = PDO::FETCH_BOTH): array
    {
        $result = $this->executePrepared($prepared, $parameters, false);

        if ($result !== false) {
            $fetched_result = $prepared->fetchAll($fetch_style);
        } else {
            $fetched_result = array();
        }

        return $fetched_result;
    }

    public function config(): array
    {
        return $this->config;
    }
}
