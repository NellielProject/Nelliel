<?php
function nel_render_staff_panel_add($dataforce, $auth)
{
    $render = new nel_render();
    nel_render_header($dataforce, $render, array());
    $render->input(nel_parse_template('staff_panel_add.tpl', 'management', $render, FALSE));
    nel_render_footer($render, FALSE, FALSE, FALSE, FALSE, FALSE);
    echo $render->output();
}

function nel_render_staff_panel_edit($dataforce, $auth)
{
    $render = new nel_render();
    array_walk($auth['perms'], create_function('&$item1', '$item1 = is_bool($item1) ? $item1 === TRUE ? "checked" : "" : $item1;'));
    $render->add_multiple_data($auth['perms']);
    nel_render_header($dataforce, $render, array());
    $render->input(nel_parse_template('staff_panel_edit.tpl', 'management', $render, FALSE));
    nel_render_footer($render, FALSE, FALSE, FALSE, FALSE, FALSE);
    echo $render->output();
}
?>