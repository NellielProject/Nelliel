<?php

//
// This holds the configuration options for database access.
// Nelliel is set to use MYSQL by default. However you can utilize a number of other relational databases as well.
// You only need to configure one database option for use. The others can be left as-is.
//

// Database type. Supported types: MYSQL, SQLITE, POSTGRES
define('SQLTYPE', 'MYSQL'); // Database type

//
// Configuration for MySQL
//

define('MYSQL_DB', 'database'); // Name of database used by imageboard
define('MYSQL_HOST', 'localhost'); // MySQL Server address
define('MYSQL_PORT', '3306'); // Server port. MySQL default is 3306
define('MYSQL_USER', 'username'); // User that will access the database
define('MYSQL_PASS', 'password'); // Password of user
define('MYSQL_ENCODING', 'utf8'); // The character encoding to use. Usually fine as-is

//
// Configuration for SQLite
//

// WARNING: The database path must be a location outside of web-accessible directories!
// Otherwise someone could just download the whole thing.
// If this is not possible on your host, do not use SQLite for your database!

define('SQLITE_DB_NAME', 'nelliel.sqlite'); // Filename of SQLite database
define('SQLITE_DB_PATH', ''); // Path where the database is to be located
define('SQLITE_ENCODING', 'UTF-8'); // The character encoding to use. Usually fine as-is

//
// Configuration for PostgreSQL
//

define('POSTGRES_DB', 'database'); // Name of database used by imageboard
define('POSTGRES_HOST', 'localhost'); // PostgreSQL Server address
define('POSTGRES_PORT', '5432'); // Server port. PostgreSQL default is 5432
define('POSTGRES_USER', 'username'); // User that will access the database
define('POSTGRES_PASS', 'password'); // Password of user
define('POSTGRES_SCHEMA', 'public'); // Which schema to use; default is 'public'
define('POSTGRES_ENCODING', 'UTF8'); // The character encoding to use. See notes below!
// You must make sure 'POSTGRES_ENCODING' matches or is compatible with what the database uses!
