<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_derp($diagnostic, $board_id = null)
{
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'derp.html');
    $dom->getElementById('error-message')->setContent($diagnostic['error-message']);
    $dom->getElementById('error-data')->setContent(''); // TODO: This actually have something

    if (!is_null($board_id))
    {
        $return_link = $dom->getElementById('return-link')->extSetAttribute('href',
                nel_parameters_and_data()->boardReferences(INPUT_BOARD_ID, 'board_directory') . '/' . PHP_SELF2 . PHP_EXT);
    }

    $do_styles = (is_null($board_id)) ? false : true;
    nel_language()->i18nDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render, $board_id, null, $do_styles);
    echo $render->outputRenderSet();
}
