<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputHead extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $session = new \Nelliel\Account\Session();
        $site_domain = new \Nelliel\DomainSite($this->database);
        $dotdot = ($parameters['dotdot']) ?? '';
        $this->render_data['main_js_file'] = $dotdot . ASSETS_CORE_WEB_PATH . 'scripts/nel.js';
        $this->render_data['js_ui_url'] = $dotdot . ASSETS_CORE_WEB_PATH . 'scripts/ui.js';
        $this->render_data['js_onload'] = 'window.onload = function () {nelliel.setup.doImportantStuff(\'' .
                $this->domain->id() . '\', \'' . $session->inModmode($this->domain) . '\');};';
        $this->render_data['js_set_style'] = 'setStyle(nelliel.core.getCookie("style-' . $this->domain->id() . '"));';
        $styles = $this->database->executeFetchAll(
                'SELECT * FROM "' . ASSETS_TABLE . '" WHERE "type" = \'style\' ORDER BY "entry", "is_default" DESC',
                PDO::FETCH_ASSOC);
        $style_set = array();
        $front_end_data = new \Nelliel\FrontEndData($this->database);

        foreach ($styles as $style)
        {
            $style_data = array();
            $info = json_decode($style['info'], true);
            $style_data['stylesheet'] = ($style['is_default']) ? 'stylesheet' : 'alternate stylesheet';
            $style_data['style_id'] = $style['id'];
            $web_path = ($front_end_data->styleIsCore($style['id'])) ? CORE_STYLES_WEB_PATH : CUSTOM_STYLES_WEB_PATH;
            $style_data['stylesheet_url'] = $dotdot . $web_path . $info['directory'] . '/' . $info['main_file'];
            $style_data['style_name'] = $info['name'];
            $this->render_data['stylesheets'][] = $style_data;
        }

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
                if ($site_domain->setting('show_favicon') && !empty($site_domain->setting('favicon')))
                {
                    $this->render_data['favicon_url'] = $site_domain->setting('favicon');
                    $this->render_data['show_favicon'] = true;
                }
            }
        }

        $this->render_data['page_title'] = $parameters['page_title'] ?? 'Nelliel Imageboard';
        return $this->output('head', $data_only, true);
    }
}