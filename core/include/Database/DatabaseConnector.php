<?php
declare(strict_types = 1);

namespace Nelliel\Database;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;
use PDOException;

class DatabaseConnector
{

    function __construct()
    {}

    public function getConnection(string $database_key): NellielPDO
    {
        $config = $this->getConfigValues($database_key);

        switch ($config['sqltype']) {
            case 'MYSQL':
                $connection = $this->mysql($config);
                break;

            case 'MARIADB':
                $connection = $this->mariadb($config);
                break;

            case 'POSTGRESQL':
                $connection = $this->postgresql($config);
                break;

            case 'SQLITE':
                $connection = $this->sqlite($config);
                break;

            default:
                nel_derp(2, _gettext('Invalid database type given in config.'));
        }

        $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $connection->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $connection->setAttribute(PDO::ATTR_TIMEOUT, $config['timeout']);
        $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $connection;
    }

    protected function getConfigValues(string $database_key): array
    {
        $db_config = NEL_DATABASES[$database_key] ?? array();
        $config['sqltype'] = $db_config['sqltype'] ?? '';
        $config['timeout'] = $db_config['timeout'] ?? 30;
        $type = utf8_strtolower($config['sqltype']);
        $config['database'] = $db_config[$type]['database'] ?? '';
        $config['schema'] = $db_config[$type]['schema'] ?? '';
        $config['host'] = $db_config[$type]['host'] ?? '';
        $config['port'] = $db_config[$type]['port'] ?? '';
        $config['user'] = $db_config[$type]['user'] ?? '';
        $config['password'] = $db_config[$type]['password'] ?? '';
        $config['encoding'] = $db_config[$type]['encoding'] ?? '';
        $config['file_name'] = $db_config[$type]['file_name'] ?? '';
        $config['path'] = $db_config[$type]['path'] ?? '';
        return $config;
    }

    protected function newConnection(string $dsn, $config, ?string $username = null, ?string $password = null,
        ?array $options = null): NellielPDO
    {
        // Just in case things go wrong we want to avoid sensitive info leaking
        try {
            $connection = new NellielPDO($config, $dsn, $username, $password, $options);
            return $connection;
        } catch (PDOException $exception) {
            nel_derp(1, _gettext('Error connecting to database. Check config values and verify database setup.'));
        }
    }

    protected function mysql(array $config): NellielPDO
    {
        $options = array();
        $dsn = 'mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['database'] .
            ';charset=' . $config['encoding'] . ';';
        $connection = $this->newConnection($dsn, $config, $config['user'], $config['password'], $options);
        $connection->exec("SET SESSION sql_mode='ANSI';");
        return $connection;
    }

    protected function mariadb(array $config): NellielPDO
    {
        $options = array();
        $dsn = 'mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['database'] .
            ';charset=' . $config['encoding'] . ';';
        $connection = $this->newConnection($dsn, $config, $config['user'], $config['password'], $options);
        $connection->exec("SET SESSION sql_mode='ANSI';");
        return $connection;
    }

    protected function postgresql(array $config): NellielPDO
    {
        $options = array();
        $dsn = 'pgsql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['database'] . ';';
        $connection = $this->newConnection($dsn, $config, $config['user'], $config['password'], $options);
        $connection->exec("SET search_path TO " . $config['schema'] . "; SET names '" . $config['encoding'] . "';");
        return $connection;
    }

    protected function sqlite(array $config): NellielPDO
    {
        $options = array();

        if ($config['path'] === '') {
            $path = NEL_CORE_PATH;
        } else {
            $path = $config['path'];
        }

        $dsn = 'sqlite:' . $path . $config['file_name'];
        $connection = $this->newConnection($dsn, $config, null, null, $options);
        $connection->exec('PRAGMA encoding = "' . $config['encoding'] . '"; PRAGMA foreign_keys = ON;');
        return $connection;
    }
}
