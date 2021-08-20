<?php
declare(strict_types = 1);

namespace Nelliel;

use PDOStatement;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class SQLHelpers
{
    private $database;

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    public function buildPreparedInsert(string $table_name, array $column_list): PDOStatement
    {
        $query = 'INSERT INTO "' . $table_name . '" (';
        $columns = '';
        $values = '';
        $column_count = count($column_list);
        $multiple = $column_count > 1;
        $limit = $column_count - 1;

        for ($i = 0; $i <= $limit; $i ++)
        {
            $columns .= '"' . $column_list[$i] . '"';
            $values .= ':' . $column_list[$i];

            if ($multiple && $i < $limit)
            {
                $columns .= ', ';
                $values .= ', ';
            }
        }

        $query .= $columns . ') VALUES (' . $values . ')';
        return $this->database->prepare($query);
    }

    public function buildPreparedUpdate(string $table_name, array $column_list, array $where_columns, array $where_keys): PDOStatement
    {
        $query = 'UPDATE "' . $table_name . '" SET ';
        $columns = '';
        $where = ' WHERE ';
        $column_count = count($column_list);
        $multiple = $column_count > 1;
        $limit = $column_count - 1;

        for ($i = 0; $i < $column_count; $i ++)
        {
            $columns .= '"' . $column_list[$i] . '" =  :' . $column_list[$i];

            if ($multiple && $i < $limit)
            {
                $columns .= ', ';
            }
        }

        $where_count = count($where_columns);
        $multiple = $where_count > 1;
        $limit = $where_count - 1;

        for ($i = 0; $i < $where_count; $i ++)
        {
            $where .= '"' . $where_columns[$i] . '" = :' . $where_keys[$i];

            if ($multiple && $i < $limit)
            {
                $where .= ' AND ';
            }
        }

        $query .= $columns . $where;
        return $this->database->prepare($query);
    }

    public function bindToPrepared(PDOStatement $prepared, array $keys, array $values, array $pdo_types = null): void
    {
        $count = count($keys);

        for ($i = 0; $i < $count; $i ++)
        {
            if (!is_null($pdo_types))
            {
                $prepared->bindValue(':' . $keys[$i], $values[$i], $pdo_types[$i]);
            }
            else
            {
                $prepared->bindValue(':' . $keys[$i], $values[$i]);
            }
        }
    }
}