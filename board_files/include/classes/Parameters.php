<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Parameters
{
    private $site_settings = array();
    private $board_settings = array();
    private $filetype_settings = array();
    private $board_references = array();

    function __construct($no_hash_load = false)
    {
    }

    public function siteSettings($setting = null, $cache_regen = false)
    {
        if (empty($this->site_settings) || $cache_regen)
        {
            $site_settings = array();
            $loaded = false;

            if (USE_INTERNAL_CACHE && !$cache_regen)
            {
                if (file_exists(CACHE_PATH . 'site_settings.php'))
                {
                    include CACHE_PATH . 'site_settings.php';
                    $loaded = true;
                }
            }

            if (!$loaded)
            {
                $dbh = nel_database();
                $config_list = $dbh->executeFetchAll('SELECT * FROM "nelliel_site_config"', PDO::FETCH_ASSOC);

                foreach ($config_list as $config)
                {
                    $config['setting'] = nel_cast_to_datatype($config['setting'], $config['data_type']);
                    $site_settings[$config['config_name']] = $config['setting'];
                }

                if (USE_INTERNAL_CACHE || $cache_regen)
                {
                    $cacheHandler = new \Nelliel\CacheHandler(true);
                    $cacheHandler->writeCacheFile(CACHE_PATH, 'site_settings.php',
                            '$site_settings = ' . var_export($site_settings, true) . ';');
                }
            }

            $this->site_settings = $site_settings;
        }

        if (is_null($setting))
        {
            return $this->site_settings;
        }

        return $this->site_settings[$setting];
    }

    public function boardSettings($board_id, $setting = null, $cache_regen = false)
    {
        if ($board_id === '' || is_null($board_id))
        {
            return;
        }

        if (empty($this->board_settings[$board_id]) || $cache_regen)
        {
            $board_settings = array();
            $loaded = false;

            if (USE_INTERNAL_CACHE && !$cache_regen)
            {
                if (file_exists(CACHE_PATH . $board_id . '/board_settings.php'))
                {
                    include CACHE_PATH . $board_id . '/board_settings.php';
                    $loaded = true;
                }
            }

            if (!$loaded)
            {
                $dbh = nel_database();
                $prepared = $dbh->prepare('SELECT "db_prefix" FROM "nelliel_board_data" WHERE "board_id" = ?');
                $db_prefix = $dbh->executePreparedFetch($prepared, array($board_id), PDO::FETCH_COLUMN);
                $config_table = $db_prefix . '_config';
                $config_list = $dbh->executeFetchAll(
                        'SELECT * FROM "' . $config_table . '" WHERE "config_type" = \'board_setting\'', PDO::FETCH_ASSOC);

                foreach ($config_list as $config)
                {
                    $config['setting'] = nel_cast_to_datatype($config['setting'], $config['data_type']);
                    $board_settings[$config['config_name']] = $config['setting'];
                }

                if (USE_INTERNAL_CACHE || $cache_regen)
                {
                    $cacheHandler = new \Nelliel\CacheHandler(true);
                    $cacheHandler->writeCacheFile(CACHE_PATH . $board_id . '/', 'board_settings.php',
                            '$board_settings = ' . var_export($board_settings, true) . ';');
                }
            }

            $this->board_settings[$board_id] = $board_settings;
        }

        if (is_null($setting))
        {
            return $this->board_settings[$board_id];
        }

        return $this->board_settings[$board_id][$setting];
    }

    public function filetypeSettings($board_id, $setting = null, $cache_regen = false)
    {
        if ($board_id === '' || is_null($board_id))
        {
            return;
        }

        if (empty($this->filetype_settings[$board_id]) || $cache_regen)
        {
            $filetype_settings = array();
            $loaded = false;

            if (USE_INTERNAL_CACHE && !$cache_regen)
            {
                if (file_exists(CACHE_PATH . $board_id . '/filetype_settings.php'))
                {
                    include CACHE_PATH . $board_id . '/filetype_settings.php';
                    $loaded = true;
                }
            }

            if (!$loaded)
            {
                $dbh = nel_database();
                $prepared = $dbh->prepare('SELECT "db_prefix" FROM "nelliel_board_data" WHERE "board_id" = ?');
                $db_prefix = $dbh->executePreparedFetch($prepared, array($board_id), PDO::FETCH_COLUMN);
                $config_table = $db_prefix . '_config';
                $config_list = $dbh->executeFetchAll(
                        'SELECT * FROM "' . $config_table . '" WHERE "config_type" = \'filetype_enable\'',
                        PDO::FETCH_ASSOC);
                $filetype_settings = array();

                foreach ($config_list as $config)
                {
                    $filetype_settings[$config['config_category']][utf8_strtolower($config['config_name'])] = (bool) $config['setting'];
                }

                if (USE_INTERNAL_CACHE || $cache_regen)
                {
                    $cacheHandler = new \Nelliel\CacheHandler(true);
                    $cacheHandler->writeCacheFile(CACHE_PATH . $board_id . '/', 'filetype_settings.php',
                            '$filetype_settings = ' . var_export($filetype_settings, true) . ';');
                }
            }

            $this->filetype_settings[$board_id] = $filetype_settings;
        }

        if (is_null($setting))
        {
            return $this->filetype_settings[$board_id];
        }

        return $this->filetype_settings[$board_id][$setting];
    }

    public function boardReferences($board_id, $reference = null)
    {
        if ($board_id === '' || is_null($board_id))
        {
            return;
        }

        if (empty($this->board_references[$board_id]))
        {
            $dbh = nel_database();
            $prepared = $dbh->prepare('SELECT * FROM "nelliel_board_data" WHERE "board_id" = ?');
            $board_data = $dbh->executePreparedFetch($prepared, array($board_id), PDO::FETCH_ASSOC);
            $new_reference = array();
            $board_path = BASE_PATH . $board_data['board_directory'] . '/';
            $new_reference['board_directory'] = $board_data['board_directory'];
            $new_reference['db_prefix'] = $board_data['db_prefix'];
            $new_reference['src_dir'] = 'src/';
            $new_reference['thumb_dir'] = 'thumb/';
            $new_reference['page_dir'] = 'threads/';
            $new_reference['archive_dir'] = 'archive/';
            $new_reference['board_path'] = $board_path;
            $new_reference['src_path'] = $board_path . $new_reference['src_dir'];
            $new_reference['thumb_path'] = $board_path . $new_reference['thumb_dir'];
            $new_reference['page_path'] = $board_path . $new_reference['page_dir'];
            $new_reference['archive_path'] = $board_path . $new_reference['archive_dir'];
            $new_reference['archive_src_path'] = $board_path . $new_reference['archive_dir'] . $new_reference['src_dir'];
            $new_reference['archive_thumb_path'] = $board_path . $new_reference['archive_dir'] .
                    $new_reference['thumb_dir'];
            $new_reference['archive_page_path'] = $board_path . $new_reference['archive_dir'] .
                    $new_reference['page_dir'];
            $new_reference['post_table'] = $new_reference['db_prefix'] . '_posts';
            $new_reference['thread_table'] = $new_reference['db_prefix'] . '_threads';
            $new_reference['file_table'] = $new_reference['db_prefix'] . '_files';
            $new_reference['archive_post_table'] = $new_reference['db_prefix'] . '_archive_posts';
            $new_reference['archive_thread_table'] = $new_reference['db_prefix'] . '_archive_threads';
            $new_reference['archive_file_table'] = $new_reference['db_prefix'] . '_archive_files';
            $new_reference['config_table'] = $new_reference['db_prefix'] . '_config';
            $this->references[$board_id] = $new_reference;
        }

        if (is_null($reference))
        {
            return $this->references[$board_id];
        }

        return $this->references[$board_id][$reference];
    }
}