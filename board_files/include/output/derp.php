<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_derp($diagnostic, $board_id = null)
{
    $language = new \Nelliel\language\Language(nel_authorize());
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'derp.html');
    $dom->getElementById('error-id')->setContent($diagnostic['error_id']);
    $dom->getElementById('error-message')->setContent($diagnostic['error_message']);
    $dom->getElementById('error-data')->setContent(''); // TODO: This actually have something
    $session = new \Nelliel\Sessions(nel_authorize());
    $url_constructor = new \Nelliel\URLConstructor();

    if (!is_null($board_id))
    {
        if($session->inModmode($board_id))
        {
            $return_link = $url_constructor->dynamic(PHP_SELF, ['module' => 'render', 'action' => 'view-index', 'section' => '0', 'board_id' => $board_id, 'modmode' => 'true']);
        }
        else
        {
            $return_link = $dom->getElementById('return-link')->extSetAttribute('href',
                    nel_parameters_and_data()->boardReferences($board_id, 'board_directory') . '/' . PHP_SELF2 . PHP_EXT);
        }
    }

    $do_styles = (is_null($board_id)) ? false : true;
    $language->i18nDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render, $board_id, null, $do_styles);
    echo $render->outputRenderSet();
}
