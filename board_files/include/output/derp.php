<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_derp($diagnostic, $board_id = '')
{
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $domain = new \Nelliel\Domain($board_id, new \Nelliel\CacheHandler(), nel_database());
    $translator = new \Nelliel\Language\Translator();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH . 'nelliel-default/');
    nel_render_general_header($render);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'derp.html');
    $dom->getElementById('error-id')->setContent($diagnostic['error_id']);
    $dom->getElementById('error-message')->setContent($diagnostic['error_message']);
    $dom->getElementById('error-data')->setContent(''); // TODO: This actually have something
    $session = new \Nelliel\Session($authorization);
    $url_constructor = new \Nelliel\URLConstructor();

        if ($session->inModmode($domain->id()))
        {
            $return_link = $url_constructor->dynamic(PHP_SELF,
                    ['manage' => 'true', 'module' => 'render', 'action' => 'view-index', 'section' => '0',
                        'board_id' => $domain->id(), 'modmode' => 'true']);
        }
        else
        {
            $return_link = $dom->getElementById('return-link')->extSetAttribute('href',
                    nel_parameters_and_data()->boardReferences($domain->id(), 'board_directory') . '/' . PHP_SELF2 . PHP_EXT);
        }

    $do_styles = ($domain->id() === '') ? false : true;
    $translator->translateDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain, null, $do_styles);
    echo $render->outputRenderSet();
}
