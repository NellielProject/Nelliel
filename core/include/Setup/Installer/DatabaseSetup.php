<?php
declare(strict_types = 1);

namespace Nelliel\Setup\Installer;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Language\Translator;
use Nelliel\Utility\FileHandler;
use PDO;

class DatabaseSetup
{
    protected $database;
    protected $sql_compatibility;
    protected $file_handler;
    protected $translator;

    function __construct(FileHandler $file_handler, Translator $translator)
    {
        $this->file_handler = $file_handler;
        $this->translator = $translator;
    }

    public function setup(string $step)
    {
        if ($step === 'environment-check') {
            if (file_exists(NEL_CONFIG_FILES_PATH . 'databases.php')) {
                $this->displayForm('database_found.html');
            }

            $this->displayForm('database_type.html');
        }

        $database_type = $_POST['database_type'] ?? '';

        if ($step === 'database-select') {
            if (!isset($_POST['keep_db_config'])) {
                if ($database_type === '') {
                    $this->displayForm('database_type.html');
                } else {

                    $this->databaseTypeCheck($database_type);

                    if ($database_type === 'MYSQL') {
                        $this->displayForm('mysql_config.html');
                    }

                    if ($database_type === 'MARIADB') {
                        $this->displayForm('mariadb_config.html');
                    }

                    if ($database_type === 'POSTGRESQL') {
                        $this->displayForm('postgresql_config.html');
                    }

                    if ($database_type === 'SQLITE') {
                        $this->displayForm('sqlite_config.html');
                    }
                }
            }
        }

        if ($step === 'database-config') {
            $this->databaseTypeCheck($database_type);
            $database_config = array();
            $database_config['core']['sqltype'] = $database_type;

            if ($database_type === 'MYSQL') {
                $database_config['core']['mysql'] = $this->mysqlConfig();
            }

            if ($database_type === 'MARIADB') {
                $database_config['core']['mariadb'] = $this->mariadbConfig();
            }

            if ($database_type === 'POSTGRESQL') {
                $database_config['core']['postgresql'] = $this->postgresqlConfig();
            }

            if ($database_type === 'SQLITE') {
                $database_config['core']['sqlite'] = $this->sqliteConfig();
            }

            $this->writeDatabaseConfig($database_config, true);
        }

        $database_type = $_POST['database_type'] ?? '';
        $db_config = array();
        require_once NEL_CONFIG_FILES_PATH . 'databases.php';
        define('NEL_DATABASES', $db_config);
        $this->database = nel_database('core');
        $this->checkDBEngine($database_type);
    }

    private function databaseTypeCheck(string $type): void
    {
        $valid_types = ['MYSQL', 'MARIADB', 'POSTGRESQL', 'SQLITE'];

        if (!in_array($type, $valid_types)) {
            nel_derp(112, __('Unrecognized database type.'));
        }
    }

    private function mysqlConfig(): array
    {
        $config = array();
        $config['database'] = $_POST['database'] ?? '';
        $config['timeout'] = intval($_POST['timeout'] ?? 30);
        $config['host'] = $_POST['host'] ?? 'localhost';
        $config['port'] = intval($_POST['port'] ?? 3306);
        $config['user'] = $_POST['user'] ?? '';
        $config['password'] = $_POST['password'] ?? '';
        $config['encoding'] = $_POST['encoding'] ?? 'utf8mb4';
        return $config;
    }

    private function mariadbConfig(): array
    {
        $config = array();
        $config['database'] = $_POST['database'] ?? '';
        $config['timeout'] = intval($_POST['timeout'] ?? 30);
        $config['host'] = $_POST['host'] ?? 'localhost';
        $config['port'] = intval($_POST['port'] ?? 3306);
        $config['user'] = $_POST['user'] ?? '';
        $config['password'] = $_POST['password'] ?? '';
        $config['encoding'] = $_POST['encoding'] ?? 'utf8mb4';
        return $config;
    }

    private function postgresqlConfig(): array
    {
        $config = array();
        $config['database'] = $_POST['database'] ?? '';
        $config['timeout'] = intval($_POST['timeout'] ?? 30);
        $config['host'] = $_POST['host'] ?? 'localhost';
        $config['port'] = intval($_POST['port'] ?? 5432);
        $config['user'] = $_POST['user'] ?? '';
        $config['password'] = $_POST['password'] ?? '';
        $config['schema'] = $_POST['schema'] ?? 'public';
        $config['encoding'] = $_POST['encoding'] ?? 'UTF-8';
        return $config;
    }

    private function sqliteConfig(): array
    {
        $config = array();
        $config['file_name'] = $_POST['file_name'] ?? 'nelliel';
        $config['timeout'] = intval($_POST['timeout'] ?? 30);
        $config['path'] = $_POST['path'] ?? '';
        $config['encoding'] = $_POST['encoding'] ?? 'UTF-8';
        return $config;
    }

    private function writeDatabaseConfig(array $config, bool $overwrite = false): bool
    {
        if (!$overwrite && file_exists(NEL_CONFIG_FILES_PATH . 'databases.php')) {
            return false;
        }

        $prepend = "\n" . '// Database config generated by Nelliel installer';
        $append = "\n\n" . '// Additional databases can be added below' . "\n";
        $this->file_handler->writeInternalFile(NEL_CONFIG_FILES_PATH . 'databases.php',
            $prepend . "\n" . nel_config_var_export($config, '$db_config') . $append, true);
        return true;
    }

    private function displayForm(string $filename)
    {
        $html = file_get_contents(__DIR__ . '/forms/' . $filename);
        echo $this->translator->translateHTML($html);
        die();
    }

    private function checkDBEngine(string $type): void
    {
        if (($type === 'MYSQL' || $type === 'MARIADB') && !$this->checkForInnoDB()) {
            nel_derp(102, __('InnoDB engine is required for MySQL or MariaDB support but that engine is not available.'));
        }
    }

    private function checkForInnoDB()
    {
        $result = $this->database->query("SHOW ENGINES");
        $list = $result->fetchAll(PDO::FETCH_ASSOC);

        foreach ($list as $entry) {
            if ($entry['Engine'] === 'InnoDB' && ($entry['Support'] === 'DEFAULT' || $entry['Support'] === 'YES')) {
                return true;
            }
        }

        return false;
    }
}