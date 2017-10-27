<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Initialize new database connections using the NellielPDO class.
//

function nel_new_database_connection($dsn, $username = null, $password = null, $options = array())
{
    $connection = new \Nelliel\NellielPDO($dsn, $username, $password, $options);
    return $connection;
}

//
// Initialize the default/main database connection here.
//

function nel_default_database_connection()
{
    $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);

    switch (SQLTYPE)
    {
        case 'MYSQL':
            $dsn = 'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DB . ';';
            $connection = nel_new_database_connection($dsn, MYSQL_USER, MYSQL_PASS, $options);
            $connection->exec("SET names '" . MYSQL_ENCODING . "'; SET SESSION sql_mode='ANSI';");
            break;

        case 'SQLITE':
            if (SQLITE_DB_PATH === '')
            {
                $path = SQLITE_DB_DEFAULT_PATH;
            }
            else
            {
                $path = SQLITE_DB_PATH;
            }

            $dsn = 'sqlite:' . $path . SQLITE_DB_NAME;
            $connection = nel_new_database_connection($dsn);
            $connection->exec('PRAGMA encoding = "' . SQLITE_ENCODING . '";');
            break;

        case 'POSTGRES':
            $dsn = 'pgsql:host=' . POSTGRES_HOST . ';port=' . POSTGRES_PORT . ';dbname=' . POSTGRES_DB . ';';
            $connection = nel_new_database_connection($dsn, POSTGRES_USER, POSTGRES_PASS, $options);
            $connection->exec("SET search_path TO " . POSTGRES_SCHEMA . "; SET names '" . POSTGRES_ENCODING . "';");
            break;

        default:
            return false;
    }

    return $connection;
}