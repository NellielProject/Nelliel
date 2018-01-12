<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_footer($render, $footer_form, $styles = true, $extra_links = false)
{
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'footer.html');

    if(!$extra_links)
    {
        $dom->getElementById('bottom-extra-links')->removeSelf();
    }

    $dom->getElementById('nelliel-version')->setContent(NELLIEL_VERSION);
    nel_process_i18n($dom);
    $dom->getElementById('timer-result')->setContent(round($render->endRenderTimer(), 4));
    $render->appendHTMLFromDOM($dom);
}
