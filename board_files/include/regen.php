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
    $dataforce['rules_list'] = nel_cache_rules();
    nel_cache_settings();
    nel_regen_template_cache();
    nel_write_multi_cache($dataforce);
}

function nel_regen_index($dataforce)
{
    require_once INCLUDE_PATH . 'output-filter.php';
    require_once INCLUDE_PATH . 'output/main-generation.php';
    nel_update_archive_status($dataforce);
    $dataforce['response_id'] = 0;
    $link_resno = 0;
    nel_main_thread_generator($dataforce, true);
}

function nel_regen_all_pages($dataforce)
{
    $query = 'SELECT thread_id FROM ' . THREAD_TABLE . ' WHERE archive_status=0';
    $result = nel_pdo_simple_query($query);
    $ids = nel_pdo_do_fetchall($result, PDO::FETCH_COLUMN);
    nel_regen_threads($dataforce, true, $ids);
    nel_regen_index($dataforce);
}

function nel_regen(&$dataforce, $ids, $mode)
{
    if ($mode[2] === 'full')
    {
        nel_regen_all_pages($dataforce);
    }

    if ($mode[2] === 'index')
    {
        nel_regen_index($dataforce);
    }

    if ($mode[2] === 'thread')
    {
        nel_regen_threads($dataforce, true, $ids);
    }

    if ($mode[2] === 'cache')
    {
        nel_regen_cache($dataforce);
    }
}
