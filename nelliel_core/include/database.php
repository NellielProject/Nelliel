<?php
defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NellielPDO;

//
// Access point for database connections.
// Database connections can be added, retrieved or removed using the hash table ID ($input).
//
function nel_database($input = null, $wut_do = null): ?NellielPDO
{
    // TODO: Actually handle secondary databases
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

    return null;
}

//
// Initialize new database connections using the NellielPDO class.
//
function nel_new_database_connection(string $dsn, ?string $username = null, ?string $password = null,
        ?array $options = null): NellielPDO
{
    // Just in case things go wrong we want to avoid sensitive info leaking
    try
    {
        $connection = new NellielPDO($dsn, $username, $password, $options);
        return $connection;
    }
    catch (PDOException $exception)
    {
        nel_derp(49, _gettext('Error connecting to database.'));
    }
}

//
// Initialize the default/main database connection here.
//
function nel_default_database_connection(): NellielPDO
{
    $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);

    switch (NEL_SQLTYPE)
    {
        case 'MYSQL':
            $dsn = 'mysql:host=' . NEL_MYSQL_HOST . ';port=' . NEL_MYSQL_PORT . ';dbname=' . NEL_MYSQL_DB . ';charset=' .
                    NEL_MYSQL_ENCODING . ';';
            $connection = nel_new_database_connection($dsn, NEL_MYSQL_USER, NEL_MYSQL_PASS, $options);
            $connection->exec("SET SESSION sql_mode='ANSI';");
            break;

        case 'MARIADB':
            $dsn = 'mysql:host=' . NEL_MARIADB_HOST . ';port=' . NEL_MARIADB_PORT . ';dbname=' . NEL_MARIADB_DB .
                    ';charset=' . NEL_MARIADB_ENCODING . ';';
            $connection = nel_new_database_connection($dsn, NEL_MARIADB_USER, NEL_MARIADB_PASS, $options);
            $connection->exec("SET SESSION sql_mode='ANSI';");
            break;

        case 'POSTGRESQL':
            $dsn = 'pgsql:host=' . NEL_POSTGRESQL_HOST . ';port=' . NEL_POSTGRESQL_PORT . ';dbname=' . NEL_POSTGRESQL_DB .
                    ';';
            $connection = nel_new_database_connection($dsn, NEL_POSTGRESQL_USER, NEL_POSTGRESQL_PASS, $options);
            $connection->exec(
                    "SET search_path TO " . NEL_POSTGRESQL_SCHEMA . "; SET names '" . NEL_POSTGRESQL_ENCODING . "';");
            break;

        case 'SQLITE':
            if (NEL_SQLITE_DB_PATH === '')
            {
                $path = NEL_SQLITE_DB_DEFAULT_PATH;
            }
            else
            {
                $path = NEL_SQLITE_DB_PATH;
            }

            $dsn = 'sqlite:' . $path . NEL_SQLITE_DB_NAME;
            $connection = nel_new_database_connection($dsn);
            $connection->exec('PRAGMA encoding = "' . NEL_SQLITE_ENCODING . '"; PRAGMA foreign_keys = ON;');
            break;

        default:
            nel_derp(50, _gettext('Invalid database type given in config.'));
    }

    $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $connection->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);
    return $connection;
}
