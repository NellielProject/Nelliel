<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_derp($diagnostic)
{
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_header(array(), $render, array());
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'derp.html');
    $dom->getElementById('error-message')->setContent($diagnostic['error-message']);
    $dom->getElementById('error-data')->setContent(''); // TODO: This actually have something{PHP_SELF2}{PHP_EXT}
    $return_link = $dom->getElementById('return-link')->extSetAttribute('href', INPUT_BOARD_ID . '/' . PHP_SELF2 . PHP_EXT);
    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_footer($render, false);
    echo $render->outputRenderSet();
}
