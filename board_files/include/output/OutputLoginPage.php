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
        $final_output = '';

        // Temp
        $this->render_instance = $this->domain->renderInstance();
        $this->render_instance->startRenderTimer();

        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $final_output .= $output_header->render(['header_type' => 'general', 'dotdot' => '']);
        $template_loader = new \Mustache_Loader_FilesystemLoader($this->domain->templatePath(), ['extension' => '.html']);
        $render_instance = new \Mustache_Engine(['loader' => $template_loader]);
        $template_loader->load('management/login');
        $render_input['form_action'] = $this->url_constructor->dynamic(MAIN_SCRIPT, ['module' => 'login', 'action' => 'login']);
        $this->render_instance->appendHTML($render_instance->render('management/login', $render_input));
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
    }
}