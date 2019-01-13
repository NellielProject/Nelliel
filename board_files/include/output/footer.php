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
    else
    {
        nel_build_footer_styles($dom, $dotdot);
    }

    $dom->getElementById('nelliel-version')->setContent(NELLIEL_VERSION);
    $dom->getElementById('js-ui')->extSetAttribute('src', $dotdot . SCRIPT_WEB_PATH . 'ui.js');
    $translator->translateDom($dom, $domain->setting('language'));

    if($domain->setting('display_render_timer'))
    {
        $timer_out = sprintf(_gettext('This page was created in %s seconds.'), round($domain->renderInstance()->endRenderTimer(), 4));
        $dom->getElementById('footer-timer')->setContent($timer_out);
    }

    $domain->renderInstance()->appendHTMLFromDOM($dom);
}

function nel_build_footer_styles($dom, $dotdot)
{
    $database = nel_database();
    $bottom_styles_menu = $dom->getElementById('bottom-styles-menu');
    $styles = $database->executeFetchAll('SELECT * FROM "' . ASSETS_TABLE . '" WHERE "type" = \'style\' ORDER BY "entry", "is_default" DESC', PDO::FETCH_ASSOC);

    foreach ($styles as $style)
    {
        $info = json_decode($style['info'], true);
        $style_option = $dom->createElement('option', $info['name']);
        $style_option->extSetAttribute('data-command', 'change-style');
        $style_option->extSetAttribute('data-id', $style['id']);
        $style_option->extSetAttribute('value', $style['id']);
        $bottom_styles_menu->appendChild($style_option);
    }
}