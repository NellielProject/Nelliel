<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}
/*function nel_render_staff_panel_main($dataforce)
{
    $render = new nel_render();
    nel_render_header($dataforce, $render, array());
    $render->parse('staff_panel_main.tpl', 'management');
    nel_render_footer($render, false, true, false);
    $render->output(TRUE);
}*/

/*function nel_render_staff_panel_user_edit($dataforce, $user_id)
{
    $authorize = nel_get_authorization();
    $user = $authorize->get_user($user_id);
    $render = new nel_render();
    $render->add_multiple_data($user);
    nel_render_header($dataforce, $render, array());
    $render->parse('staff_panel_user_edit.tpl', 'management');
    nel_render_footer($render, false, true, false);
    $render->output(TRUE);
}*/

/*function nel_render_staff_panel_role_edit($dataforce, $role_id)
{
    $authorize = nel_get_authorization();
    $role = $authorize->get_role($role_id);
    $render = new nel_render();
    array_walk($role['permissions'], create_function('&$item1', '$item1 = is_bool($item1) ? $item1 === true ? "checked" : "" : $item1;'));
    $render->add_multiple_data($role);
    $render->add_multiple_data($role['permissions']);
    nel_render_header($dataforce, $render, array());
    $render->parse('staff_panel_role_edit.tpl', 'management');
    nel_render_footer($render, false, true, false);
    $render->output(TRUE);
}*/
