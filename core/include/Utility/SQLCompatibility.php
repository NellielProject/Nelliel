<?php
declare(strict_types = 1);

namespace Nelliel\Utility;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class SQLCompatibility
{
    private $database;
    private $sqltype;

    function __construct($database)
    {
        $this->database = $database;
        $this->sqltype = $this->database->config()['sqltype'];
    }

    public function autoincrementColumn(string $int_column, bool $primary_declaration): array
    {
        $auto = '';

        if ($this->sqltype === 'MYSQL') {
            $auto = 'AUTO_INCREMENT';
        } else if ($this->sqltype === 'MARIADB') {
            $auto = 'AUTO_INCREMENT';
        } else if ($this->sqltype === 'POSTGRESQL') {
            if ($int_column === 'SMALLINT') {
                $int_column = 'SMALLSERIAL';
            }

            if ($int_column === 'INTEGER') {
                $int_column = 'SERIAL';
            }

            if ($int_column === 'BIGINT') {
                $int_column = 'BIGSERIAL';
            }
        } else if ($this->sqltype === 'SQLITE') {
            if($primary_declaration) {
                $auto = 'AUTOINCREMENT';
            } else {
                $auto = '';
            }
        }

        return [$int_column, $auto];
    }

    public function sqlAlternatives($datatype, $length)
    {
        if ($this->sqltype === 'MYSQL') {
            if ($datatype === "BINARY") {
                return 'BINARY(' . $length . ')';
            } else if ($datatype === "VARBINARY") {
                return 'VARBINARY(' . $length . ')';
            }
        } else if ($this->sqltype === 'MARIADB') {
            if ($datatype === "BINARY") {
                return 'BINARY(' . $length . ')';
            } else if ($datatype === "VARBINARY") {
                return 'VARBINARY(' . $length . ')';
            }
        } else if ($this->sqltype === 'POSTGRESQL') {
            if ($datatype === "BINARY") {
                return 'BYTEA';
            } else if ($datatype === "VARBINARY") {
                return 'BYTEA';
            }
        } else if ($this->sqltype === 'SQLITE') {
            if ($datatype === "BINARY") {
                return 'BLOB';
            } else if ($datatype === "VARBINARY") {
                return 'BLOB';
            }
        }
    }

    public function tableOptions()
    {
        $options = '';

        if ($this->sqltype === 'MYSQL') {
            $options = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
            $options .= ' ENGINE = InnoDB';
        } else if ($this->sqltype === 'MARIADB') {
            $options = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
            $options .= ' ENGINE = InnoDB';
        }

        return $options . ';';
    }

    public function limitOffset($limit, $offset)
    {
        if ($this->sqltype === 'MYSQL' || $this->sqltype === 'SQLITE' || $this->sqltype === 'MARIADB' ||
            $this->sqltype === 'POSTGRESQL') {
            return 'LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
    }
}