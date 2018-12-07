<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Board
{
    private $board_id;
    private $board_settings;
    private $board_references;
    private $cache_handler;
    private $database;

    public function __construct($board_id, $cache_handler, $database)
    {
        $this->board_id = $board_id;
        $this->cache_handler = $cache_handler;
        $this->database = $database;
    }

    public function id()
    {
        return $this->board_id;
    }

    public function setting($setting = null)
    {
        if (empty($this->board_settings))
        {
            $this->loadSettings();
        }

        if (is_null($setting))
        {
            return $this->board_settings;
        }

        return $this->board_settings[$setting];
    }


    public function reference($reference = null)
    {
        if (empty($this->board_references))
        {
            $prepared = $this->database->prepare('SELECT * FROM "nelliel_board_data" WHERE "board_id" = ?');
            $board_data = $this->database->executePreparedFetch($prepared, array($this->board_id), PDO::FETCH_ASSOC);
            $new_reference = array();
            $board_path = BASE_PATH . $board_data['board_directory'] . '/';
            $new_reference['board_directory'] = $board_data['board_directory'];
            $new_reference['db_prefix'] = $board_data['db_prefix'];
            $new_reference['locked'] = (bool) $board_data['locked'];
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
            $this->board_references = $new_reference;
        }

        if (is_null($reference))
        {
            return $this->board_references;
        }

        return $this->board_references[$reference];
    }

    private function loadSettings()
    {
        $settings = $this->cache_handler->loadArrayFromCache($this->board_id . '/board_settings.php', 'board_settings');

        if (empty($settings))
        {
            $settings = $this->loadSettingsFromDatabase();

            if (USE_INTERNAL_CACHE)
            {
                $this->cache_handler->writeCacheFile(CACHE_PATH . $this->board_id . '/', 'board_settings.php',
                        '$board_settings = ' . var_export($settings, true) . ';');
            }
        }

        $this->board_settings = $settings;
    }

    private function loadSettingsFromDatabase()
    {
        $settings = array();
        $prepared = $this->database->prepare(
                'SELECT "db_prefix" FROM "' . BOARD_DATA_TABLE . '" WHERE "board_id" = ?');
        $db_prefix = $this->database->executePreparedFetch($prepared, array($this->board_id), PDO::FETCH_COLUMN);
        $config_table = $db_prefix . '_config';
        $config_list = $this->database->executeFetchAll(
                'SELECT * FROM "' . $config_table . '" WHERE "config_type" = \'board_setting\'', PDO::FETCH_ASSOC);

        foreach ($config_list as $config)
        {
            $config['setting'] = nel_cast_to_datatype($config['setting'], $config['data_type']);
            $settings[$config['config_name']] = $config['setting'];
        }

        return $settings;
    }

    public function regenCache()
    {
        $settings = $this->loadSettingsFromDatabase();

        if (USE_INTERNAL_CACHE)
        {
            $this->cache_handler->writeCacheFile(CACHE_PATH . $this->board_id . '/', 'board_settings.php',
                    '$board_settings = ' . var_export($settings, true) . ';');
        }
    }
}