<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

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
        $this->render_data['site_referrer_policy'] = $this->site_domain->setting('site_referrer_policy');
        $this->scripts();
        $this->render_data['base_stylesheet'] = NEL_STYLES_WEB_PATH . 'core/base_style.css';
        $this->render_data['support_stylesheet'] = NEL_STYLES_WEB_PATH . 'core/support.css';
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
        $this->render_data['show_favicon'] = $this->domain->setting('show_favicon') && !nel_true_empty($this->domain->setting('favicon'));
        $this->render_data['favicon_url'] = $this->domain->setting('favicon');
        $this->render_data['page_title'] = $page_title;
        return $this->output('head', $data_only, true, $this->render_data);
    }

    private function scripts(): void
    {
        $scripts = $this->database->executeFetchAll('SELECT * FROM "' . NEL_SCRIPTS_TABLE . '"', PDO::FETCH_ASSOC);
        $script_list = array();

        foreach ($scripts as $script) {
            if ($script['enabled'] != 1) {
                continue;
            }

            $location = $script['location'];

            if ($script['full_url'] != 1) {
                $location = NEL_SCRIPTS_WEB_PATH . $location;
            }

            $script_list[]['location'] = $location;
        }

        $this->render_data['scripts'] = $script_list;
    }
}