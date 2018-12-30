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
    nel_render_general_header($domain);
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'derp.html');
    $dom->getElementById('error-id')->setContent($diagnostic['error_id']);
    $dom->getElementById('error-message')->setContent($diagnostic['error_message']);
    $dom->getElementById('error-data')->setContent(''); // TODO: This actually have something
    $session = new \Nelliel\Session($authorization);
    $url_constructor = new \Nelliel\URLConstructor();
    $base_domain = pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);

    if ($session->inModmode($domain->id()))
    {
        if ($domain->id() === '')
        {
            ; // TODO: Figure out this one
        }
        else
        {
            $return_link = $url_constructor->dynamic(PHP_SELF,
                    ['module' => 'render', 'action' => 'view-index', 'section' => '0', 'board_id' => $domain->id(),
                        'modmode' => 'true']);
        }
    }
    else
    {
        if ($domain->id() === '')
        {
            $return_link = $dom->getElementById('return-link')->extSetAttribute('href',
                    $base_domain . $domain->setting('home_page'));
        }
        else
        {
            $return_link = $dom->getElementById('return-link')->extSetAttribute('href',
                    $base_domain . '/' . $domain->reference('board_directory') . '/');
        }
    }

    $do_styles = ($domain->id() === '') ? false : true;
    $translator->translateDom($dom, $domain->setting('language'));
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain, null, $do_styles);
    echo $domain->renderInstance()->outputRenderSet();
}
