<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_login($dataforce)
{
    $authorize = nel_get_authorization();
    $render = new nel_render();
    $render->add_data('dotdot', '');
    nel_render_header($dataforce, $render, array());

    if (!nel_session_ignored())
    {
        $user_perms = $authorize->get_user_perms($_SESSION['username']);
        $render->add_multiple_data($user_perms);
        nel_generate_main_panel($render);
    }
    else
    {
        nel_insert_default_admin(); // Let's make sure there's some kind of admin in the system
        nel_insert_role_defaults();
        nel_generate_login_page($render);
    }

    nel_render_footer($render, false);
    $render->output(TRUE);
}
