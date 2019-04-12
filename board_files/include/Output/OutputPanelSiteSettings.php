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

    public function render(array $parameters = array(), bool $data_only = false)
    {
        $render_data = array();
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_site_config_access'))
        {
            nel_derp(360, _gettext('You are not allowed to access the site settings.'));
        }

        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $render_data['head'] = $output_head->render(['dotdot' => $dotdot]);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Site Settings')];
        $render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'extra_data' => $extra_data], true);
        $render_data['form_action'] = MAIN_SCRIPT . '?module=site-settings&action=update';
        $result = $this->database->query('SELECT * FROM "' . SITE_CONFIG_TABLE . '"');
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);
        unset($result);

        foreach ($rows as $config_line)
        {
            if ($config_line['data_type'] === 'boolean')
            {
                if ($config_line['setting'] == 1)
                {
                    $render_data[$config_line['config_name']] = 'checked';
                }
            }
            else
            {
                $render_data[$config_line['config_name']] = $config_line['setting'];
            }
        }

        $render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/site_settings_panel',
                $render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output($render_data, 'page', true);
        echo $output;
        return $output;
    }
}