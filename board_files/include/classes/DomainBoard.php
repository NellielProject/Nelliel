<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class DomainBoard extends Domain
{

    public function __construct(string $domain_id, CacheHandler $cache_handler, $database)
    {
        $this->domain_id = $domain_id;

        if($domain_id === '')
        {
            $this->scope = 'ALL_BOARDS';
        }
        else
        {
            $this->scope = 'BOARD';
        }

        $this->cache_handler = $cache_handler;
        $this->database = $database;
    }

    public function boardExists()
    {
        $prepared = $this->database->prepare('SELECT 1 FROM "nelliel_board_data" WHERE "board_id" = ?');
        $board_data = $this->database->executePreparedFetch($prepared, [$this->domain_id], PDO::FETCH_COLUMN);

        return !empty($board_data);
    }

    protected function loadSettings()
    {
        $settings = $this->cache_handler->loadArrayFromCache($this->domain_id . '/domain_settings.php',
                'domain_settings');

        if (empty($settings))
        {
            $settings = $this->loadSettingsFromDatabase();

            if (USE_INTERNAL_CACHE)
            {
                $this->cache_handler->writeCacheFile(CACHE_FILE_PATH . $this->domain_id . '/', 'domain_settings.php',
                        '$domain_settings = ' . var_export($settings, true) . ';');
            }
        }

        $this->domain_settings = $settings;
    }

    protected function loadReferences()
    {
        $prepared = $this->database->prepare('SELECT * FROM "nelliel_board_data" WHERE "board_id" = ?');
        $board_data = $this->database->executePreparedFetch($prepared, [$this->domain_id], PDO::FETCH_ASSOC);
        $new_reference = array();
        $board_path = BASE_PATH . $board_data['board_id'] . '/';
        $new_reference['board_directory'] = $board_data['board_id'];
        $new_reference['db_prefix'] = $board_data['db_prefix'];
        $new_reference['locked'] = (bool) $board_data['locked'];
        $new_reference['src_dir'] = 'src';
        $new_reference['preview_dir'] = 'preview';
        $new_reference['page_dir'] = 'threads';
        $new_reference['archive_dir'] = 'archive';
        $new_reference['board_path'] = $board_path;
        $new_reference['src_path'] = $board_path . $new_reference['src_dir'] . '/';
        $new_reference['preview_path'] = $board_path . $new_reference['preview_dir'] . '/';
        $new_reference['page_path'] = $board_path . $new_reference['page_dir'] . '/';
        $new_reference['archive_path'] = $board_path . $new_reference['archive_dir'] . '/';
        $new_reference['archive_src_path'] = $board_path . $new_reference['archive_dir'] . '/' .
                $new_reference['src_dir'] . '/';
        $new_reference['archive_preview_path'] = $board_path . $new_reference['archive_dir'] . '/' .
                $new_reference['preview_dir'] . '/';
        $new_reference['archive_page_path'] = $board_path . $new_reference['archive_dir'] . '/' .
                $new_reference['page_dir'] . '/';
        $new_reference['posts_table'] = $new_reference['db_prefix'] . '_posts';
        $new_reference['threads_table'] = $new_reference['db_prefix'] . '_threads';
        $new_reference['content_table'] = $new_reference['db_prefix'] . '_content';
        $new_reference['archive_posts_table'] = $new_reference['db_prefix'] . '_archive_posts';
        $new_reference['archive_threads_table'] = $new_reference['db_prefix'] . '_archive_threads';
        $new_reference['archive_content_table'] = $new_reference['db_prefix'] . '_archive_content';
        $new_reference['config_table'] = $new_reference['db_prefix'] . '_config';
        $this->domain_references = $new_reference;
    }

    protected function loadSettingsFromDatabase()
    {
        $settings = array();
        $prepared = $this->database->prepare('SELECT "db_prefix" FROM "' . BOARD_DATA_TABLE . '" WHERE "board_id" = ?');
        $db_prefix = $this->database->executePreparedFetch($prepared, [$this->domain_id], PDO::FETCH_COLUMN);
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
        if (USE_INTERNAL_CACHE)
        {
            $settings = $this->loadSettingsFromDatabase();
            $this->cache_handler->writeCacheFile(CACHE_FILE_PATH . $this->domain_id . '/', 'domain_settings.php',
                    '$domain_settings = ' . var_export($settings, true) . ';');
        }
    }
}