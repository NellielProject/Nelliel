<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;

class OutputDerp extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    public function render(array $parameters = array())
    {
        $this->prepare('derp.html');
        $diagnostic = $parameters['diagnostic'];
        $authorization = new \Nelliel\Auth\Authorization(nel_database());
        nel_render_general_header($this->domain);
        $this->dom->getElementById('error-id')->setContent($diagnostic['error_id']);
        $this->dom->getElementById('error-message')->setContent($diagnostic['error_message']);
        $this->dom->getElementById('error-data')->setContent(''); // TODO: This actually have something
        $session = new \Nelliel\Session($authorization);
        $url_constructor = new \Nelliel\URLConstructor();

        if ($session->inModmode($this->domain))
        {
            if ($this->domain->id() === '')
            {
                ; // TODO: Figure out this one
            }
            else
            {
                $return_link = $url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'render', 'action' => 'view-index', 'index' => '0', 'board_id' => $this->domain->id(),
                        'modmode' => 'true']);
            }
        }
        else
        {
            if ($this->domain->id() === '')
            {
                $return_link = $this->dom->getElementById('return-link')->extSetAttribute('href',
                        BASE_WEB_PATH . $this->domain->setting('home_page'));
            }
            else
            {
                $return_link = $this->dom->getElementById('return-link')->extSetAttribute('href',
                        BASE_WEB_PATH . '/' . $this->domain->reference('board_directory') . '/');
            }
        }

        $this->domain->translator()->translateDom($this->dom, $this->domain->setting('language'));
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
    }

    public function renderSimple(array $diagnostic)
    {
        echo _gettext('oh god how did this get in here');
        echo '<br>';
        echo _gettext('Error ID: ') . $diagnostic['error_id'];
        echo '<br>';
        echo _gettext('Error Message: ') . $diagnostic['error_message'];
        die();
    }
}