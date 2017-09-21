<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_staff_panel_add($dataforce)
{
    $render = new nel_render();
    nel_render_header($dataforce, $render, array());
    $render->parse('staff_panel_add.tpl', 'management');
    nel_render_footer($render, FALSE, FALSE, FALSE, FALSE, FALSE);
    $render->output(TRUE);
}

function nel_render_staff_panel_user_edit($dataforce, $user_id)
{
    $authorize = nel_get_authorization();
    $user = $authorize->get_user($user_id);
    $render = new nel_render();
    array_walk($user['perms'], create_function('&$item1', '$item1 = is_bool($item1) ? $item1 === TRUE ? "checked" : "" : $item1;'));
    $render->add_multiple_data($user['perms']);
    nel_render_header($dataforce, $render, array());
    $render->parse('staff_panel_user_edit.tpl', 'management');
    nel_render_footer($render, FALSE, FALSE, FALSE, FALSE, FALSE);
    $render->output(TRUE);
}
?>