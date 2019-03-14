<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;

class OutputLoginPage extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $this->prepare('management/login.html');
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('Management Login')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $form_action = $this->url_constructor->dynamic(MAIN_SCRIPT, ['module' => 'login', 'action' => 'login']);
        $this->dom->getElementById('login-form')->extSetAttribute('action', $form_action);
        $this->domain->translator()->translateDom($this->dom, $this->domain->setting('language'));
        $this->domain->renderInstance()->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->domain->renderInstance()->outputRenderSet();
    }
}