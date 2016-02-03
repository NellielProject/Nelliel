<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_regen(&$dataforce, $id, $mode, $modmode, $dbh)
{
    global $link_resno, $link_updates;
    
    require_once INCLUDE_PATH . 'output-filter.php';
    
    if (!empty($_SESSION) && !$modmode)
    {
        $temp = $_SESSION['ignore_login'];
        $_SESSION['ignore_login'] = TRUE;
    }
    
    if ($mode === 'full')
    {
        $result = $dbh->query('SELECT post_number FROM ' . POSTTABLE . ' WHERE response_to=0 AND archive_status=0');
        $ids = $result->fetchAll(PDO::FETCH_COLUMN);
    }
    
    if ($mode === 'thread')
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
    
    if ($mode === 'main' || $mode === 'full')
    {
        require_once INCLUDE_PATH . 'output/main-generation.php';
        nel_update_archive_status($dataforce, $dbh);
        $dataforce['response_id'] = 0;
        $link_resno = 0;
        nel_main_nel_thread_generator($dataforce, $dbh);
    }
    
    if ($mode === 'thread' || $mode === 'full')
    {
        require_once INCLUDE_PATH . 'output/thread-generation.php';
        $threads = count($ids);
        $i = 0;
        
        while ($i < $threads)
        {
            $dataforce['response_id'] = $ids[$i];
            nel_thread_generator($dataforce, $dbh);
            ++ $i;
        }
    }
    
    if ($mode === 'cache')
    {
        $dataforce['rules_list'] = nel_cache_rules($dbh);
        nel_cache_settings($dbh);
        $dataforce['post_links'] = $link_updates;
        nel_regen_template_cache();
    }
    
    if (!empty($_SESSION) && !$modmode)
    {
        $_SESSION['ignore_login'] = $temp;
    }
    
    $dataforce['post_links'] = $link_updates;
}
?>