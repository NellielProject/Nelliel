<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_board_footer($board_id, $render, $styles = true, $extra_links = false)
{
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'footer.html');

    if(!$extra_links)
    {
        $dom->getElementById('bottom-extra-links')->removeSelf();
    }

    $dom->getElementById('nelliel-version')->setContent(NELLIEL_VERSION);
    nel_process_i18n($dom, nel_board_settings($board_id, 'board_language'));
    $dom->getElementById('timer-result')->setContent(round($render->endRenderTimer(), 4));
    $render->appendHTMLFromDOM($dom);
}

function nel_render_general_footer($render, $styles = false, $extra_links = false)
{
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'footer.html');

    if(!$styles)
    {
        $dom->getElementById('bottom-styles-span')->removeSelf();
    }

    if(!$extra_links)
    {
        $dom->getElementById('bottom-extra-links')->removeSelf();
    }

    $dom->getElementById('nelliel-version')->setContent(NELLIEL_VERSION);
    nel_process_i18n($dom);
    $dom->getElementById('timer-result')->setContent(round($render->endRenderTimer(), 4));
    $render->appendHTMLFromDOM($dom);
}