<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_login_page(\Nelliel\Domain $domain)
{
    $url_constructor = new \Nelliel\URLConstructor();
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    $output_header = new \Nelliel\Output\OutputHeader($domain, nel_database());
    $extra_data = ['header' => _gettext('Management Login')];
    $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/login.html');
    $form_action = $url_constructor->dynamic(MAIN_SCRIPT, ['module' => 'login', 'action' => 'login']);
    $dom->getElementById('login-form')->extSetAttribute('action', $form_action);
    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
}