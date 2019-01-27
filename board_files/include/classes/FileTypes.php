<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class FileTypes
{
    private $cache_handler;
    private $database;
    private static $filetype_data;
    private static $filetype_settings;
    private static $filetype_categories;

    function __construct($database)
    {
        $this->cache_handler = new CacheHandler();
        $this->database = $database;
    }

    private function loadDataFromDatabase()
    {
        $filetypes = array();
        $db_results = $this->database->executeFetchAll('SELECT * FROM "nelliel_filetypes" ORDER BY "entry" ASC', PDO::FETCH_ASSOC);
        $sub_extensions = array();
        $categories = array();

        foreach ($db_results as $result)
        {
            if($result['extension'] == '')
            {
                $categories[$result['type']] = $result;
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

        self::$filetype_categories = $categories;

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

    private function loadSettingsFromDatabase(string $board_id, bool $ignore_cache = false)
    {
        if(!$ignore_cache)
        {
            $settings = $this->cache_handler->loadArrayFromCache($board_id . '/filetype_settings.php', 'filetype_settings');
        }

        if (empty($settings))
        {
            $prepared = $this->database->prepare('SELECT "db_prefix" FROM "nelliel_board_data" WHERE "board_id" = ?');
            $db_prefix = $this->database->executePreparedFetch($prepared, [$board_id], PDO::FETCH_COLUMN);
            $config_table = $db_prefix . '_config';
            $config_list = $this->database->executeFetchAll(
                    'SELECT * FROM "' . $config_table . '" WHERE "config_type" = \'filetype_enable\'', PDO::FETCH_ASSOC);
            $settings = array();

            foreach ($config_list as $config)
            {
                $settings[$config['config_category']][utf8_strtolower($config['config_name'])] = (bool) $config['setting'];
            }
        }

        self::$filetype_settings[$board_id] = $settings;
    }

    public function getFiletypeData()
    {
        $this->filetypesLoaded(true);
        return self::$filetype_data;
    }

    public function getFiletypeCategories()
    {
        $this->filetypesLoaded(true);
        return self::$filetype_categories;
    }

    public function isValidExtension(string $extension)
    {
        if (!isset(self::$filetype_data))
        {
            $this->loadDataFromDatabase();
        }

        return isset(self::$filetype_data[$extension]);
    }

    public function extensionData(string $extension)
    {
        if (!$this->isValidExtension($extension))
        {
            return false;
        }

        return self::$filetype_data[$extension];
    }

    public function settings(string $board_id, string $setting = null, bool $cache_regen = false)
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

    private function settingsLoaded(string $board_id, bool $load_if_not = false, bool $ignore_cache = false)
    {
        $result = empty(self::$filetype_settings) && empty(self::$filetype_settings[$board_id]);

        if ($result && $load_if_not)
        {
            $this->loadSettingsFromDatabase($board_id, $ignore_cache);
        }

        return $result;
    }

    private function filetypesLoaded(bool $load_if_not = false, bool $ignore_cache = false)
    {
        $result = empty(self::$filetype_data);

        if ($result && $load_if_not)
        {
            $this->loadDataFromDatabase();
            return true;
        }

        return $result;
    }

    public function extensionIsEnabled(string $board_id, string $extension)
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

    public function typeIsEnabled(string $board_id, string $type)
    {
        if (!$this->settingsLoaded($board_id, true))
        {
            $this->loadSettingsFromDatabase($board_id);
        }

        return isset(self::$filetype_settings[$board_id][$type][$type]) &&
                self::$filetype_settings[$board_id][$type][$type];
    }

    public function formatIsEnabled(string $board_id, string $type, string $format)
    {
        return isset(self::$filetype_settings[$board_id][$type][$format]) &&
                self::$filetype_settings[$board_id][$type][$format];
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

    public function generateSettingsCache(string $board_id)
    {
        if (USE_INTERNAL_CACHE)
        {
            $this->settingsLoaded($board_id, true, true);
            $this->cache_handler->writeCacheFile(CACHE_FILE_PATH . $board_id . '/', 'filetype_settings.php',
                    '$filetype_settings = ' . var_export(self::$filetype_settings[$board_id], true) . ';');
        }
    }
}