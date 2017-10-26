<?php
$dbh = nel_get_database_handle();

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
    $dbh = nel_get_database_handle();
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

function nel_get_database_handle()
{
    static $database_handle;

    if (!isset($database_handle))
    {
        $database_handle = nel_init_db_connection();
    }

    return $database_handle;
}

function nel_pdo_simple_query($query)
{
    $dbh = nel_get_database_handle();
    $result = $dbh->query($query);
    return $result;
}

function nel_pdo_one_parameter_query($query, $parameter, $type = PDO::PARAM_STR, $close_cursor = true)
{
    $prepared = nel_pdo_prepare($query);
    $prepared->bindValue(1, $parameter, $type);
    $prepared = nel_pdo_execute($prepared, $close_cursor);
    return $prepared;
}

function nel_pdo_prepare($query)
{
    $dbh = nel_get_database_handle();
    $prepared = $dbh->prepare($query);
    return $prepared;
}

function nel_pdo_execute($prepared, $closecursor = true)
{
    $prepared->execute();

    if ($closecursor)
    {
        $prepared->closeCursor();
    }

    return $prepared;
}

function nel_pdo_execute_fetch($prepared, $fetch_style = PDO::ATTR_DEFAULT_FETCH_MODE, $closecursor = false)
{
    $prepared = nel_pdo_execute($prepared, false);
    return nel_pdo_doFetch($prepared, $fetch_style, $closecursor);
}

function nel_pdo_execute_fetchall($prepared, $fetch_style = PDO::ATTR_DEFAULT_FETCH_MODE)
{
    $prepared = nel_pdo_execute($prepared, false);
    return nel_pdo_doFetch($prepared, $fetch_style, $closecursor);
}

function nel_pdo_doFetch($prepared, $fetch_style = PDO::ATTR_DEFAULT_FETCH_MODE, $closecursor = false)
{
    if ($fetch_style === PDO::FETCH_COLUMN)
    {
        $fetched_result = $prepared->fetchColumn();
    }
    else
    {
        $fetched_result = $prepared->fetch($fetch_style);
    }

    if ($closecursor)
    {
        $prepared->closeCursor();
    }

    return $fetched_result;
}

function nel_pdo_fetchall($prepared, $fetch_style = PDO::ATTR_DEFAULT_FETCH_MODE)
{
    $fetched_result = $prepared->fetchAll($fetch_style);
    return $fetched_result;
}

function nel_pdo_create_parameter_ids($column_names)
{
    $identifiers = array();

    foreach ($column_names as $name)
    {
        array_push($identifiers, ':' . $name);
    }

    return $identifiers;
}

function nel_sql_format_columns($columns)
{
    $column_count = count($columns);
    $columns_sql = '(';

    for ($i = 0; $i < $column_count; $i ++)
    {
        $columns_sql .= '"' . $columns[$i] . '"';

        if ($i < $column_count - 1)
        {
            $columns_sql .= ', ';
        }
        else
        {
            $columns_sql .= ')';
        }
    }

    return $columns_sql;
}

function nel_sql_format_values($values)
{
    $values_count = count($values);
    $values_sql = '(';

    for ($i = 0; $i < $values_count; $i ++)
    {
        $values_sql .= $values[$i];

        if ($i < $values_count - 1)
        {
            $values_sql .= ', ';
        }
        else
        {
            $values_sql .= ')';
        }
    }

    return $values_sql;
}

function nel_pdo_generate_insert_query($table, $columns, $values)
{
    $query = 'INSERT INTO "' . $table . '" ' . nel_sql_format_columns($columns) . ' VALUES ' .
         nel_sql_format_values($values);
    return $query;
}

// Bye
function nel_pdo_one_parameter_query($query, $parameter, $type = null, $close_cursor = false)
{
    $bind_values = array();
    nel_pdo_bind_set($bind_values, 1, $parameter, $type);
    return nel_pdo_prepared_query($query, $bind_values);
}

//Bye
function nel_pdo_prepared_query($query, $bind_values, $close_cursor = false)
{
    $dbh = nel_get_database_handle();
    $prepared = $dbh->prepare($query);
    $prepared = nel_pdo_bind_values($prepared, $bind_values);
    $prepared->execute();

    if ($close_cursor)
    {
        $prepared->closeCursor();
    }

    return $prepared;
}

// Bye
function nel_pdo_do_fetchall($prepared, $fetch_style = PDO::ATTR_DEFAULT_FETCH_MODE)
{
    $fetched_result = $prepared->fetchAll($fetch_style);
    return $fetched_result;
}

// Bye
function nel_pdo_doFetch($result, $fetch_style = PDO::ATTR_DEFAULT_FETCH_MODE, $close_cursor = false)
{
    if ($fetch_style === PDO::FETCH_COLUMN)
    {
        $fetched_result = $result->fetchColumn();
    }
    else
    {
        $fetched_result = $result->fetch($fetch_style);
    }

    return $fetched_result;
}

// Bye
function nel_pdo_bind_set(&$bind_values, $key, $value, $type = null)
{
    $bind_values[$key]['value'] = $value;

    if (!is_null($type))
    {
        $bind_values[$key]['type'] = $type;
    }
}

// Bye
function nel_pdo_bind_values($prepared, $bind_values)
{
    foreach ($bind_values as $parameter => $values)
    {
        if (array_key_exists('bind_type', $values))
        {
            $prepared->bindValue($parameter, $values['value'], $values['bind_type']);
        }
        else
        {
            $prepared->bindValue($parameter, $values['value']);
        }
    }

    return $prepared;
}

function nel_database_exists($database)
{
    $dbh = nel_get_database_handle();

    if (SQLTYPE === 'SQLITE')
    {
        $result = $dbh->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name='" . $database . "'");
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
        return true;
    }

    return false;
}

function nel_table_exists($table)
{
    $dbh = nel_get_database_handle();

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
        return true;
    }

    return false;
}

function nel_table_fail($table)
{
    die('Creation of ' . $table . ' failed! Check database settings and config.php then retry installation.');
}
