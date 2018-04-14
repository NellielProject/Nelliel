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

function nel_ban_hammer()
{
    static $ban_hammer;

    if (!isset($ban_hammer))
    {
        $ban_hammer = new \Nelliel\BanHammer();
    }

    return $ban_hammer;
}

function nel_output_filter()
{
    static $output_filter;

    if (!isset($output_filter))
    {
        $output_filter = new \Nelliel\OutputFilter();
    }

    return $output_filter;
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

function nel_site_settings($setting = null)
{
    static $settings;

    if (!isset($settings))
    {
        $settings = array();

        if (!file_exists(CACHE_PATH . 'site_settings.php'))
        {
            nel_cache_site_settings();
        }

        include CACHE_PATH . 'site_settings.php';
        $settings = $site_settings;
    }

    if (is_null($setting))
    {
        return $settings;
    }

    return $settings[$setting];
}

function nel_board_settings($board_id, $setting = null)
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

    if (!isset($settings[$board_id]))
    {
        if (!file_exists(CACHE_PATH . $board_id . '/board_settings.php'))
        {
            nel_cache_board_settings($board_id);
        }

        include CACHE_PATH . $board_id . '/board_settings.php';
        $settings[$board_id] = $board_settings;
    }

    if (is_null($setting))
    {
        return $settings[$board_id];
    }

    return $settings[$board_id][$setting];
}

function nel_filetype_settings($board_id, $setting = null)
{
    static $settings;

    if ($board_id === '' || is_null($board_id))
    {
        return;
    }

    if (!isset($settings))
    {
        if (!file_exists(CACHE_PATH . $board_id . '/filetype_settings.php'))
        {
            nel_cache_filetype_settings($board_id);
        }

        include CACHE_PATH . $board_id . '/filetype_settings.php';
        $settings[$board_id] = $filetype_settings;
    }

    if (is_null($setting))
    {
        return $settings[$board_id];
    }

    return $settings[$board_id][$setting];
}

function nel_archive($board_id)
{
    static $archives;

    if (!isset($archives))
    {
        $archives = array();
    }

    if (!isset($archives[$board_id]))
    {
        $archives[$board_id] = new \Nelliel\ArchiveAndPrune($board_id);
    }

    return $archives[$board_id];
}

function nel_thread_handler($board_id)
{
    static $thread_handlers;

    if (!isset($thread_handlers))
    {
        $thread_handlers = array();
    }

    if (!isset($thread_handlers[$board_id]))
    {
        $thread_handlers[$board_id] = new \Nelliel\ThreadHandler($board_id);
    }

    return $thread_handlers[$board_id];
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

function nel_file_handler()
{
    static $file_handler;

    if (!isset($file_handler))
    {
        $file_handler = new \Nelliel\FileHandler();
    }

    return $file_handler;
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
            if($result['extension'] == $result['parent_extension'])
            {
                $filetypes[$result['extension']] = $result;
            }
            else
            {
                $sub_extensions[] = $result;
            }
        }

        foreach($sub_extensions as $sub_extension)
        {
            if(array_key_exists($sub_extension['parent_extension'], $filetypes))
            {
                $filetypes[$sub_extension['extension']] = $filetypes[$sub_extension['parent_extension']];
                $filetypes[$sub_extension['extension']]['extension'] = $sub_extension['extension'];
            }
        }
    }

    if(is_null($extension))
    {
        return $filetypes;
    }
    else
    {
        return $filetypes[$extension];
    }
}