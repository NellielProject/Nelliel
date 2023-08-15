<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class DatabaseConfig
{
    private static $database_config;
    private $current_config = array();

    function __construct(array $temp_config = array())
    {
        if (!empty($temp_config)) {
            $this->current_config = $this->process($temp_config);
        } else {
            if (empty(self::$database_config)) {
                $db_config = array();
                include NEL_CONFIG_FILES_PATH . 'databases.php';
                self::$database_config = $this->process($db_config);
            }

            $this->current_config = self::$database_config;
        }
    }

    private function process(array $raw_config): array
    {
        $processed_config = array();

        foreach ($raw_config as $key => $config) {
            $processed_config[$key]['sqltype'] = strval($config['sqltype'] ?? '');

            foreach ($config as $type => $type_config) {
                if ($type === 'sqltype') {
                    continue;
                }

                $processed_config[$key][$type]['sqltype'] = strval($config['sqltype'] ?? '');
                $processed_config[$key][$type]['database'] = strval($type_config['database'] ?? '');
                $processed_config[$key][$type]['timeout'] = intval($type_config['timeout'] ?? 30);
                $processed_config[$key][$type]['schema'] = strval($type_config['schema'] ?? '');
                $processed_config[$key][$type]['host'] = strval($type_config['host'] ?? '');
                $processed_config[$key][$type]['port'] = intval($type_config['port'] ?? 0);
                $processed_config[$key][$type]['user'] = strval($type_config['user'] ?? '');
                $processed_config[$key][$type]['password'] = strval($type_config['password'] ?? '');
                $processed_config[$key][$type]['encoding'] = strval($type_config['encoding'] ?? '');
                $processed_config[$key][$type]['file_name'] = strval($type_config['file_name'] ?? '');

                if (!nel_true_empty($type_config['path'] ?? '')) {
                    $processed_config[$key][$type]['path'] = strval($type_config['path']);
                } else {
                    $processed_config[$key][$type]['path'] = strval(NEL_CORE_PATH);
                }
            }
        }

        return $processed_config;
    }

    public function getConfig(string $key): array
    {
        return $this->current_config[$key] ?? array();
    }
}