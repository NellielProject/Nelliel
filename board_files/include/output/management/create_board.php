<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_generate_create_board_panel()
{
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header(array(), $render);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/create_board.html');
    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    die();
}