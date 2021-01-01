<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;

class OutputAccount extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $session = new \Nelliel\Account\Session();
        $user = $session->sessionUser();
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $manage_headers = ['header' => _gettext('User Account'), 'sub_header' => _gettext('Main')];
        $this->render_data['header'] = $output_header->general(['manage_headers' => $manage_headers], true);
        $this->render_data['user_id'] = $user->id();
        $this->render_data['normal_user'] = true;
        $this->render_data['display_name'] = $user->auth_data['display_name'];
        $this->render_data['last_login'] = $user->auth_data['last_login'];
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('account/account_main',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}