<?php

$dbh = nel_get_db_handle();

function nel_init_db_connection()
{
    $dbh;

    if (SQLTYPE === 'MYSQL')
    {
        $dbh = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_DB, MYSQL_USER, MYSQL_PASS);
        $dbh->exec("SET names utf8");
    }
    else if (SQLTYPE === 'SQLITE')
    {
        $dbh = new PDO('sqlite:' . SQLITE_DB_LOCATION . SQLITE_DB_NAME);
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

function nel_get_db_handle()
{
    static $database_handle;

    if(!isset($database_handle))
    {
        $database_handle = nel_init_db_connection();
    }

    return $database_handle;
}

?>