<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_staff_panel_main($dataforce)
{
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_header($dataforce, $render, array());
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/staff_panel_main.html');
    $dom->getElementById('board_id_field')->extSetAttribute('value', BOARD_ID);
    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_footer($render, false);
    echo $render->outputRenderSet();
}

function nel_render_staff_panel_user_edit($dataforce, $user_id)
{
    $authorize = nel_authorize();
    $user = $authorize->get_user($user_id);
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_header($dataforce, $render, array());
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/staff_panel_user_edit.html');
    $dom->getElementById('board_id_field')->extSetAttribute('value', BOARD_ID);
    $dom->getElementById('user-id-field')->extSetAttribute('value', $user['user_id']);
    $dom->getElementById('user-title-field')->extSetAttribute('value', $user['user_title']);
    $dom->getElementById('role-id-field')->extSetAttribute('value', $user['role_id']);

    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_footer($render, false);
    echo $render->outputRenderSet();
}

function nel_render_staff_panel_role_edit($dataforce, $role_id)
{
    $authorize = nel_authorize();
    $role = $authorize->get_role($role_id);
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_header($dataforce, $render, array());
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/staff_panel_role_edit.html');
    $dom->getElementById('board_id_field')->extSetAttribute('value', BOARD_ID);
    $dom->getElementById('role_id')->extSetAttribute('value', $role['role_id']);
    $dom->getElementById('role_level')->extSetAttribute('value', $role['role_level']);
    $dom->getElementById('role_title')->extSetAttribute('value', $role['role_title']);
    $dom->getElementById('capcode_text')->extSetAttribute('value', $role['capcode_text']);

    foreach($role['permissions'] as $key => $value)
    {
        if($value === true)
        {
            $dom->getElementById($key)->extSetAttribute('checked', $value);
        }
    }

    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_footer($render, false);
    echo $render->outputRenderSet();
}