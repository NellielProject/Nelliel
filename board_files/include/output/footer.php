<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_footer($render, $footer_form, $styles = true, $extra_links = false)
{
    $render1 = new NellielTemplates\RenderCore();
    $dom = $render1->newDOMDocument();
    $render1->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $render1->loadTemplateFromFile($dom, 'footer.html');
    $xpath = new DOMXPath($dom);
    $footer_form_element = $dom->getElementById('footer-form');

    if($footer_form)
    {
        $form_td_list = $xpath->query(".//input", $footer_form_element);

        if(nel_session_is_ignored('render'))
        {
            $dom->getElementById('admin-input-set1')->removeSelf();
            $dom->getElementById('bottom-submit-button')->setContent('FORM_SUBMIT');
            $dom->getElementById('bottom-pass-input')->removeSelf();
        }

        if (!BS_USE_NEW_IMGDEL)
        {
            $form_td_list->item(4)->removeSelf();
        }
    }
    else
    {
        $footer_form_element->removeSelf();
    }

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
    $timer_element = $dom->getElementById('footer-timer');

    if($render->get('output_timer'))
    {
        $dom->getElementById('timer-result')->setContent($render->get_timer(4));
    }
    else
    {
        $timer_element->removeSelf();
    }

    nel_process_i18n($dom);
    $render->appendOutput($render1->outputHTML($dom));
}
