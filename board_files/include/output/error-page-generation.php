<?php
function nel_render_derp($diagnostic)
{
    $render = new nel_render();
    nel_render_header(array(), $render, array());
    $render->parse('derp.tpl', '');
    nel_render_basic_footer($render);
    echo $render->output();
}
?>