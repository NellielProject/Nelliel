<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class ParametersAndData
{
    private $site_settings = array();
    private $board_settings = array();
    private $filetype_settings = array();
    private $board_references = array();
    private $filetype_data = array();
    private $file_filters = array();

    function __construct($no_hash_load = false)
    {
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
        if (empty($this->site_settings) || $cache_regen)
        {
            $settings = $this->loadArrayFromCache('site_settings.php', 'site_settings');

            if ($settings === false || $cache_regen)
            {
                $dbh = nel_database();
                $config_list = $dbh->executeFetchAll('SELECT * FROM "nelliel_site_config"', PDO::FETCH_ASSOC);

                foreach ($config_list as $config)
                {
                    $config['setting'] = nel_cast_to_datatype($config['setting'], $config['data_type']);
                    $settings[$config['config_name']] = $config['setting'];
                }

                if (USE_INTERNAL_CACHE || $cache_regen)
                {
                    $cacheHandler = new CacheHandler(true);
                    $cacheHandler->writeCacheFile(CACHE_PATH, 'site_settings.php',
                            '$site_settings = ' . var_export($settings, true) . ';');
                }
            }

            $this->site_settings = $settings;
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
            $settings = $this->loadArrayFromCache($board_id . '/board_settings.php', 'board_settings');

            if ($settings === false || $cache_regen)
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
                    $settings[$config['config_name']] = $config['setting'];
                }

                if (USE_INTERNAL_CACHE || $cache_regen)
                {
                    $cacheHandler = new CacheHandler(true);
                    $cacheHandler->writeCacheFile(CACHE_PATH . $board_id . '/', 'board_settings.php',
                            '$board_settings = ' . var_export($settings, true) . ';');
                }
            }

            $this->board_settings[$board_id] = $settings;
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
            $settings = $this->loadArrayFromCache($board_id . '/filetype_settings.php', 'filetype_settings');

            if ($settings === false || $cache_regen)
            {
                $dbh = nel_database();
                $prepared = $dbh->prepare('SELECT "db_prefix" FROM "nelliel_board_data" WHERE "board_id" = ?');
                $db_prefix = $dbh->executePreparedFetch($prepared, array($board_id), PDO::FETCH_COLUMN);
                $config_table = $db_prefix . '_config';
                $config_list = $dbh->executeFetchAll(
                        'SELECT * FROM "' . $config_table . '" WHERE "config_type" = \'filetype_enable\'',
                        PDO::FETCH_ASSOC);
                $settings = array();

                foreach ($config_list as $config)
                {
                    $settings[$config['config_category']][utf8_strtolower($config['config_name'])] = (bool) $config['setting'];
                }

                if (USE_INTERNAL_CACHE || $cache_regen)
                {
                    $cacheHandler = new CacheHandler(true);
                    $cacheHandler->writeCacheFile(CACHE_PATH . $board_id . '/', 'filetype_settings.php',
                            '$filetype_settings = ' . var_export($settings, true) . ';');
                }
            }

            $this->filetype_settings[$board_id] = $settings;
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

    public function filetypeData($extension = null)
    {
        if (empty($this->filetype_data))
        {
            $filetypes = array();

            $dbh = nel_database();
            $db_results = $dbh->executeFetchAll('SELECT * FROM "nelliel_filetypes"', PDO::FETCH_ASSOC);
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

            $this->filetype_data = $filetypes;
        }

        if (is_null($extension))
        {
            return $this->filetype_data;
        }

        return $this->filetype_data[$extension];
    }

    public function fileFilters()
    {
        if (empty($this->file_filters))
        {
            $loaded = false;

            if (!$loaded)
            {
                $dbh = nel_database();
                $filters = $dbh->executeFetchAll('SELECT "hash_type", "file_hash" FROM "nelliel_file_filters"',
                        PDO::FETCH_ASSOC);

                foreach ($filters as $filter)
                {
                    $this->file_filters[$filter['hash_type']][] = $filter['file_hash'];
                }
            }
        }

        return $this->file_filters;
    }
}