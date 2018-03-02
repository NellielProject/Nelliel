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
    $render = new NellielTemplates\RenderCore();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'about_nelliel.html');
    $dom->getElementById('version')->setContent('Version: ' . NELLIEL_VERSION);
    $dom->getElementById('disclaimer-image')->extSetAttribute('src', IMAGES_DIR . 'wat/luna_canterlot_disclaimer.png');
    $render->appendHTMLFromDOM($dom);
    echo $render->outputRenderSet();
    die();
}
