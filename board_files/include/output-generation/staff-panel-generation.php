<?php
function nel_render_staff_panel_add($dataforce, $auth)
{
    nel_render_init(TRUE);
    $dat = '';
    $dat .= nel_render_header($dataforce, 'ADMIN', array());
    $dat .= nel_parse_template('staff_panel_add.tpl', 'management',FALSE);
    $dat .= nel_render_footer(FALSE, FALSE, FALSE, FALSE, FALSE);
    return $dat;
}

function nel_render_staff_panel_edit($dataforce, $auth)
{
    nel_render_init(TRUE);
    $dat = '';
    array_walk($auth['perms'], create_function('&$item1', '$item1 = is_bool($item1) ? $item1 === TRUE ? "checked" : "" : $item1;'));
    nel_render_multiple_in($auth['perms']);
    $dat .= nel_render_header($dataforce, 'ADMIN', array());
    $dat .= nel_parse_template('staff_panel_edit.tpl', 'management',FALSE);
    $dat .= nel_render_footer(FALSE, FALSE, FALSE, FALSE, FALSE);
    return $dat;
}
?>