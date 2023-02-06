<?php
declare(strict_types = 1);

namespace Nelliel\Utility;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;
use PDOStatement;

class SQLHelpers
{
    private $database;

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    public function buildPreparedInsert(string $table_name, array $column_list, array $column_named = array()): PDOStatement
    {
        $named = !empty($column_named);
        $query = 'INSERT INTO "' . $table_name . '" (';
        $columns = '';
        $values = '';
        $column_count = count($column_list);
        $multiple = $column_count > 1;
        $limit = $column_count - 1;

        for ($i = 0; $i <= $limit; $i ++) {
            $columns .= '"' . $column_list[$i] . '"';

            if ($named) {
                $values .= $column_named[$i];
            } else {
                $values .= '?';
            }

            if ($multiple && $i < $limit) {
                $columns .= ', ';
                $values .= ', ';
            }
        }

        $query .= $columns . ') VALUES (' . $values . ')';
        return $this->database->prepare($query);
    }

    public function buildPreparedUpdate(string $table_name, array $column_list, array $where_columns, array $where_keys,
        array $column_named = array(), array $where_named = array()): PDOStatement
    {
        $named = !empty($column_named) && !empty($where_named);
        $query = 'UPDATE "' . $table_name . '" SET ';
        $columns = '';
        $where = ' WHERE ';
        $column_count = count($column_list);
        $multiple = $column_count > 1;
        $limit = $column_count - 1;

        for ($i = 0; $i < $column_count; $i ++) {
            if ($named) {
                $placeholder = $column_named[$i];
            } else {
                $placeholder = '?';
            }

            $columns .= '"' . $column_list[$i] . '" = ' . $placeholder;

            if ($multiple && $i < $limit) {
                $columns .= ', ';
            }
        }

        $where_count = count($where_columns);
        $multiple = $where_count > 1;
        $limit = $where_count - 1;

        for ($i = 0; $i < $where_count; $i ++) {
            if ($named) {
                $placeholder = $where_named[$i];
            } else {
                $placeholder = '?';
            }

            $where .= '"' . $where_columns[$i] . '" = ' . $placeholder;

            if ($multiple && $i < $limit) {
                $where .= ' AND ';
            }
        }

        $query .= $columns . $where;
        return $this->database->prepare($query);
    }

    public function bindToPrepared(PDOStatement $prepared, array $keys, array $values, array $pdo_types = null): void
    {
        $count = count($keys);

        for ($i = 0; $i < $count; $i ++) {
            if (is_int($keys[$i])) {
                $bind_key = $i + 1;
            } else {
                $bind_key = $keys[$i];
            }

            if (!is_null($pdo_types)) {
                $prepared->bindValue($bind_key, $values[$i], $pdo_types[$i]);
            } else {
                $prepared->bindValue($bind_key, $values[$i]);
            }
        }
    }

    public function parameterize(array $strings): array
    {
        $parameterize = function ($string) {
            return ':' . $string;
        };

        return array_map($parameterize, $strings);
    }
}