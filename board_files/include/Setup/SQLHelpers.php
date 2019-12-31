<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class SQLHelpers
{
    private $database;

    function __construct($database)
    {
        $this->database = $database;
    }

    public function autoincrementColumn($int_column)
    {
        $auto = '';

        if (SQLTYPE === 'MYSQL')
        {
            $auto = 'AUTO_INCREMENT';
        }
        else if (SQLTYPE === 'MARIADB')
        {
            $auto = 'AUTO_INCREMENT';
        }
        else if (SQLTYPE === 'POSTGRESQL')
        {
            if ($int_column === 'SMALLINT')
            {
                $int_column = 'SMALLSERIAL';
            }

            if ($int_column === 'INTEGER')
            {
                $int_column = 'SERIAL';
            }

            if ($int_column === 'BIGINT')
            {
                $int_column = 'BIGSERIAL';
            }
        }
        else if (SQLTYPE === 'SQLITE')
        {
            $auto = 'AUTOINCREMENT';
        }

        return [$int_column, $auto];
    }

    public function sqlAlternatives($datatype, $length)
    {
        if (SQLTYPE === 'MYSQL')
        {
            if ($datatype === "BINARY")
            {
                return 'BINARY(' . $length . ')';
            }
            else if ($datatype === "VARBINARY")
            {
                return 'VARBINARY(' . $length . ')';
            }
        }
        else if (SQLTYPE === 'MARIADB')
        {
            if ($datatype === "BINARY")
            {
                return 'BINARY(' . $length . ')';
            }
            else if ($datatype === "VARBINARY")
            {
                return 'VARBINARY(' . $length . ')';
            }
        }
        else if (SQLTYPE === 'POSTGRESQL')
        {
            if ($datatype === "BINARY")
            {
                return 'BYTEA';
            }
            else if ($datatype === "VARBINARY")
            {
                return 'BYTEA';
            }
        }
        else if (SQLTYPE === 'SQLITE')
        {
            if ($datatype === "BINARY")
            {
                return 'BLOB';
            }
            else if ($datatype === "VARBINARY")
            {
                return 'BLOB';
            }
        }
    }

    public function tableOptions()
    {
        $options = '';

        if (SQLTYPE === 'MYSQL')
        {
            $options = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
            $options .= ' ENGINE = InnoDB';
        }
        else if (SQLTYPE === 'MARIADB')
        {
            $options = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
            $options .= ' ENGINE = InnoDB';
        }

        return $options . ';';
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
            $this->database->tableFail($table_name);
        }

        return $result;
    }


    public function tableFail($table)
    {
        nel_derp(103,
                sprintf(
                        _gettext(
                                'Creation of %s failed! Check database settings and config.php then retry installation.'),
                        $table));
    }


    public function compileExecuteInsert(string $table_name, array $columns, array $values, array $pdo_types = null)
    {
        $query = 'INSERT INTO "' . $table_name . '" (';

        foreach($columns as $column)
        {
            $query .= '"' . $column . '", ';
        }

        $query = substr($query, 0, -2) . ') VALUES (';

        foreach($columns as $column)
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

    public function limitOffset(int $limit, int $offset)
    {
        if (SQLTYPE === 'MYSQL' || SQLTYPE === 'SQLITE' || SQLTYPE === 'MARIADB')
        {
            return 'LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        else if (SQLTYPE === 'POSTGRESQL')
        {
            if ($datatype === "BINARY")
            {
                return 'BINARY(' . $length . ')';
            }
            else if ($datatype === "VARBINARY")
            {
                return 'VARBINARY(' . $length . ')';
            }
        }
    }
}