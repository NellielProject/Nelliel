<?php
$dbh = nel_get_db_handle();

function nel_init_db_connection()
{
    if (SQLTYPE === 'MYSQL')
    {
        $dsn = 'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DB . ';';
        $dbh = new PDO($dsn, MYSQL_USER, MYSQL_PASS);
        $dbh->exec("SET names '" . MYSQL_ENCODING . "'; SET SESSION sql_mode='ANSI';");
    }
    else if (SQLTYPE === 'SQLITE')
    {
        if (SQLITE_DB_PATH === '')
        {
            $path = SQLITE_DB_DEFAULT_PATH;
        }
        else
        {
            $path = SQLITE_DB_PATH;
        }

        $dbh = new PDO('sqlite:' . $path . SQLITE_DB_NAME);
        $dbh->exec('PRAGMA encoding = "' . SQLITE_ENCODING . '";');
    }
    else if (SQLTYPE === 'POSTGRES')
    {
        $dsn = 'pgsql:host=' . POSTGRES_HOST . ';port=' . POSTGRES_PORT . ';dbname=' . POSTGRES_DB . ';';
        $dbh = new PDO($dsn, POSTGRES_USER, POSTGRES_PASS);
        $dbh->exec("SET search_path TO " . POSTGRES_SCHEMA . "; SET names '" . POSTGRES_ENCODING . "';");
    }
    else
    {
        die("No valid database type specified in config. Can't do shit cap'n!");
    }

    if (ini_get('date.timezone') === '')
    {
        date_default_timezone_set('UTC');
    }

    return $dbh;
}

function nel_check_for_innodb()
{
    $dbh = nel_get_db_handle();
    $result = $dbh->query("SHOW ENGINES");
    $list = $result->fetchAll(PDO::FETCH_ASSOC);

    foreach ($list as $entry)
    {
        if ($entry['Engine'] === 'InnoDB' && ($entry['Support'] === 'DEFAULT' || $entry['Support'] === 'YES'))
        {
            return true;
        }
    }

    return false;
}

function nel_get_db_handle()
{
    static $database_handle;

    if (!isset($database_handle))
    {
        $database_handle = nel_init_db_connection();
    }

    return $database_handle;
}

function nel_database_exists($database)
{
    $dbh = nel_get_db_handle();

    if (SQLTYPE === 'SQLITE')
    {
        $result = $dbh->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name='" . $table . "'");
    }

    if (SQLTYPE === 'MYSQL')
    {
        $result = $dbh->query("SELECT schema_name FROM information_schema.schemata WHERE schema_name = '" . $database .
             "';");
    }

    if (SQLTYPE === 'POSTGRES')
    {
        $result = $dbh->query("SELECT nspname FROM pg_catalog.pg_namespace WHERE nspname = '" . $database . "';");
    }

    $test = $result->fetch(PDO::FETCH_NUM);

    if ($test[0] == $database)
    {
        return TRUE;
    }

    return FALSE;
}

function nel_table_exists($table)
{
    $dbh = nel_get_db_handle();

    if (SQLTYPE === 'SQLITE')
    {
        $result = $dbh->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name='" . $table . "'");
    }

    if (SQLTYPE === 'MYSQL')
    {
        $result = $dbh->query("SELECT table_name FROM information_schema.tables WHERE table_schema = '" . MYSQL_DB .
             "' AND table_name = '" . $table . "';");
    }

    if (SQLTYPE === 'POSTGRES')
    {
        $result = $dbh->query("SELECT table_name FROM information_schema.tables WHERE table_schema = '" .
             POSTGRES_SCHEMA . "' AND table_name = '" . $table . "';");
    }

    $test = $result->fetch(PDO::FETCH_NUM);

    if ($test[0] == $table)
    {
        return TRUE;
    }

    return FALSE;
}

function nel_table_fail($table)
{
    die('Creation of ' . $table . ' failed! Check database settings and config.php then retry installation.');
}