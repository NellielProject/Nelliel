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
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $this->render_core->startTimer();
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $this->render_core->appendToOutput($output_header->render(['header_type' => 'general', 'dotdot' => '']));
        $render_input['form_action'] = $this->url_constructor->dynamic(MAIN_SCRIPT, ['module' => 'login', 'action' => 'login']);
        $this->render_core->appendToOutput($this->render_core->renderFromTemplateFile('management/login', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_core->appendToOutput($output_footer->render(['dotdot' => '', 'generate_styles' => false]));
        echo $this->render_core->getOutput();
        nel_clean_exit();
    }
}