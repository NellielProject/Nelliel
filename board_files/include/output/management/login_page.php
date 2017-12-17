<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_generate_login_page($render)
{
    $render1 = new NellielTemplates\RenderCore();
    $dom = $render1->newDOMDocument();
    $render1->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $render1->loadTemplateFromFile($dom, 'management/login.html');
    $dom->getElementById('login-form')->extSetAttribute('action', PHP_SELF);
    nel_process_i18n($dom);
    $render->appendOutput($render1->outputHTML($dom));
}