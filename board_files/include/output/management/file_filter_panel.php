<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_file_filter_panel()
{
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, $board_id, array('header' => 'MANAGE_BOARD', 'sub_header' => 'MANAGE_BANS'));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/file_filter_panel.html');
    $dom->getElementById('add-file-filter-form')->extSetAttribute('action', PHP_SELF . '?manage=general&module=file-filter');
    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}