<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_board_footer($board_id, $render, $dotdot = null, $styles = true, $extra_links = false)
{
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'footer.html');
    $dotdot = (!empty($dotdot)) ? $dotdot : '';

    if (!$extra_links)
    {
        $dom->getElementById('bottom-extra-links')->removeSelf();
    }

    $dom->getElementById('nelliel-version')->setContent(NELLIEL_VERSION);
    $dom->getElementById('js-ui')->modifyAttribute('src', $dotdot, 'before');
    nel_language()->i18nDom($dom, nel_parameters_and_data()->boardSettings($board_id, 'board_language'));
    $dom->getElementById('timer-result')->setContent(round($render->endRenderTimer(), 4));
    $render->appendHTMLFromDOM($dom);
}

function nel_render_general_footer($render, $dotdot = null, $styles = false, $extra_links = false)
{
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'footer.html');
    $dotdot = (!empty($dotdot)) ? $dotdot : '';

    if (!$styles)
    {
        $dom->getElementById('bottom-styles-span')->removeSelf();
    }

    if (!$extra_links)
    {
        $dom->getElementById('bottom-extra-links')->removeSelf();
    }

    $dom->getElementById('nelliel-version')->setContent(NELLIEL_VERSION);
    $dom->getElementById('js-ui')->modifyAttribute('src', $dotdot, 'before');
    nel_language()->i18nDom($dom);
    $dom->getElementById('timer-result')->setContent(round($render->endRenderTimer(), 4));
    $render->appendHTMLFromDOM($dom);
}