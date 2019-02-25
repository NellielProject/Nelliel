<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// The content this function presents must remain intact and be accessible to users
//
function nel_about_nelliel_screen()
{
    $domain = new \Nelliel\DomainSite(new \Nelliel\CacheHandler(), nel_database(), new \Nelliel\Language\Translator());
    $domain->renderInstance(new \Nelliel\RenderCore());
    $domain->renderInstance()->startRenderTimer();
    nel_render_general_header($domain);
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->getTemplateInstance()->setTemplatePath(INCLUDE_PATH . 'wat/');
    $domain->renderInstance()->loadTemplateFromFile($dom, 'about_nelliel.html');
    $domain->renderInstance()->getTemplateInstance()->setTemplatePath($domain->templatePath());
    $dom->getElementById('version')->setContent('Version: ' . NELLIEL_VERSION);
    $dom->getElementById('disclaimer-image')->extSetAttribute('src', IMAGES_WEB_PATH . 'wat/luna_canterlot_disclaimer.png');
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}
