<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class ParametersAndData
{
    private $database;
    private $cache_handler;
    private static $site_settings = array();
    private static $board_settings = array();
    private static $filetype_settings = array();
    private static $board_references = array();
    private static $filetype_data = array();
    private static $file_filters = array();

    function __construct($database, $cache_handler)
    {
        $this->database = $database;
        $this->cache_handler = $cache_handler;
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

    public function siteSettings($setting = null, $cache_regen = false)
    {
        if (empty(self::$site_settings) || $cache_regen)
        {
            $settings = $this->loadArrayFromCache('site_settings.php', 'site_settings');

            if ($settings === false || $cache_regen)
            {
                if ($this->database->tableExists(SITE_CONFIG_TABLE))
                {
                    $config_list = $this->database->executeFetchAll('SELECT * FROM "' . SITE_CONFIG_TABLE . '"',
                            PDO::FETCH_ASSOC);

                    foreach ($config_list as $config)
                    {
                        $config['setting'] = nel_cast_to_datatype($config['setting'], $config['data_type']);
                        $settings[$config['config_name']] = $config['setting'];
                    }

                    if (USE_INTERNAL_CACHE || $cache_regen)
                    {
                        $this->cache_handler->writeCacheFile(CACHE_PATH, 'site_settings.php',
                                '$site_settings = ' . var_export($settings, true) . ';');
                    }
                }
                else
                {
                    $settings = self::$site_settings;
                }
            }

            self::$site_settings = $settings;
        }

        if (is_null($setting))
        {
            return self::$site_settings;
        }

        return self::$site_settings[$setting];
    }

    public function boardSettings($board_id, $setting = null, $cache_regen = false)
    {
        if ($board_id === '' || is_null($board_id))
        {
            return;
        }

        if (empty(self::$board_settings[$board_id]) || $cache_regen)
        {
            $settings = $this->loadArrayFromCache($board_id . '/board_settings.php', 'board_settings');

            if ($settings === false || $cache_regen)
            {
                $prepared = $this->database->prepare(
                        'SELECT "db_prefix" FROM "' . BOARD_DATA_TABLE . '" WHERE "board_id" = ?');
                $db_prefix = $this->database->executePreparedFetch($prepared, array($board_id), PDO::FETCH_COLUMN);
                $config_table = $db_prefix . '_config';
                $config_list = $this->database->executeFetchAll(
                        'SELECT * FROM "' . $config_table . '" WHERE "config_type" = \'board_setting\'', PDO::FETCH_ASSOC);

                foreach ($config_list as $config)
                {
                    $config['setting'] = nel_cast_to_datatype($config['setting'], $config['data_type']);
                    $settings[$config['config_name']] = $config['setting'];
                }

                if (USE_INTERNAL_CACHE || $cache_regen)
                {
                    $this->cache_handler->writeCacheFile(CACHE_PATH . $board_id . '/', 'board_settings.php',
                            '$board_settings = ' . var_export($settings, true) . ';');
                }
            }

            self::$board_settings[$board_id] = $settings;
        }

        if (is_null($setting))
        {
            return self::$board_settings[$board_id];
        }

        return self::$board_settings[$board_id][$setting];
    }

    public function filetypeSettings($board_id, $setting = null, $cache_regen = false)
    {
        if ($board_id === '' || is_null($board_id))
        {
            return;
        }

        if (empty(self::$filetype_settings[$board_id]) || $cache_regen)
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

        if (is_null($setting))
        {
            return self::$filetype_settings[$board_id];
        }

        return self::$filetype_settings[$board_id][$setting];
    }

    public function boardReferences($board_id, $reference = null)
    {
        if ($board_id === '' || is_null($board_id))
        {
            return;
        }

        if (empty(self::$board_references[$board_id]))
        {
            $prepared = $this->database->prepare('SELECT * FROM "nelliel_board_data" WHERE "board_id" = ?');
            $board_data = $this->database->executePreparedFetch($prepared, array($board_id), PDO::FETCH_ASSOC);
            $new_reference = array();
            $board_path = BASE_PATH . $board_data['board_directory'] . '/';
            $new_reference['board_directory'] = $board_data['board_directory'];
            $new_reference['db_prefix'] = $board_data['db_prefix'];
            $new_reference['src_dir'] = 'src';
            $new_reference['thumb_dir'] = 'thumb';
            $new_reference['page_dir'] = 'threads';
            $new_reference['archive_dir'] = 'archive';
            $new_reference['board_path'] = $board_path;
            $new_reference['src_path'] = $board_path . $new_reference['src_dir'] . '/';
            $new_reference['thumb_path'] = $board_path . $new_reference['thumb_dir'] . '/';
            $new_reference['page_path'] = $board_path . $new_reference['page_dir'] . '/';
            $new_reference['archive_path'] = $board_path . $new_reference['archive_dir'] . '/';
            $new_reference['archive_src_path'] = $board_path . $new_reference['archive_dir'] . '/' .
                    $new_reference['src_dir'] . '/';
            $new_reference['archive_thumb_path'] = $board_path . $new_reference['archive_dir'] . '/' .
                    $new_reference['thumb_dir'] . '/';
            $new_reference['archive_page_path'] = $board_path . $new_reference['archive_dir'] . '/' .
                    $new_reference['page_dir'] . '/';
            $new_reference['post_table'] = $new_reference['db_prefix'] . '_posts';
            $new_reference['thread_table'] = $new_reference['db_prefix'] . '_threads';
            $new_reference['content_table'] = $new_reference['db_prefix'] . '_content';
            $new_reference['archive_post_table'] = $new_reference['db_prefix'] . '_archive_posts';
            $new_reference['archive_thread_table'] = $new_reference['db_prefix'] . '_archive_threads';
            $new_reference['archive_content_table'] = $new_reference['db_prefix'] . '_archive_content';
            $new_reference['config_table'] = $new_reference['db_prefix'] . '_config';
            self::$board_references[$board_id] = $new_reference;
        }

        if (is_null($reference))
        {
            return self::$board_references[$board_id];
        }

        return self::$board_references[$board_id][$reference];
    }

    public function filetypeData($extension = null)
    {
        if (empty(self::$filetype_data))
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

        if (is_null($extension))
        {
            return self::$filetype_data;
        }

        return self::$filetype_data[$extension];
    }

    public function fileFilters()
    {
        if (empty(self::$file_filters))
        {
            $loaded = false;

            if (!$loaded)
            {
                $filters = $this->database->executeFetchAll(
                        'SELECT "hash_type", "file_hash" FROM "nelliel_file_filters"', PDO::FETCH_ASSOC);

                foreach ($filters as $filter)
                {
                    self::$file_filters[$filter['hash_type']][] = $filter['file_hash'];
                }
            }
        }

        return self::$file_filters;
    }
}