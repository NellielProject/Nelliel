<?php
declare(strict_types = 1);

namespace Nelliel\Database;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;
use PDOException;

class DatabaseConnector
{
    protected $database_key;
    protected $config = array();
    protected $options = array();
    protected $connection;

    function __construct(string $database_key)
    {
        $this->database_key = $database_key;
        $this->setValues();

        switch ($this->config['sqltype']) {
            case 'MYSQL':
                $this->connection = $this->mysql();
                break;

            case 'MARIADB':
                $this->connection = $this->mariadb();
                break;

            case 'POSTGRESQL':
                $this->connection = $this->postgresql();
                break;

            case 'SQLITE':
                $this->connection = $this->sqlite();
                break;

            default:
                nel_derp(2, _gettext('Invalid database type given in config.'));
        }

        $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->connection->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);
    }

    public function connection(): NellielPDO
    {
        return $this->connection;
    }

    protected function setValues(): void
    {
        $db_config = NEL_DATABASES[$this->database_key] ?? array();
        $this->options[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_ASSOC;
        $this->options[PDO::ATTR_EMULATE_PREPARES] = false;
        $this->options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        $this->options[PDO::ATTR_TIMEOUT] = $db_config['timeout'];

        $this->config['sqltype'] = $db_config['sqltype'] ?? '';
        $type = utf8_strtolower($this->config['sqltype']);
        $this->config['database'] = $db_config[$type]['database'] ?? '';
        $this->config['schema'] = $db_config[$type]['schema'] ?? '';
        $this->config['host'] = $db_config[$type]['host'] ?? '';
        $this->config['port'] = $db_config[$type]['port'] ?? '';
        $this->config['user'] = $db_config[$type]['user'] ?? '';
        $this->config['password'] = $db_config[$type]['password'] ?? '';
        $this->config['encoding'] = $db_config[$type]['encoding'] ?? '';
        $this->config['file_name'] = $db_config[$type]['file_name'] ?? '';
        $this->config['path'] = $db_config[$type]['path'] ?? '';
    }

    protected function newConnection(string $dsn, ?string $username = null, ?string $password = null,
        ?array $options = null): NellielPDO
    {
        // Just in case things go wrong we want to avoid sensitive info leaking
        try {
            $connection = new NellielPDO($this->config, $dsn, $username, $password, $options);
            return $connection;
        } catch (PDOException $exception) {
            nel_derp(1, _gettext('Error connecting to database.'));
        }
    }

    protected function mysql(): NellielPDO
    {
        $dsn = 'mysql:host=' . $this->config['host'] . ';port=' . $this->config['port'] . ';dbname=' .
            $this->config['database'] . ';charset=' . $this->config['encoding'] . ';';
        $connection = $this->newConnection($dsn, $this->config['user'], $this->config['password'], $this->options);
        $connection->exec("SET SESSION sql_mode='ANSI';");
        return $connection;
    }

    protected function mariadb(): NellielPDO
    {
        $dsn = 'mysql:host=' . $this->config['host'] . ';port=' . $this->config['port'] . ';dbname=' .
            $this->config['database'] . ';charset=' . $this->config['encoding'] . ';';
        $connection = $this->newConnection($dsn, $this->config['user'], $this->config['password'], $this->options);
        $connection->exec("SET SESSION sql_mode='ANSI';");
        return $connection;
    }

    protected function postgresql(): NellielPDO
    {
        $dsn = 'pgsql:host=' . $this->config['host'] . ';port=' . $this->config['port'] . ';dbname=' .
            $this->config['database'] . ';';
        $connection = $this->newConnection($dsn, $this->config['user'], $this->config['password'], $this->options);
        $connection->exec(
            "SET search_path TO " . $this->config['schema'] . "; SET names '" . $this->config['encoding'] . "';");
        return $connection;
    }

    protected function sqlite(): NellielPDO
    {
        if ($this->config['path'] === '') {
            $path = NEL_CORE_PATH;
        } else {
            $path = $this->config['path'];
        }

        $dsn = 'sqlite:' . $path . $this->config['file_name'];
        $connection = $this->newConnection($dsn);
        $connection->exec('PRAGMA encoding = "' . $this->config['encoding'] . '"; PRAGMA foreign_keys = ON;');
        return $connection;
    }
}
