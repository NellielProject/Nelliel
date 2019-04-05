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
    $domain = new \Nelliel\DomainSite(nel_database());
    $domain->renderInstance(new \Nelliel\RenderCoreDOM());
    $domain->renderInstance()->startTimer();
    $output_header = new \Nelliel\Output\OutputHeader($domain);
    $this->render_core->appendToOutput($output_header->render(['header_type' => 'general', 'dotdot' => '']));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->getTemplateInstance()->templatePath(INCLUDE_PATH . 'wat/');
    $template = $render->loadTemplateFromFile('about_nelliel.html');
    $render->loadDOMFromTemplate($dom, $template);
    $domain->renderInstance()->getTemplateInstance()->templatePath($domain->templatePath());
    $dom->getElementById('version')->setContent('Version: ' . NELLIEL_VERSION);
    $dom->getElementById('disclaimer-image')->extSetAttribute('src', IMAGES_WEB_PATH . 'wat/luna_canterlot_disclaimer.png');
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    $output_footer = new \Nelliel\Output\OutputFooter($domain);
    $this->render_core->appendToOutput($output_footer->render(['dotdot' => '', 'generate_styles' => false]));
    echo $domain->renderInstance()->getOutput();
    nel_clean_exit();
}
