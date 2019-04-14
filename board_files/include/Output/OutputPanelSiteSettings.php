<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelSiteSettings extends OutputCore
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
        $user = $parameters['user'];
        
        if (!$user->domainPermission($this->domain, 'perm_site_config_access'))
        {
            nel_derp(360, _gettext('You are not allowed to access the site settings.'));
        }
        
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Site Settings')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);
        $this->render_data['form_action'] = MAIN_SCRIPT . '?module=site-settings&action=update';
        $result = $this->database->query('SELECT * FROM "' . SITE_CONFIG_TABLE . '"');
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);
        unset($result);
        
        foreach ($rows as $config_line)
        {
            if ($config_line['data_type'] === 'boolean')
            {
                if ($config_line['setting'] == 1)
                {
                    $this->render_data[$config_line['config_name']] = 'checked';
                }
            }
            else
            {
                $this->render_data[$config_line['config_name']] = $config_line['setting'];
            }
        }
        
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/site_settings_panel',
                $this->render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}