<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_staff_panel_main($dataforce)
{
    $render = new nel_render();
    nel_render_header($dataforce, $render, array());
    $render1 = new NellielTemplates\RenderCore();
    $dom = $render1->newDOMDocument();
    $render1->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $render1->loadTemplateFromFile($dom, 'management/staff_panel_main.html');
    nel_process_i18n($dom);
    $render->appendOutput($render1->outputHTML($dom));
    nel_render_footer($render, false);
    $render->output(true);
}

function nel_render_staff_panel_user_edit($dataforce, $user_id)
{
    $authorize = nel_get_authorization();
    $user = $authorize->get_user($user_id);
    $render = new nel_render();
    nel_render_header($dataforce, $render, array());
    $render1 = new NellielTemplates\RenderCore();
    $dom = $render1->newDOMDocument();
    $render1->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $render1->loadTemplateFromFile($dom, 'management/staff_panel_user_edit.html');

    $dom->getElementById('user-id-field')->extSetAttribute('value', $user['user_id']);
    $dom->getElementById('user-title-field')->extSetAttribute('value', $user['user_title']);
    $dom->getElementById('role-id-field')->extSetAttribute('value', $user['role_id']);

    nel_process_i18n($dom);
    $render->appendOutput($render1->outputHTML($dom));
    nel_render_footer($render, false);
    $render->output(true);
}

function nel_render_staff_panel_role_edit($dataforce, $role_id)
{
    $authorize = nel_get_authorization();
    $role = $authorize->get_role($role_id);
    $render = new nel_render();
    nel_render_header($dataforce, $render, array());
    $render1 = new NellielTemplates\RenderCore();
    $dom = $render1->newDOMDocument();
    $render1->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $render1->loadTemplateFromFile($dom, 'management/staff_panel_role_edit.html');

    $dom->getElementById('role_id')->extSetAttribute('value', $role['role_id']);
    $dom->getElementById('role_level')->extSetAttribute('value', $role['role_level']);
    $dom->getElementById('role_title')->extSetAttribute('value', $role['role_title']);
    $dom->getElementById('capcode_text')->extSetAttribute('value', $role['capcode_text']);

    array_walk($role['permissions'], create_function('&$item1', '$item1 = is_bool($item1) ? $item1 === true ? "checked" : "" : $item1;'));

    foreach($role['permissions'] as $key => $value)
    {
        $dom->getElementById($key)->extSetAttribute('checked', $value);
    }

    nel_process_i18n($dom);
    $render->appendOutput($render1->outputHTML($dom));
    nel_render_footer($render, false, true, false);
    $render->output(TRUE);
}