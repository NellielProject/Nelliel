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

    public function loadDataFromDatabase()
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

    public function loadSettingsFromDatabase($board_id)
    {
        $settings = $this->loadArrayFromCache($board_id . '/filetype_settings.php', 'filetype_settings');

        if ($settings === false || $cache_regen)
        {
            $prepared = $this->database->prepare(
                    'SELECT "db_prefix" FROM "nelliel_board_data" WHERE "board_id" = ?');
            $db_prefix = $this->database->executePreparedFetch($prepared, array($board_id), PDO::FETCH_COLUMN);
            $config_table = $db_prefix . '_config';
            $config_list = $this->database->executeFetchAll(
                    'SELECT * FROM "' . $config_table . '" WHERE "config_type" = \'filetype_enable\'',
                    PDO::FETCH_ASSOC);
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

    public function getFiletypeData($extension = null)
    {
        if (empty(self::$filetype_data))
        {
            $this->loadDataFromDatabase();
        }

        if (is_null($extension))
        {
            return self::$filetype_data;
        }

        return self::$filetype_data[$extension];
    }

    public function filetypeSettings($board_id, $setting = null, $cache_regen = false)
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

    public function typeIsEnabled($board_id, $type)
    {
        return isset(self::$filetype_settings[$board_id][$type][$type]) && self::$filetype_settings[$board_id][$type][$type];
    }

    public function formatIsEnabled($board_id, $type, $format)
    {
        return isset(self::$filetype_settings[$board_id][$type][$format]) && self::$filetype_settings[$board_id][$type][$format];
    }
}