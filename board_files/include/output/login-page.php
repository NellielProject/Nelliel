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
    $dom->loadTemplateFromFile('management/login.html');
    $dom->getElementById('login-form')->setAttribute('action', PHP_SELF, 'none');
    nel_process_i18n($dom);
    $render->appendOutput($dom->outputHTML());
}