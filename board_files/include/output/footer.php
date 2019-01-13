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
    $dom->getElementById('js-ui')->modifyAttribute('src', $dotdot, 'before');
    $translator->translateDom($dom, $domain->setting('language'));
    $dom->getElementById('timer-result')->setContent(round($domain->renderInstance()->endRenderTimer(), 4));
    $domain->renderInstance()->appendHTMLFromDOM($dom);
}

function nel_build_footer_styles($dom, $dotdot)
{
    $database = nel_database();
    $bottom_styles_nav = $dom->getElementById('bottom-styles');
    $styles = $database->executeFetchAll('SELECT * FROM "' . ASSETS_TABLE . '" WHERE "type" = \'style\' ORDER BY "entry", "is_default" DESC', PDO::FETCH_ASSOC);

    foreach ($styles as $style)
    {
        $info = json_decode($style['info'], true);
        $style_link = $dom->createElement('a');
        $style_link->extSetAttribute('href', '#');
        $style_link->extSetAttribute('data-command', 'change-style');
        $style_link->extSetAttribute('data-id', $style['id']);
        $style_link->setContent('[' . $info['name'] . ']');
        $bottom_styles_nav->appendChild($style_link);
        $bottom_styles_nav->appendChild($dom->createTextNode("\n"));
    }
}