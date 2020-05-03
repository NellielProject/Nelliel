<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;

class OutputRegisterPage extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        switch ($parameters['section'])
        {
            case 'register':
                $output = $this->registrationPage($parameters, $data_only);
                break;

            case 'registration-done':
                $output = $this->registrationDone($parameters, $data_only);
                break;
        }
    }

    private function registrationPage(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new OutputHeader($this->domain);
        $this->render_data['header'] = $output_header->render(['header_type' => 'general', 'dotdot' => $dotdot],
                true);
        $this->render_data['form_action'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                ['module' => 'account', 'action' => 'register']);
        $this->render_data['login_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                ['module' => 'account', 'action' => 'login']);
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('account/register', $this->render_data);
        $output_footer = new OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }

    private function registrationDone(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new OutputHeader($this->domain);
        $this->render_data['header'] = $output_header->render(['header_type' => 'general', 'dotdot' => $dotdot],
                true);
        $this->render_data['login_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                ['module' => 'account', 'action' => 'login']);
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('account/registration_complete', $this->render_data);
        $output_footer = new OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}