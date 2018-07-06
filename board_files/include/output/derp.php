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
    nel_render_general_header($render);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'derp.html');
    $dom->getElementById('error-message')->setContent($diagnostic['error-message']);
    $dom->getElementById('error-data')->setContent(''); // TODO: This actually have something
    $return_link = $dom->getElementById('return-link')->extSetAttribute('href',
            nel_parameters_and_data()->boardReferences(INPUT_BOARD_ID, 'board_directory') . '/' . PHP_SELF2 . PHP_EXT);
    nel_language()->i18nDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
}

function nel_render_board_derp($board_id, $diagnostic)
{
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_board_header($board_id, $render);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'derp.html');
    $dom->getElementById('error-message')->setContent($diagnostic['error-message']);
    $dom->getElementById('error-data')->setContent(''); // TODO: This actually have something
    $return_link = $dom->getElementById('return-link')->extSetAttribute('href',
            nel_parameters_and_data()->boardReferences(INPUT_BOARD_ID, 'board_directory') . '/' . PHP_SELF2 . PHP_EXT);
    nel_language()->i18nDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_board_footer($board_id, $render);
    echo $render->outputRenderSet();
}
