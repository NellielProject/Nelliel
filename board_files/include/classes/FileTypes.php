<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class FileTypes
{
    private $database;
    private static $filetype_data;
    private static $filetype_settings;

    function __construct($database)
    {
        $this->database = $database;
    }

    private function loadArrayFromCache($filename, $array_variable)
    {
        if (USE_INTERNAL_CACHE)
        {
            if (file_exists(CACHE_PATH . $filename))
            {
                include CACHE_PATH . $filename;
                $array = $$array_variable;
                return $array;
            }
        }

        return false;
    }

    private function loadDataFromDatabase()
    {
        $filetypes = array();
        $db_results = $this->database->executeFetchAll('SELECT * FROM "nelliel_filetypes"', PDO::FETCH_ASSOC);
        $sub_extensions = array();

        foreach ($db_results as $result)
        {
            if ($result['extension'] == $result['parent_extension'])
            {
                $filetypes[$result['extension']] = $result;
            }
            else
            {
                $sub_extensions[] = $result;
            }
        }

        foreach ($sub_extensions as $sub_extension)
        {
            if (array_key_exists($sub_extension['parent_extension'], $filetypes))
            {
                $filetypes[$sub_extension['extension']] = $filetypes[$sub_extension['parent_extension']];
                $filetypes[$sub_extension['extension']]['extension'] = $sub_extension['extension'];
            }
        }

        self::$filetype_data = $filetypes;
    }

    private function loadSettingsFromDatabase($board_id)
    {
        $settings = $this->loadArrayFromCache($board_id . '/filetype_settings.php', 'filetype_settings');

        if ($settings === false || $cache_regen) // TODO: Handle cache regen
        {
            $prepared = $this->database->prepare('SELECT "db_prefix" FROM "nelliel_board_data" WHERE "board_id" = ?');
            $db_prefix = $this->database->executePreparedFetch($prepared, array($board_id), PDO::FETCH_COLUMN);
            $config_table = $db_prefix . '_config';
            $config_list = $this->database->executeFetchAll(
                    'SELECT * FROM "' . $config_table . '" WHERE "config_type" = \'filetype_enable\'', PDO::FETCH_ASSOC);
            $settings = array();

            foreach ($config_list as $config)
            {
                $settings[$config['config_category']][utf8_strtolower($config['config_name'])] = (bool) $config['setting'];
            }

            if (USE_INTERNAL_CACHE || $cache_regen)
            {
                $this->cache_handler->writeCacheFile(CACHE_PATH . $board_id . '/', 'filetype_settings.php',
                        '$filetype_settings = ' . var_export($settings, true) . ';');
            }
        }

        self::$filetype_settings[$board_id] = $settings;
    }

    public function isValidExtension($extension)
    {
        if (!isset(self::$filetype_data))
        {
            $this->loadDataFromDatabase();
        }

        return isset(self::$filetype_data[$extension]);
    }

    public function extensionData($extension)
    {
        if (!$this->isValidExtension($extension))
        {
            return false;
        }

        return self::$filetype_data[$extension];
    }

    public function settings($board_id, $setting = null, $cache_regen = false)
    {
        if ($board_id === '' || is_null($board_id))
        {
            return;
        }

        if (empty(self::$filetype_settings[$board_id]) || $cache_regen)
        {
            $this->loadSettingsFromDatabase($board_id);
        }

        if (is_null($setting))
        {
            return self::$filetype_settings[$board_id];
        }

        return self::$filetype_settings[$board_id][$setting];
    }

    private function settingsLoaded()
    {
        return !empty(self::$filetype_settings);
    }

    public function extensionIsEnabled($board_id, $extension)
    {
        $extension_data = $this->extensionData($extension);

        if ($extension_data === false)
        {
            return false;
        }

        $type = $extension_data['type'];
        $format = $extension_data['format'];
        return $this->typeIsEnabled($board_id, $type) && $this->formatIsEnabled($board_id, $type, $format);
    }

    public function typeIsEnabled($board_id, $type)
    {
        if (!$this->settingsLoaded())
        {
            $this->loadSettingsFromDatabase($board_id);
        }

        return isset(self::$filetype_settings[$board_id][$type][$type]) &&
                self::$filetype_settings[$board_id][$type][$type];
    }

    public function formatIsEnabled($board_id, $type, $format)
    {
        return isset(self::$filetype_settings[$board_id][$type][$format]) &&
                self::$filetype_settings[$board_id][$type][$format];
    }

    public function verifyFile($extension, $file_path, $start_buffer = 65535, $end_buffer = 65535)
    {
        $file_length = filesize($file_path);
        $end_offset = ($file_length < 65535) ? $file_length : $file_length - 65535;
        $file_test_begin = file_get_contents($file_path, null, null, 0, 65535);
        $file_test_end = file_get_contents($file_path, null, null, $end_offset);
        $extension_data = $this->extensionData($extension);
        return preg_match('#' . $extension_data['id_regex'] . '#', $file_test_begin) ||
                preg_match('#' . $extension_data['id_regex'] . '#', $file_test_end);
    }
}