<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Access point for database connections.
// Database connections can be added, retrieved or removed using the hash table ID ($input).
//
function nel_database($input = null, $wut_do = null)
{
    // TODO: Actually create/remove/handle secondary databases
    static $databases = array();
    static $default_database_key = null;

    $current_database = false;

    if (is_null($input))
    {
        if (!array_key_exists($default_database_key, $databases))
        {
            $new_database = nel_default_database_connection();
            $default_database_key = spl_object_hash($new_database);
            $databases[$default_database_key] = $new_database;
        }

        $current_database = $databases[$default_database_key];
    }
    else
    {
        if (array_key_exists($input, $databases))
        {
            $current_database = $databases[$input];
        }
    }

    if (is_null($wut_do) || $current_database === false)
    {
        return $current_database;
    }
    else
    {
        switch ($wut_do)
        {
            case 'version':
                if (array_key_exists($input, $databases))
                {
                    return $databases[$input]->getAttribute(PDO::ATTR_SERVER_VERSION);
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
            $dsn = 'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DB . ';charset=' .
                 MYSQL_ENCODING . ';';
            $connection = nel_new_database_connection($dsn, MYSQL_USER, MYSQL_PASS, $options);
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

    $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $connection->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);
    return $connection;
}
