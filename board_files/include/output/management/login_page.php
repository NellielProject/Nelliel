<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_generate_login_page()
{
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header(array(), $render);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/login.html');
    $dom->getElementById('login-form')->extSetAttribute('action', PHP_SELF);
    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
}