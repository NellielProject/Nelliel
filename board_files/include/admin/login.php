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
        $user_auth = $authorize->get_user($_SESSION['username']);
        $render->add_multiple_data($user_auth['perms']);
        $render->parse('manage_options.tpl', 'management');
    }
    else
    {
        nel_insert_default_admin(); // Let's make sure there's some kind of admin in the system
        $render->parse('manage_login.tpl', 'management');
    }

    nel_render_basic_footer($render);
    $render->output(TRUE);
}
?>