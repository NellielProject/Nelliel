<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_regen_threads($dataforce, $write, $ids)
{
    require_once INCLUDE_PATH . 'output-filter.php';
    require_once INCLUDE_PATH . 'output/thread-generation.php';
    $threads = count($ids);
    $i = 0;

    while ($i < $threads)
    {
        nel_thread_generator($dataforce, $write, $ids[$i]);
        ++ $i;
    }
}

function nel_regen_cache($dataforce)
{
    global $link_updates;
    $dataforce['rules_list'] = nel_cache_rules();
    nel_cache_settings();
    $dataforce['post_links'] = $link_updates;
    nel_regen_template_cache();
    nel_write_multi_cache($dataforce);
}

function nel_regen(&$dataforce, $ids, $mode)
{
    global $link_resno, $link_updates;
    $dbh = nel_get_db_handle();
    require_once INCLUDE_PATH . 'output-filter.php';
    if($mode[1] !== 'modmode')
    {
        nel_toggle_session();
    }

    if ($mode[2] === 'full')
    {
        $query = 'SELECT thread_id FROM ' . THREAD_TABLE . ' WHERE archive_status=0';
        $ids = nel_pdo_simple_query($query, true, PDO::FETCH_COLUMN, true);
    }

    if ($mode[2] === 'main' || $mode[2] === 'full')
    {
        require_once INCLUDE_PATH . 'output/main-generation.php';
        nel_update_archive_status($dataforce);
        $dataforce['response_id'] = 0;
        $link_resno = 0;
        nel_main_thread_generator($dataforce, true);
    }

    if (/*$mode[2] === 'thread' || */$mode[2] === 'full')
    {
        nel_regen_threads($dataforce, true, $ids);
    }

    if ($mode[2] === 'cache' || $mode[2] === 'full')
    {
        nel_regen_cache();
    }

    if($mode[1] !== 'modmode')
    {
        nel_toggle_session();
    }

    $dataforce['post_links'] = $link_updates;
}
