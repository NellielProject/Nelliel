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
    $domain = new \Nelliel\Domain('', new \Nelliel\CacheHandler(), nel_database());
    $domain->renderInstance(new \NellielTemplates\RenderCore());
    nel_render_general_header($domain);
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'about_nelliel.html');
    $dom->getElementById('version')->setContent('Version: ' . NELLIEL_VERSION);
    $dom->getElementById('disclaimer-image')->extSetAttribute('src', IMAGES_DIR . '/wat/luna_canterlot_disclaimer.png');
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}
