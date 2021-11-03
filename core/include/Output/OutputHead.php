<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

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
        $page_title = $parameters['page_title'] ?? $this->domain->reference('title');
        $this->render_data['main_js_file'] = NEL_SCRIPTS_WEB_PATH . 'core/nel.js';
        $this->render_data['js_ui_url'] = NEL_SCRIPTS_WEB_PATH . 'core/ui.js';
        $this->render_data['base_stylesheet'] = NEL_STYLES_WEB_PATH . 'core/base_style.css';
        $info = array();
        $info['domain_id'] = $this->domain->id();
        $info['src_directory'] = $this->domain->reference('source_directory');
        $info['preview_directory'] = $this->domain->reference('preview_directory');
        $info['page_directory'] = $this->domain->reference('page_directory');
        $info['is_modmode'] = $this->session->inModmode($this->domain);
        $this->render_data['js_domloaded'] = 'window.addEventListener(\'DOMContentLoaded\', (event) => {
            nelliel.setup.infoTransfer(' . json_encode($info) . ');
            nelliel.setup.doImportantStuff();});';
        $output_menu = new OutputMenu($this->domain, $this->write_mode);
        $this->render_data['stylesheets'] = $output_menu->styles([], true);

        if ($this->domain->setting('use_honeypot')) {
            $this->render_data['honeypot_css'] = '#form-user-info-1{display: none !important;}#form-user-info-2{display: none !important;}#form-user-info-3{position: absolute; top: 3px; left: -9001px;}';
            $this->render_data['use_honeypot'] = true;
        }

        $this->render_data['show_favicon'] = $this->domain->setting('show_favicon');
        $this->render_data['favicon_url'] = $this->domain->setting('favicon') ?? '';
        $this->render_data['page_title'] = $page_title;
        return $this->output('head', $data_only, true, $this->render_data);
    }
}