<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_derp($diagnostic, $domain_id = '')
{
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $domain = new \Nelliel\Domain($domain_id, new \Nelliel\CacheHandler(), nel_database());
    $domain->renderInstance(new NellielTemplates\RenderCore());
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    nel_render_general_header($domain->renderInstance());
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'derp.html');
    $dom->getElementById('error-id')->setContent($diagnostic['error_id']);
    $dom->getElementById('error-message')->setContent($diagnostic['error_message']);
    $dom->getElementById('error-data')->setContent(''); // TODO: This actually have something
    $session = new \Nelliel\Session($authorization);
    $url_constructor = new \Nelliel\URLConstructor();

    if ($session->inModmode($domain->id()))
    {
        $return_link = $url_constructor->dynamic(PHP_SELF,
                ['module' => 'render', 'action' => 'view-index', 'section' => '0', 'board_id' => $domain->id(),
                    'modmode' => 'true']);
    }
    else
    {
        $return_link = $dom->getElementById('return-link')->extSetAttribute('href',
                nel_parameters_and_data()->boardReferences($domain->id(), 'board_directory') . '/' . PHP_SELF2 . PHP_EXT);
    }

    $do_styles = ($domain->id() === '') ? false : true;
    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain, null, $do_styles);
    echo $domain->renderInstance()->outputRenderSet();
}
