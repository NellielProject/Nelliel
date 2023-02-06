<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;

class OutputRegisterPage extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        switch ($parameters['section']) {
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
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('account/register');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general([], true);
        $this->render_data['form_action'] = nel_build_router_url([Domain::SITE, 'account', 'register']);
        $this->render_data['login_url'] = nel_build_router_url([Domain::SITE, 'account', 'login']);

        if (nel_site_domain()->setting('enable_captchas') && ($this->domain->setting('use_register_captcha'))) {
            $output_native_captchas = new OutputCAPTCHA($this->domain, $this->write_mode);
            $this->render_data['captchas'] = $output_native_captchas->render(['area' => 'user-register'], false);
        }

        $this->render_data['use_register_captcha'] = nel_site_domain()->setting('enable_captchas') &&
            $this->domain->setting('use_register_captcha');
        $this->render_data['captcha_gen_url'] = nel_build_router_url([Domain::SITE, 'captcha', 'get']);
        $this->render_data['captcha_regen_url'] = nel_build_router_url([Domain::SITE, 'captcha', 'regenerate']);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->general([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    private function registrationDone(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('account/registration_complete');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general([], true);
        $this->render_data['login_url'] = nel_build_router_url([Domain::SITE, 'account', 'login']);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->general([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}