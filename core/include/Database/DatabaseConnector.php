<?php
declare(strict_types = 1);

namespace Nelliel\Database;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\DatabaseConfig;
use PDO;
use PDOException;

class DatabaseConnector
{
    private $database_config;

    function __construct(DatabaseConfig $database_config)
    {
        $this->database_config = $database_config;
    }

    public function getConnection(string $database_key): NellielPDO
    {
        $config = $this->database_config->getConfig($database_key);
        $type = utf8_strtolower($config['sqltype']);

        switch ($config['sqltype']) {
            case 'MYSQL':
                $connection = $this->mysql($config[$type]);
                break;

            case 'MARIADB':
                $connection = $this->mariadb($config[$type]);
                break;

            case 'POSTGRESQL':
                $connection = $this->postgresql($config[$type]);
                break;

            case 'SQLITE':
                $connection = $this->sqlite($config[$type]);
                break;

            default:
                nel_derp(2, __('Invalid database type given in config.'));
        }

        $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $connection->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $connection->setAttribute(PDO::ATTR_TIMEOUT, $config[$type]['timeout']);
        $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $connection;
    }

    private function newConnection(string $dsn, array $config, ?string $username = null, ?string $password = null,
        ?array $options = null): NellielPDO
    {
        // Just in case things go wrong we want to avoid sensitive info leaking
        try {
            $connection = new NellielPDO($config, $dsn, $username, $password, $options);
            return $connection;
        } catch (PDOException $exception) {
            nel_derp(1,
                sprintf(
                    __(
                        'Received SQL Error %d while connecting to database. Verify the database is functioning and the configuration is correct.'),
                    $exception->getCode()));
        }

        die();
    }

    private function mysql(array $config): NellielPDO
    {
        $options = array();
        $dsn = 'mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['database'] .
            ';charset=' . $config['encoding'] . ';';
        $connection = $this->newConnection($dsn, $config, $config['user'], $config['password'], $options);
        $connection->exec("SET SESSION sql_mode='ANSI';");
        return $connection;
    }

    private function mariadb(array $config): NellielPDO
    {
        $options = array();
        $dsn = 'mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['database'] .
            ';charset=' . $config['encoding'] . ';';
        $connection = $this->newConnection($dsn, $config, $config['user'], $config['password'], $options);
        $connection->exec("SET SESSION sql_mode='ANSI';");
        return $connection;
    }

    private function postgresql(array $config): NellielPDO
    {
        $options = array();
        $dsn = 'pgsql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['database'] . ';';
        $connection = $this->newConnection($dsn, $config, $config['user'], $config['password'], $options);
        $connection->exec("SET search_path TO " . $config['schema'] . "; SET names '" . $config['encoding'] . "';");
        return $connection;
    }

    private function sqlite(array $config): NellielPDO
    {
        $options = array();
        $dsn = 'sqlite:' . $config['path'] . $config['file_name'];
        $connection = $this->newConnection($dsn, $config, null, null, $options);
        $connection->exec('PRAGMA encoding = "' . $config['encoding'] . '"; PRAGMA foreign_keys = ON;');
        return $connection;
    }
}
