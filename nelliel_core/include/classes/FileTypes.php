<?php

declare(strict_types=1);

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
    private static $extensions;
    private static $settings;
    private static $types;
    private static $extensions_map;

    function __construct(NellielPDO $database)
    {
        $this->cache_handler = nel_utilities()->cacheHandler();
        $this->database = $database;
    }

    private function loadDataFromDatabase(bool $ignore_cache = false)
    {
        $extensions = array();
        $types = array();

        if (!$ignore_cache)
        {
            $filetype_data = $this->cache_handler->loadArrayFromFile('filetype_data', 'filetype_data.php');
        }

        if(empty($filetype_data))
        {
            $filetype_data = $this->database->executeFetchAll('SELECT * FROM "nelliel_filetypes" ORDER BY "entry" ASC',
                    PDO::FETCH_ASSOC);
            $this->cache_handler->writeArrayToFile('filetype_data', $filetype_data, 'filetype_data.php');
        }

        foreach ($filetype_data as $data)
        {
            if ($data['type_def'] == 1)
            {
                $types[$data['type']] = $data;
                continue;
            }
            else
            {
                $base_extension = $data['base_extension'];
                $extensions_map[$base_extension] = $base_extension;
                $extensions[$base_extension] = $data;
                $sub_extensions = json_decode($data['sub_extensions'], true);

                if (empty($sub_extensions))
                {
                    continue;
                }

                $filetypes = array();

                foreach ($sub_extensions as $sub_extension)
                {
                    $filetypes['extensions'][$sub_extension] = $base_extension;
                    $extensions_map[$sub_extension] = $base_extension;
                }
            }
        }

        self::$types = $types;
        self::$extensions = $extensions;
        self::$extensions_map = $extensions_map;
    }

    private function loadSettingsFromDatabase(string $domain_id, bool $ignore_cache = false)
    {
        $settings = array();

        if (!$ignore_cache)
        {
            $settings = $this->cache_handler->loadArrayFromFile('settings', 'filetype_settings.php', $domain_id);
        }

        if (empty($settings))
        {
            $prepared = $this->database->prepare('SELECT "db_prefix" FROM "nelliel_board_data" WHERE "board_id" = ?');
            $db_prefix = $this->database->executePreparedFetch($prepared, [$domain_id], PDO::FETCH_COLUMN);
            $config_table = $db_prefix . '_config';
            $filetypes_json = $this->database->executeFetch(
                    'SELECT "setting_value" FROM "' . $config_table . '" WHERE "setting_name" = \'enabled_filetypes\'',
                    PDO::FETCH_COLUMN);
            $settings = json_decode($filetypes_json, true);
        }

        self::$settings[$domain_id] = $settings;
    }

    public function getBaseExtension(string $extension)
    {
        return self::$extensions_map[$extension];
    }

    public function allTypeData()
    {
        $this->loadDataIfNot(false);
        return self::$extensions;
    }

    public function extensionData(string $extension)
    {
        $extension_data = array();

        if (!$this->isValidExtension($extension))
        {
            return $extension_data;
        }

        $base_extension = $this->getBaseExtension($extension);
        $extension_data = self::$extensions[$base_extension];
        return $extension_data;
    }

    public function types()
    {
        $this->loadDataIfNot(false);
        return self::$types;
    }

    public function isValidExtension(string $extension)
    {
        $this->loadDataIfNot(false);
        $base_extension = $this->getBaseExtension($extension);
        return isset(self::$extensions[$base_extension]);
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

    private function loadDataIfNot(bool $ignore_cache)
    {
        if (empty(self::$extensions))
        {
            $this->loadDataFromDatabase($ignore_cache);
        }
    }

    private function loadSettingsIfNot(string $domain_id, bool $ignore_cache = false)
    {
        if (!isset(self::$settings[$domain_id]))
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
        $this->loadDataIfNot(false);
        $this->loadSettingsIfNot($domain_id);
        $available_formats = $this->availableFormats();
        return in_array($format, $this->enabledFormats($domain_id, $type)) && isset($available_formats[$format]) &&
                $available_formats[$format];
    }

    public function verifyFile(string $extension, $file_path, $start_buffer = 65535, $end_buffer = 65535)
    {
        $file_length = filesize($file_path);
        $end_offset = ($file_length < 65535) ? $file_length : $file_length - 65535;
        $file_test_begin = file_get_contents($file_path, false, null, 0, 65535);
        $file_test_end = file_get_contents($file_path, false, null, $end_offset);
        $extension_data = $this->extensionData($extension);
        return preg_match('/' . $extension_data['id_regex'] . '/s', $file_test_begin) ||
                preg_match('/' . $extension_data['id_regex'] . '/s', $file_test_end);
    }

    public function generateSettingsCache(string $domain_id)
    {
        if (NEL_USE_FILE_CACHE)
        {
            $this->loadSettingsIfNot($domain_id, true);
            $this->cache_handler->writeArrayToFile('settings', self::$settings[$domain_id], 'filetype_settings.php',
                    'domains/' . $domain_id);
        }
    }

    public function enabledTypes(string $domain_id)
    {
        $this->loadSettingsIfNot($domain_id);
        $enabled = array();

        foreach (self::$settings[$domain_id] as $type => $settings)
        {
            if (isset($settings['enabled']) && $settings['enabled'])
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

        if (!isset(self::$settings[$domain_id][$type]) || !isset(self::$settings[$domain_id][$type]['formats']))
        {
            return $enabled;
        }

        foreach (self::$settings[$domain_id][$type]['formats'] as $format => $setting)
        {
            if ($setting)
            {
                $enabled[] = $format;
            }
        }

        return $enabled;
    }

    public function availableFormats()
    {
        $this->loadDataIfNot(false);
        $available = array();

        foreach (self::$extensions as $data)
        {
            if (!is_array($data))
            {
                $data = $this->extensionData($data);
            }

            if ($data['enabled'])
            {
                $available[$data['format']] = true;
            }
        }

        return $available;
    }
}