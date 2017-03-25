<?php

//
// This holds the configuration options for database access.
// Nelliel is set to use SQLite by default. However you can utilize a number of other relational databases as well.
// You only need to configure one database option for use. The others can be left as-is.
//


// Prefix used for table names. Make sure this is unique for each board!
define('TABLEPREFIX', 'nelliel'); // Prefix used for tables in the database.


// Database type. Supported types: MYSQL, SQLITE, POSTGRES
define('SQLTYPE', 'SQLITE'); // Database type


//
// Configuration for SQLite
//


define('SQLITE_DB_NAME', 'nelliel.sqlite'); // Filename of SQLite database
define('SQLITE_DB_PATH', ''); // Alternative path where the database is to be located. Defaults to board_files if left blank
define('SQLITE_ENCODING', 'UTF-8'); // The character encoding to use. It is rare that this would need to be changed


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


//
// Leave everything below this point alone unless you have a good reason to mess with it.
//


define('POST_TABLE', TABLEPREFIX . '_posts'); // Table used for post data
define('THREAD_TABLE', TABLEPREFIX . '_threads'); // Table used for thread data
define('FILE_TABLE', TABLEPREFIX . '_files'); // Table used for file data
define('EXTERNAL_TABLE', TABLEPREFIX . '_external'); // Table used for external content
define('ARCHIVE_POST_TABLE', TABLEPREFIX . '_archive_posts'); // Stores archived threads
define('ARCHIVE_THREAD_TABLE', TABLEPREFIX . '_archive_threads'); // Stores archived thread data
define('ARCHIVE_FILE_TABLE', TABLEPREFIX . '_archive_files'); // Stores archived file data
define('ARCHIVE_EXTERNAL_TABLE', TABLEPREFIX . '_archive_external'); // Stores archived external content
define('CONFIG_TABLE', TABLEPREFIX . '_config'); // Table to store board configuration. Best to leave it as-is unless you really need to change it
define('BAN_TABLE', TABLEPREFIX . '_bans'); // Table containing ban info