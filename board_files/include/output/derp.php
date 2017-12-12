<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_derp()
{
    $render = new nel_render();
    nel_render_header(array(), $render, array());
    $render1 = new NellielTemplates\RenderCore();
    $dom = $render1->newDOMDocument();
    $render1->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $dom->loadTemplateFromFile('derp.html');
    $dom->getElementById('error-message')->setContent(nel_get_derp('error-message'));
    $dom->getElementById('error-data')->setContent(''); // TODO: This actually have something{PHP_SELF2}{PHP_EXT}
    $return_link = $dom->getElementById('return-link')->extSetAttribute('href', PHP_SELF2 . PHP_EXT);
    nel_process_i18n($dom);
    $render->appendOutput($dom->outputHTML());
    nel_render_footer($render, false);
    $render->output(true);
}
