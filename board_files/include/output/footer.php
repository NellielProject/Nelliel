<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_general_footer($render, $domain = null, $dotdot = null, $styles = false, $extra_links = false)
{
    $translator = new \Nelliel\Language\Translator();
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'footer.html');
    $dotdot = (!empty($dotdot)) ? $dotdot : '';

    if (!$styles)
    {
        $dom->getElementById('bottom-styles')->remove();
    }

    if (!$extra_links)
    {
        $dom->getElementById('bottom-extra-links')->remove();
    }

    $dom->getElementById('nelliel-version')->setContent(NELLIEL_VERSION);
    $dom->getElementById('js-ui')->modifyAttribute('src', $dotdot, 'before');
    $locale = (!is_null($domain)) ? $domain->setting('board_language') : DEFAULT_LOCALE;
    $translator->translateDom($dom, $locale);
    $dom->getElementById('timer-result')->setContent(round($render->endRenderTimer(), 4));
    $render->appendHTMLFromDOM($dom);
}