<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_footer($render, $footer_form, $styles = true, $extra_links = false)
{
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'footer.html');

    if($styles)
    {
        $a_elements = $dom->getElementById('bottom-styles-span')->getElementsByTagName('a');

        foreach ($a_elements as $element)
        {
            $content = $element->getContent();
            $element->extSetAttribute('onclick', 'changeCSS(\'' . $content . '\', \'style-' . CONF_BOARD_DIR .
            '\'); return false;');
        }
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
