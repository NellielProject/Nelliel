<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/thread_panel.php';

function nel_thread_panel($inputs)
{
    $authorize = nel_authorize();
    $board_id = $inputs['board_id'];
    $thread_handler = new \Nelliel\ThreadHandler(nel_database(), $board_id);

    if (!$authorize->getUserPerm($_SESSION['username'], 'perm_post_access', $board_id))
    {
        nel_derp(350, _gettext('You are not allowed to access the threads panel.'));
    }

    if ($inputs['action'] === 'update')
    {
        if (!$authorize->getUserPerm($_SESSION['username'], 'perm_post_modify', $board_id))
        {
            nel_derp(351, _gettext('You are not allowed to modify threads or posts.'));
        }

        $updates = $thread_handler->processContentDeletes();
        $regen = new \Nelliel\Regen();
        $regen->threads($board_id, true, $updates);
        $regen->index($board_id);
    }

    if (isset($_POST['expand_thread']))
    {
        $expand_data = explode(' ', $_POST['expand_thread']);
        nel_render_thread_panel_expand($board_id, $expand_data[1]);
    }
    else
    {
        nel_render_thread_panel_main($board_id);
    }
}
