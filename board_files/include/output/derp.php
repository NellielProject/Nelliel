<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_derp($diagnostic, $domain_id = '')
{
    $authorization = new \Nelliel\Auth\Authorization(nel_database());

    if($domain_id === '')
    {
        $domain = new \Nelliel\DomainSite(new \Nelliel\CacheHandler(), nel_database());
    }
    else
    {
        $domain = new \Nelliel\DomainBoard($domain_id, new \Nelliel\CacheHandler(), nel_database());
    }

    $domain->renderInstance(new \Nelliel\RenderCore());
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

    if ($session->inModmode($domain))
    {
        if ($domain->id() === '')
        {
            ; // TODO: Figure out this one
        }
        else
        {
            $return_link = $url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'render', 'action' => 'view-index', 'index' => '0', 'board_id' => $domain->id(),
                        'modmode' => 'true']);
        }
    }
    else
    {
        if ($domain->id() === '')
        {
            $return_link = $dom->getElementById('return-link')->extSetAttribute('href',
                    BASE_WEB_PATH . $domain->setting('home_page'));
        }
        else
        {
            $return_link = $dom->getElementById('return-link')->extSetAttribute('href',
                    BASE_WEB_PATH . '/' . $domain->reference('board_directory') . '/');
        }
    }

    $do_styles = ($domain->id() === '') ? false : true;
    $translator->translateDom($dom, $domain->setting('language'));
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain, null, $do_styles);
    echo $domain->renderInstance()->outputRenderSet();
}

function nel_render_simple_derp($diagnostic)
{
    echo _gettext('oh god how did this get in here');
    echo '<br>';
    echo _gettext('Error ID: ') . $diagnostic['error_id'];
    echo '<br>';
    echo _gettext('Error Message: ') . $diagnostic['error_message'];
    die();
}
