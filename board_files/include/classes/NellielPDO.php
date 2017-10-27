<?php
namespace Nelliel;
use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

// Handle all the authorization functions
//
class NellielPDO extends PDO
{
    public function __construct($dsn, $username = null, $password = null, $options = array())
    {
        parent::__construct($dsn, $username, $password, $options);

        if (ini_get('date.timezone') === '')
        {
            date_default_timezone_set('UTC');
        }
    }

    public function databaseExists($database)
    {
        $result = null;

        switch (SQLTYPE)
        {
            case 'MYSQL':
                $result = $this->query(
                        "SELECT schema_name FROM information_schema.schemata WHERE schema_name = '" . $database . "';");
                break;
            case 'SQLITE':
                $result = $this->query(
                        "SELECT name FROM sqlite_master WHERE type = 'table' AND name='" . $database . "'");
                break;
            case 'POSTGRES':
                $result = $this->query(
                        "SELECT nspname FROM pg_catalog.pg_namespace WHERE nspname = '" . $database . "';");
                break;
            default:
                return false;
        }

        $test = $result->fetch(PDO::FETCH_NUM);
        return $test[0] == $database;
    }

    public function tableExists($table)
    {
        switch (SQLTYPE)
        {
            case 'MYSQL':
                $result = $this->query(
                        "SELECT table_name FROM information_schema.tables WHERE table_schema = '" . MYSQL_DB .
                                 "' AND table_name = '" . $table . "';");
                break;
            case 'SQLITE':
                $result = $this->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name='" . $table . "'");
                break;
            case 'POSTGRES':
                $result = $this->query(
                        "SELECT table_name FROM information_schema.tables WHERE table_schema = '" . POSTGRES_SCHEMA .
                                 "' AND table_name = '" . $table . "';");
                break;
            default:
                return false;
        }

        $test = $result->fetch(PDO::FETCH_NUM);
        return $test[0] == $table;
    }

    public function tableFail($table)
    {
        die('Creation of ' . $table . ' failed! Check database settings and config.php then retry installation.');
    }

    public function executePrepared($prepared, $parameters = null, $close_cursor = true)
    {
        $prepared->execute($parameters);

        if ($prepared !== false && $close_cursor)
        {
            $prepared->closeCursor();
        }

        return $prepared;
    }

    public function executePreparedFetch($prepared, $parameters = null, $fetch_style = PDO::ATTR_DEFAULT_FETCH_MODE, $close_cursor = true)
    {
        $prepared = $this->executePrepared($prepared, $parameters, false);

        if ($prepared !== false)
        {
            if ($fetch_style === PDO::FETCH_COLUMN)
            {
                $fetched_result = $prepared->fetchColumn();
            }
            else
            {
                $fetched_result = $prepared->fetch($fetch_style);
            }

            if ($close_cursor)
            {
                $prepared->closeCursor();
            }
        }
        else
        {
            $fetched_result = false;
        }

        return $fetched_result;
    }

    public function executePreparedFetchAll($prepared, $parameters = null, $fetch_style = PDO::ATTR_DEFAULT_FETCH_MODE)
    {
        $prepared = $this->executePrepared($prepared, $parameters, false);

        if ($prepared !== false)
        {
            $fetched_result = $prepared->fetchAll($fetch_style);
        }
        else
        {
            $fetched_result = false;
        }

        return $fetched_result;
    }

    public function generateParameterIds($column_names)
    {
        $identifiers = array();

        foreach ($column_names as $name)
        {
            array_push($identifiers, ':' . $name);
        }

        return $identifiers;
    }

    public function formatColumns($columns)
    {
        $column_count = count($columns);
        $columns_sql = '(';

        for ($i = 0; $i < $column_count; $i ++)
        {
            $columns_sql .= '"' . $columns[$i] . '"';

            if ($i < $column_count - 1)
            {
                $columns_sql .= ', ';
            }
            else
            {
                $columns_sql .= ')';
            }
        }

        return $columns_sql;
    }

    public function formatValues($values)
    {
        $values_count = count($values);
        $values_sql = '(';

        for ($i = 0; $i < $values_count; $i ++)
        {
            $values_sql .= $values[$i];

            if ($i < $values_count - 1)
            {
                $values_sql .= ', ';
            }
            else
            {
                $values_sql .= ')';
            }
        }

        return $values_sql;
    }

    public function buildBasicInsertQuery($table, $columns, $values)
    {
        $query = 'INSERT INTO "' . $table . '" ' . $this->formatColumns($columns) . ' VALUES ' .
                 $this->formatValues($values);
        return $query;
    }
}
