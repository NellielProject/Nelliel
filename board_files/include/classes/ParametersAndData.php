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

    function __construct($database, CacheHandler $cache_handler)
    {
        $this->database = $database;
        $this->cache_handler = $cache_handler;
    }

    public function siteSettings(string $setting = null, bool $cache_regen = false)
    {
        if (empty(self::$site_settings) || $cache_regen)
        {
            $settings = $this->cache_handler->loadArrayFromCache('site_settings.php', 'site_settings');

            if (empty($settings) || $cache_regen)
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
                        $this->cache_handler->writeCacheFile(CACHE_FILE_PATH, 'site_settings.php',
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

    public function boardReferences($board_id, string $reference = null)
    {
        if ($board_id === '' || is_null($board_id))
        {
            return;
        }

        if (empty(self::$board_references[$board_id]))
        {
            $prepared = $this->database->prepare('SELECT * FROM "nelliel_board_data" WHERE "board_id" = ?');
            $board_data = $this->database->executePreparedFetch($prepared, [$board_id], PDO::FETCH_ASSOC);
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
            self::$board_references[$board_id] = $new_reference;
        }

        if (is_null($reference))
        {
            return self::$board_references[$board_id];
        }

        return self::$board_references[$board_id][$reference];
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