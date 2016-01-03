<?php
function nel_render_derp($diagnostic)
{
    nel_render_init(TRUE);
    $dat = '';
    $dat .= nel_render_header(array(), 'DERP', array());
    $dat .= nel_parse_template('derp.tpl', '', FALSE);
    $dat .= nel_render_basic_footer();
    return $dat;
}
?>