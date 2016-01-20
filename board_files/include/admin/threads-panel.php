<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_thread_panel($dataforce, $authorize, $plugins, $dbh)
{
    $mode = $dataforce['mode_action'];

    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_thread_panel'))
    {
        nel_derp(103, array('origin' => 'ADMIN'));
    }

    require_once INCLUDE_PATH . 'output/thread-panel-generation.php';
    if (isset($dataforce['expand_thread']))
    {
        $expand = TRUE;
    }
    else
    {
        $expand = FALSE;
    }

    if ($mode === 'update')
    {
        $updates = nel_thread_updates($dataforce, $plugins, $dbh);
        nel_regen($dataforce, $updates, 'thread', FALSE, $dbh);
        nel_regen($dataforce, NULL, 'main', FALSE, $dbh);
    }

    nel_render_thread_panel($dataforce, $expand, $dbh);
}
?>