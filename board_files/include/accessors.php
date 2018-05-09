<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_authorize()
{
    static $authorize;

    if (!isset($authorize))
    {
        $authorize = new \Nelliel\Authorization();
    }

    return $authorize;
}

function nel_sessions()
{
    static $sessions;

    if (!isset($sessions))
    {
        $sessions = new \Nelliel\Sessions();
    }

    return $sessions;
}

function nel_site_settings($setting = null, $cache_regen = false)
{
    static $settings;

    if (!isset($settings) || $cache_regen)
    {
        $settings = array();
        $site_settings = array();
        $loaded = false;

        if (USE_INTERNAL_CACHE && !cache_regen)
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
                if ($config['data_type'] === 'bool')
                {
                    $config['setting'] = (bool) $config['setting'];
                }
                else if ($config['data_type'] === 'int')
                {
                    $config['setting'] = intval($config['setting']);
                }
                else if ($config['data_type'] === 'str')
                {
                    $config['setting'] = print_r($config['setting'], true);
                }

                $site_settings[$config['config_name']] = $config['setting'];
            }

            if (USE_INTERNAL_CACHE || $cache_regen)
            {
                $cacheHandler = new \Nelliel\CacheHandler();
                $header = '<?php if(!defined("NELLIEL_VERSION")){die("NOPE.AVI");}';
                $cacheHandler->writeCacheFile(CACHE_PATH . $board_id . '/', 'site_settings.php', '$site_settings = ' .
                     var_export($site_settings, true) . ';', $header);
            }
        }

        $settings = $site_settings;
    }

    if (is_null($setting))
    {
        return $settings;
    }

    return $settings[$setting];
}

function nel_board_settings($board_id, $setting = null, $cache_regen = false)
{
    static $settings;

    if ($board_id === '' || is_null($board_id))
    {
        return;
    }

    if (!isset($settings))
    {
        $settings = array();
    }

    if (!isset($settings[$board_id]) || $cache_regen)
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
            $config_list = $dbh->executeFetchAll('SELECT * FROM "' . $config_table .
                 '" WHERE "config_type" = \'board_setting\'', PDO::FETCH_ASSOC);

            foreach ($config_list as $config)
            {
                if ($config['data_type'] === 'bool')
                {
                    $config['setting'] = (bool) $config['setting'];
                }
                else if ($config['data_type'] === 'int')
                {
                    $config['setting'] = intval($config['setting']);
                }
                else if ($config['data_type'] === 'str')
                {
                    $config['setting'] = print_r($config['setting'], true);
                }

                $board_settings[$config['config_name']] = $config['setting'];
            }

            if (USE_INTERNAL_CACHE || $cache_regen)
            {
                $cacheHandler = new \Nelliel\CacheHandler();
                $header = '<?php if(!defined("NELLIEL_VERSION")){die("NOPE.AVI");}';
                $cacheHandler->writeCacheFile(CACHE_PATH . $board_id . '/', 'board_settings.php', '$board_settings = ' .
                     var_export($board_settings, true) . ';', $header);
            }
        }

        $settings[$board_id] = $board_settings;
    }

    if (is_null($setting))
    {
        return $settings[$board_id];
    }

    return $settings[$board_id][$setting];
}

function nel_filetype_settings($board_id, $setting = null, $cache_regen = false)
{
    static $settings;

    if ($board_id === '' || is_null($board_id))
    {
        return;
    }

    if (!isset($settings))
    {
        $settings = array();
    }

    if (!isset($settings[$board_id]) || $cache_regen)
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
            $config_list = $dbh->executeFetchAll('SELECT * FROM "' . $config_table .
                 '" WHERE "config_type" = \'filetype_enable\'', PDO::FETCH_ASSOC);
            $filetype_settings = array();

            foreach ($config_list as $config)
            {
                $filetype_settings[$config['config_category']][utf8_strtolower($config['config_name'])] = (bool) $config['setting'];
            }

            if (USE_INTERNAL_CACHE || $cache_regen)
            {
                $cacheHandler = new \Nelliel\CacheHandler();
                $header = '<?php if(!defined("NELLIEL_VERSION")){die("NOPE.AVI");}';
                $cacheHandler->writeCacheFile(CACHE_PATH . $board_id . '/', 'filetype_settings.php', '$filetype_settings = ' .
                     var_export($filetype_settings, true) . ';', $header);
            }
        }

        $settings[$board_id] = $filetype_settings;
    }

    if (is_null($setting))
    {
        return $settings[$board_id];
    }
    else
    {
        return $settings[$board_id][$setting];
    }
}

function nel_fgsfds($entry, $new_value = null)
{
    static $fgsfds;

    if (!isset($fgsfds))
    {
        $fgsfds = array();
    }

    if (!is_null($new_value))
    {
        $fgsfds[$entry] = $new_value;
    }

    if (isset($fgsfds[$entry]))
    {
        return $fgsfds[$entry];
    }

    return null;
}

function nel_board_references($board_id, $reference = null)
{
    static $references;

    if (true_empty($board_id))
    {
        return;
    }

    if (!isset($references))
    {
        $references = array();
    }

    if (!isset($references[$board_id]))
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
        $new_reference['archive_thumb_path'] = $board_path . $new_reference['archive_dir'] . $new_reference['thumb_dir'];
        $new_reference['archive_page_path'] = $board_path . $new_reference['archive_dir'] . $new_reference['page_dir'];
        $new_reference['post_table'] = $new_reference['db_prefix'] . '_posts';
        $new_reference['thread_table'] = $new_reference['db_prefix'] . '_threads';
        $new_reference['file_table'] = $new_reference['db_prefix'] . '_files';
        $new_reference['archive_post_table'] = $new_reference['db_prefix'] . '_archive_posts';
        $new_reference['archive_thread_table'] = $new_reference['db_prefix'] . '_archive_threads';
        $new_reference['archive_file_table'] = $new_reference['db_prefix'] . '_archive_files';
        $new_reference['config_table'] = $new_reference['db_prefix'] . '_config';
        $references[$board_id] = $new_reference;
    }

    if (!is_null($reference))
    {
        return $references[$board_id][$reference];
    }

    return $references[$board_id];
}

function nel_get_filetype_data($extension = null)
{
    static $filetypes;

    if (!isset($filetypes))
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
    }

    if (is_null($extension))
    {
        return $filetypes;
    }
    else
    {
        return $filetypes[$extension];
    }
}