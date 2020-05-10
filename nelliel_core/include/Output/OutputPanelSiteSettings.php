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

    function __construct(Domain $domain, bool $write_mode)
    {
        $this->domain = $domain;
        $this->write_mode = $write_mode;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        $user = $parameters['user'];

        if (!$user->checkPermission($this->domain, 'perm_site_config'))
        {
            nel_derp(360, _gettext('You are not allowed to access the site settings.'));
        }

        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Site Settings')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);
        $this->render_data['form_action'] = NEL_MAIN_SCRIPT . '?module=site-settings&action=update';
        $result = $this->database->query('SELECT * FROM "' . NEL_SITE_CONFIG_TABLE . '"');
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
                if ($config_line['select_type'] == 1)
                {
                    $this->render_data[$config_line['config_name'] . '_' . $config_line['setting']] = 'checked';
                }
                else
                {
                    $this->render_data[$config_line['config_name']] = $config_line['setting'];
                }
            }
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/site_settings_panel',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}