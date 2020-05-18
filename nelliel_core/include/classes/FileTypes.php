<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class FileTypes
{
    private $cache_handler;
    private $database;
    private static $data;
    private static $settings;
    private static $types;

    function __construct(NellielPDO $database)
    {
        $this->cache_handler = new \Nelliel\Utility\CacheHandler();
        $this->database = $database;
    }

    private function loadDataFromDatabase(bool $ignore_cache = false)
    {
        $filetypes = array();
        $sub_extensions = array();
        $types = array();
        $db_results = $this->database->executeFetchAll('SELECT * FROM "nelliel_filetypes" ORDER BY "entry" ASC', PDO::FETCH_ASSOC);

        foreach ($db_results as $result)
        {
            if($result['type_def'] == 1)
            {
                $types[$result['type']] = $result;
                continue;
            }

            if ($result['extension'] == $result['parent_extension'])
            {
                $filetypes[$result['extension']] = $result;
            }
            else
            {
                $sub_extensions[] = $result;
            }
        }

        self::$types = $types;

        foreach ($sub_extensions as $sub_extension)
        {
            if (array_key_exists($sub_extension['parent_extension'], $filetypes))
            {
                $filetypes[$sub_extension['extension']] = $filetypes[$sub_extension['parent_extension']];
                $filetypes[$sub_extension['extension']]['extension'] = $sub_extension['extension'];
            }
        }

        self::$data = $filetypes;
    }

    private function loadSettingsFromDatabase(string $domain_id, bool $ignore_cache = false)
    {
        $settings = array();

        if(!$ignore_cache)
        {
            $settings = $this->cache_handler->loadArrayFromCache($domain_id . '/filetype_settings.php', 'settings');
        }

        if (empty($settings))
        {
            $prepared = $this->database->prepare('SELECT "db_prefix" FROM "nelliel_board_data" WHERE "board_id" = ?');
            $db_prefix = $this->database->executePreparedFetch($prepared, [$domain_id], PDO::FETCH_COLUMN);
            $config_table = $db_prefix . '_config';
            $filetypes_json = $this->database->executeFetch(
                    'SELECT "setting" FROM "' . $config_table . '" WHERE "config_name" = \'enabled_filetypes\'', PDO::FETCH_COLUMN);
            $settings = json_decode($filetypes_json, true);
        }

        self::$settings[$domain_id] = $settings;
    }

    public function allTypeData()
    {
        $this->loadDataIfNot();
        return self::$data;
    }

    public function extensionData(string $extension)
    {
        return $this->isValidExtension($extension) ? self::$data[$extension] : array();
    }

    public function types()
    {
        $this->loadDataIfNot();
        return self::$types;
    }

    public function isValidExtension(string $extension)
    {
        $this->loadDataIfNot();
        return isset(self::$data[$extension]);
    }

    public function settings(string $domain_id, string $setting = null, bool $cache_regen = false)
    {
        if ($domain_id === '' || is_null($domain_id))
        {
            return array();
        }

        if (empty(self::$settings[$domain_id]) || $cache_regen)
        {
            $this->loadSettingsFromDatabase($domain_id);
        }

        if (is_null($setting))
        {
            return self::$settings[$domain_id];
        }

        return self::$settings[$domain_id][$setting];
    }

    private function loadDataIfNot(bool $ignore_cache = false)
    {
        if(empty(self::$data))
        {
            $this->loadDataFromDatabase($ignore_cache);
        }
    }

    private function loadSettingsIfNot(string $domain_id, bool $ignore_cache = false)
    {
        if(!isset(self::$settings[$domain_id]))
        {
            $this->loadSettingsFromDatabase($domain_id, $ignore_cache);
        }
    }

    public function extensionIsEnabled(string $domain_id, string $extension)
    {
        $extension_data = $this->extensionData($extension);

        if (empty($extension_data))
        {
            return false;
        }

        $type = $extension_data['type'];
        $format = $extension_data['format'];
        return $this->typeIsEnabled($domain_id, $type) && $this->formatIsEnabled($domain_id, $type, $format);
    }

    public function typeIsEnabled(string $domain_id, string $type)
    {
        $this->loadSettingsIfNot($domain_id);
        return in_array($type, $this->enabledTypes($domain_id));
    }

    public function formatIsEnabled(string $domain_id, string $type, string $format)
    {
        $this->loadSettingsIfNot($domain_id);
        return in_array($format, $this->enabledFormats($domain_id, $type));
    }

    public function verifyFile(string $extension, $file_path, $start_buffer = 65535, $end_buffer = 65535)
    {
        $file_length = filesize($file_path);
        $end_offset = ($file_length < 65535) ? $file_length : $file_length - 65535;
        $file_test_begin = file_get_contents($file_path, null, null, 0, 65535);
        $file_test_end = file_get_contents($file_path, null, null, $end_offset);
        $extension_data = $this->extensionData($extension);
        return preg_match('#' . $extension_data['id_regex'] . '#', $file_test_begin) ||
                preg_match('#' . $extension_data['id_regex'] . '#', $file_test_end);
    }

    public function generateSettingsCache(string $domain_id)
    {
        if (NEL_USE_INTERNAL_CACHE)
        {
            $this->loadSettingsIfNot($domain_id, true);
            $this->cache_handler->writeCacheFile(NEL_CACHE_FILES_PATH . $domain_id . '/', 'filetype_settings.php',
                    '$settings = ' . var_export(self::$settings[$domain_id], true) . ';');
        }
    }

    public function enabledTypes(string $domain_id)
    {
        $this->loadSettingsIfNot($domain_id);
        $enabled = array();

        foreach(self::$settings[$domain_id] as $type => $settings)
        {
            if(isset($settings['enabled']) && $settings['enabled'])
            {
                $enabled[] = $type;
            }
        }

        return $enabled;
    }

    public function enabledFormats(string $domain_id, string $type)
    {
        $this->loadSettingsIfNot($domain_id);
        $enabled = array();

        if(!isset(self::$settings[$domain_id][$type]))
        {
            return $enabled;
        }

        foreach(self::$settings[$domain_id][$type]['formats'] as $format => $settings)
        {
            if(isset($settings['enabled']) && $settings['enabled'])
            {
                $enabled[] = $format;
            }
        }

        return $enabled;
    }
}