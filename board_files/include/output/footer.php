<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_general_footer($domain, $dotdot = null, $styles = false)
{
    $translator = new \Nelliel\Language\Translator();
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'footer.html');
    $dotdot = (!empty($dotdot)) ? $dotdot : '';

    if (!$styles)
    {
        $dom->getElementById('bottom-styles')->remove();
    }

    $dom->getElementById('nelliel-version')->setContent(NELLIEL_VERSION);
    $dom->getElementById('js-ui')->modifyAttribute('src', $dotdot, 'before');
    $locale = ($domain->id() === '') ? DEFAULT_LOCALE : $domain->setting('language');
    $translator->translateDom($dom, $locale);
    $dom->getElementById('timer-result')->setContent(round($domain->renderInstance()->endRenderTimer(), 4));
    $domain->renderInstance()->appendHTMLFromDOM($dom);
}