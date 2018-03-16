<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Access point for database connections.
// Database connections can be added, retrieved or removed using the hash table ID.
//

function nel_database($input = null, $wut_do = null)
{
    static $databases = array();
    static $default_database;

    // No arguments provided: send back the default database
    if (is_null($wut_do) && is_null($input))
    {
        if (!isset($default_database))
        {
            $default_database = nel_default_database_connection();
        }

        return $default_database;
    }

    // ID provided but no instructions: send back the requested database if available
    if (is_null($wut_do) && !is_null($input))
    {
        if (array_key_exists($input, $databases))
        {
            return $databases[$input];
        }
    }

    // Both ID and instructions provided
    if (!is_null($wut_do) && !is_null($input))
    {
        switch ($wut_do)
        {
            case 'store':
                $id = spl_object_hash($input);
                $databases[$id] = $input;
                return $id;
                break;

            case 'retrieve':
                if (array_key_exists($input, $databases))
                {
                    return $databases[$input];
                }

                break;

            case 'identify':
                if (in_array($input, $databases))
                {
                    return array_search($input, $databases);
                }
                break;

            case 'remove':
                if (array_key_exists($input, $databases))
                {
                    unset($input);
                    return true;
                }

                break;
        }
    }

    return false;
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
            $dsn = 'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DB . ';charset=' . MYSQL_ENCODING . ';';
            $connection = nel_new_database_connection($dsn, MYSQL_USER, MYSQL_PASS, $options);

            if (version_compare(PHP_VERSION, '5.3.6', '<')) {
                $connection->exec("SET names '" . MYSQL_ENCODING . "';");
            }

            $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $connection->exec("SET SESSION sql_mode='ANSI';");
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
            $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $connection->exec('PRAGMA encoding = "' . SQLITE_ENCODING . '";');
            break;

        case 'POSTGRES':
            $dsn = 'pgsql:host=' . POSTGRES_HOST . ';port=' . POSTGRES_PORT . ';dbname=' . POSTGRES_DB . ';';
            $connection = nel_new_database_connection($dsn, POSTGRES_USER, POSTGRES_PASS, $options);
            $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $connection->exec("SET search_path TO " . POSTGRES_SCHEMA . "; SET names '" . POSTGRES_ENCODING . "';");
            break;

        default:
            return false;
    }

    return $connection;
}