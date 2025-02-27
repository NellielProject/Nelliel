<?php
declare(strict_types = 1);

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
        $this->setBodyTemplate('account/login');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general([], true);
        $this->render_data['form_action'] = nel_build_router_url([Domain::SITE, 'account', 'login']);
        $this->render_data['register_url'] = nel_build_router_url([Domain::SITE, 'account', 'register']);

        if (nel_get_cached_domain(Domain::SITE)->setting('enable_captchas') && ($this->domain->setting('use_login_captcha'))) {
            $output_native_captchas = new OutputCAPTCHA($this->domain, $this->write_mode);
            $this->render_data['captchas'] = $output_native_captchas->render(['area' => 'user-login'], false);
        }
        $this->render_data['user_registration_enabled'] = $this->site_domain->setting('allow_user_registration');
        $this->render_data['super_sekrit_max_length'] = nel_crypt_config()->configValue('account_password_max_length');
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->general([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}