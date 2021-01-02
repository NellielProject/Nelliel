<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;

class OutputHead extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $session = new \Nelliel\Account\Session();
        $this->render_data['main_js_file'] = NEL_SCRIPTS_WEB_PATH . 'core/nel.js';
        $this->render_data['js_ui_url'] = NEL_SCRIPTS_WEB_PATH . 'core/ui.js';
        $this->render_data['js_onload'] = 'window.onload = function () {nelliel.setup.doImportantStuff(\'' .
                $this->domain->id() . '\', \'' . $session->inModmode($this->domain) . '\');};';
        $this->render_data['js_set_style'] = 'setStyle(nelliel.core.getCookie("style-' . $this->domain->id() . '"));';
        $output_menu = new OutputMenu($this->domain, $this->write_mode);
        $this->render_data['stylesheets'] = $output_menu->styles([], true);

        if ($this->domain->setting('use_honeypot'))
        {
            $this->render_data['honeypot_css'] = '#form-user-info-1{display: none !important;}#form-user-info-2{display: none !important;}#form-user-info-3{position: absolute; top: 3px; left: -9001px;}';
            $this->render_data['use_honeypot'] = true;
        }

        $this->render_data['show_favicon'] = false;

        if ($this->domain->setting('show_favicon'))
        {
            if (!empty($this->domain->setting('favicon')))
            {
                $this->render_data['favicon_url'] = $this->domain->setting('favicon');
                $this->render_data['show_favicon'] = true;
            }
            else
            {
                if ($this->site_domain->setting('show_favicon') && !empty($this->site_domain->setting('favicon')))
                {
                    $this->render_data['favicon_url'] = $this->site_domain->setting('favicon');
                    $this->render_data['show_favicon'] = true;
                }
            }
        }

        $this->render_data['page_title'] = $parameters['page_title'] ?? 'Nelliel Imageboard';
        return $this->output('head', $data_only, true);
    }
}