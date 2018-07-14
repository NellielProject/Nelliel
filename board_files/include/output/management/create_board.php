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
    nel_render_general_header($render, null, null,
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Create new board')));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/create_board.html');
    $dom->getElementById('create-board-form')->extSetAttribute('action',
            PHP_SELF . '?manage=general&module=create-board&action=create');
    nel_language()->i18nDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}