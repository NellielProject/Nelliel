<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_login_page()
{
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $url_constructor = new \Nelliel\URLConstructor();
    $language = new \Nelliel\language\Language($authorization);
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, null, array('header' => _gettext('Management Login')));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/login.html');
    $form_action = $url_constructor->dynamic(PHP_SELF, ['module' => 'login', 'action' => 'login']);
    $dom->getElementById('login-form')->extSetAttribute('action', $form_action);
    $language->i18nDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
}