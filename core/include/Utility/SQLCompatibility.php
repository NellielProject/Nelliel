<?php
declare(strict_types = 1);

namespace Nelliel\Utility;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class SQLCompatibility
{
    private $database;

    function __construct($database)
    {
        $this->database = $database;
    }

    public function autoincrementColumn($int_column)
    {
        $auto = '';

        if (NEL_SQLTYPE === 'MYSQL')
        {
            $auto = 'AUTO_INCREMENT';
        }
        else if (NEL_SQLTYPE === 'MARIADB')
        {
            $auto = 'AUTO_INCREMENT';
        }
        else if (NEL_SQLTYPE === 'POSTGRESQL')
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
        else if (NEL_SQLTYPE === 'SQLITE')
        {
            $auto = 'AUTOINCREMENT';
        }

        return [$int_column, $auto];
    }

    public function sqlAlternatives($datatype, $length)
    {
        if (NEL_SQLTYPE === 'MYSQL')
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
        else if (NEL_SQLTYPE === 'MARIADB')
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
        else if (NEL_SQLTYPE === 'POSTGRESQL')
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
        else if (NEL_SQLTYPE === 'SQLITE')
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

        if (NEL_SQLTYPE === 'MYSQL')
        {
            $options = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
            $options .= ' ENGINE = InnoDB';
        }
        else if (NEL_SQLTYPE === 'MARIADB')
        {
            $options = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
            $options .= ' ENGINE = InnoDB';
        }

        return $options . ';';
    }

    public function limitOffset($limit, $offset)
    {
        if (NEL_SQLTYPE === 'MYSQL' || NEL_SQLTYPE === 'SQLITE' || NEL_SQLTYPE === 'MARIADB' ||
                NEL_SQLTYPE === 'POSTGRESQL')
        {
            return 'LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
    }

    public function return(string $sqltype): string
    {
        if ($sqltype === 'MYSQL' || $sqltype === 'MARIADB')
        {
            return 'RETURN';
        }
        else if ($sqltype === 'POSTGRESQL' || $sqltype === 'SQLITE')
        {
            return 'RETURNING';
        }
        else
        {
            return 'RETURN';
        }
    }
}