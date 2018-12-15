<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_interstitial($domain, $message, $continue_link)
{
    $domain->renderInstance(new NellielTemplates\RenderCore());
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    nel_render_general_header($domain->renderInstance(), null, $domain->id(),
            array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Confirm Board Deletion')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'interstitial_page.html');
    $dom->getElementById('message-text')->setContent($message);
    $dom->getElementById('continue-link')->setContent($continue_link['text']);
    $dom->getElementById('continue-link')->extSetAttribute('href', $continue_link['href']);
    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
}
