<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;

class OutputLoginPage extends OutputCore
{

    function __construct(Domain $domain, bool $write_mode)
    {
        $this->domain = $domain;
        $this->write_mode = $write_mode;
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $this->startTimer();
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->render(['header_type' => 'general'], true);
        $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY .
                http_build_query(['module' => 'account', 'section' => 'login', 'actions' => 'submit']);
        $this->render_data['register_url'] = NEL_MAIN_SCRIPT_QUERY .
                http_build_query(['module' => 'account', 'section' => 'register']);
        $this->render_data['use_login_captcha'] = $this->domain->setting('use_login_captcha');
        $this->render_data['captcha_gen_url'] = NEL_MAIN_SCRIPT_WEB_PATH . '?module=captcha&actions=get';
        $this->render_data['captcha_regen_url'] = NEL_MAIN_SCRIPT_WEB_PATH .
                '?module=captcha&actions=generate&no-display';
        $this->render_data['use_login_recaptcha'] = $this->domain->setting('use_login_recaptcha');
        $this->render_data['recaptcha_sitekey'] = $this->site_domain->setting('recaptcha_site_key');
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('account/login', $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}