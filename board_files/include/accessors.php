<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Because fuck passing an instance through half a dozen classes and/or functions
// just so one thing can access it. PHP gives us nice things and we're gonna use them!
//

function nel_get_database_handle()
{
    static $database;

    if (!isset($database))
    {
        $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);

        switch (SQLTYPE)
        {
            case 'MYSQL':
                $dsn = 'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DB . ';';
                $connection = new \Nelliel\NellielPDO($dsn, MYSQL_USER, MYSQL_PASS, $options);
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
                $connection = new PDO($dsn);
                $connection->exec('PRAGMA encoding = "' . SQLITE_ENCODING . '";');
                break;
            case 'POSTGRES':
                $dsn = 'pgsql:host=' . POSTGRES_HOST . ';port=' . POSTGRES_PORT . ';dbname=' . POSTGRES_DB . ';';
                $connection = new PDO($dsn, POSTGRES_USER, POSTGRES_PASS, $options);
                $connection->exec("SET search_path TO " . POSTGRES_SCHEMA . "; SET names '" . POSTGRES_ENCODING . "';");
                break;
            default:
                return false;
        }

        $database = $connection;
    }

    return $database;
}

function nel_authorize()
{
    static $authorize;

    if (!isset($authorize))
    {
        $authorize = new \Nelliel\Authorization();
    }

    return $authorize;
}

// Legacy. TODO: Remove when no longer accessed.
function nel_get_authorization()
{
    return nel_authorize();
}