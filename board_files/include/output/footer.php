<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_general_footer($render, $board_id = null, $dotdot = null, $styles = false, $extra_links = false)
{
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $translator = new \Nelliel\Language\Translator();
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'footer.html');
    $dotdot = (!empty($dotdot)) ? $dotdot : '';
    $is_board = !is_null($board_id);

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
    $locale = ($is_board) ? nel_parameters_and_data()->boardSettings($board_id, 'board_language') : DEFAULT_LOCALE;
    $translator->translateDom($dom, $locale);
    $dom->getElementById('timer-result')->setContent(round($render->endRenderTimer(), 4));
    $render->appendHTMLFromDOM($dom);
}