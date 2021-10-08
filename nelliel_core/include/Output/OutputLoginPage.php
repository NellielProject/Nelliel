<?php

declare(strict_types=1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;

class OutputLoginPage extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('account/login');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general([], true);
        $this->render_data['form_action'] = nel_build_router_url(['account', 'login']);;
        $this->render_data['register_url'] = nel_build_router_url(['account', 'register']);
        $this->render_data['use_login_captcha'] = $this->domain->setting('use_login_captcha');
        $this->render_data['captcha_gen_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=anti-spam&section=captcha&actions=get';
        $this->render_data['captcha_regen_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                'module=anti-spam&section=captcha&actions=generate&no-display';
        $this->render_data['use_login_recaptcha'] = $this->domain->setting('use_login_recaptcha');
        $this->render_data['recaptcha_sitekey'] = $this->site_domain->setting('recaptcha_site_key');
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}