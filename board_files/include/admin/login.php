<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_login($dataforce, $authorize)
{
    $render = new nel_render();
    $render->add_data('dotdot', '');
    nel_render_header($dataforce, $render, array());

    if (!empty($_SESSION))
    {
        $user_auth = $authorize->get_user_auth($_SESSION['username']);
        $render->add_multiple_data($user_auth['perms']);
        $render->parse('manage_options.tpl', 'management');
    }
    else
    {
        $render->parse('manage_login.tpl', 'management');
    }

    nel_render_basic_footer($render);
    echo $render->output();
}
?>