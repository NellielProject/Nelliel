<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_thread_panel($dataforce, $authorize)
{
    $mode = $dataforce['mode'];

    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_post_access'))
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

    if ($mode === 'admin->thread->update')
    {
        $updates = nel_thread_updates($dataforce);
        nel_regen_threads($dataforce, true, $updates);
        nel_regen($dataforce, NULL, 'main');
    }

    nel_render_thread_panel($dataforce, $expand);
}
