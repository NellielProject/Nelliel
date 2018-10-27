<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_manage_boards_panel($user)
{
    if (!$user->boardPerm('', 'perm_manage_boards_access'))
    {
        nel_derp(370, _gettext('You are not allowed to access the board manager panel.'));
    }

    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $language = new \Nelliel\Language\Language($authorization);
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, null,
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Create new board')));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/manage_boards_panel_main.html');
    $dom->getElementById('create-board-form')->extSetAttribute('action',
            PHP_SELF . '?module=manage-boards&action=add');
    $language->i18nDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}