<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_regen(&$dataforce, $id, $mode)
{
    global $link_resno, $link_updates;
    $dbh = nel_get_db_handle();
    require_once INCLUDE_PATH . 'output-filter.php';
    nel_toggle_session();

    if ($mode[2] === 'full')
    {
        $result = $dbh->query('SELECT thread_id FROM ' . THREAD_TABLE . ' WHERE archive_status=0');
        $ids = $result->fetchAll(PDO::FETCH_COLUMN);
        unset($result);
    }

    if ($mode[2] === 'thread')
    {
        if (is_array($id))
        {
            $ids = $id;
        }
        else
        {
            $ids[0] = $id;
        }
    }

    if ($mode[2] === 'main' || $mode[2] === 'full')
    {
        require_once INCLUDE_PATH . 'output/main-generation.php';
        nel_update_archive_status($dataforce);
        $dataforce['response_id'] = 0;
        $link_resno = 0;
        nel_main_nel_thread_generator($dataforce);
    }

    if ($mode[2] === 'thread' || $mode[2] === 'full')
    {
        require_once INCLUDE_PATH . 'output/thread-generation.php';
        $threads = count($ids);
        $i = 0;

        while ($i < $threads)
        {
            $dataforce['response_id'] = $ids[$i];
            nel_thread_generator($dataforce);
            ++ $i;
        }
    }

    if ($mode[2] === 'cache' || $mode[2] === 'full')
    {
        $dataforce['rules_list'] = nel_cache_rules();
        nel_cache_settings();
        $dataforce['post_links'] = $link_updates;
        nel_regen_template_cache();
        nel_write_multi_cache($dataforce);
    }

    nel_toggle_session();
    $dataforce['post_links'] = $link_updates;
}
