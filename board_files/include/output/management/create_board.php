<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_create_board_panel()
{
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, null, array('header' => 'General Management', 'sub_header' => 'MANAGE_CREATE_BOARD'));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/create_board.html');
    $dom->getElementById('create-board-form')->extSetAttribute('action', PHP_SELF . '?manage=general&module=create-board');
    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}