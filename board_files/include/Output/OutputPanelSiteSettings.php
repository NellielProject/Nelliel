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

    public function render(array $parameters = array())
    {
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_site_config_access'))
        {
            nel_derp(360, _gettext('You are not allowed to access the site settings.'));
        }

        $this->render_core->startTimer();
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Site Settings')];
        $this->render_core->appendToOutput(
                $output_header->render(['header_type' => 'general', 'dotdot' => '', 'manage_render' => true, 'extra_data' => $extra_data]));
        $render_input['form_action'] = MAIN_SCRIPT . '?module=site-settings&action=update';
        $result = $this->database->query('SELECT * FROM "' . SITE_CONFIG_TABLE . '"');
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);
        unset($result);

        foreach ($rows as $config_line)
        {
            if ($config_line['data_type'] === 'boolean')
            {
                if ($config_line['setting'] == 1)
                {
                    $render_input[$config_line['config_name']] = 'checked';
                }
            }
            else
            {
                $render_input[$config_line['config_name']] = $config_line['setting'];
            }
        }

        $this->render_core->appendToOutput(
                $this->render_core->renderFromTemplateFile('management/panels/site_settings_panel', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_core->appendToOutput($output_footer->render(['dotdot' => '', 'generate_styles' => false]));
        echo $this->render_core->getOutput();
        nel_clean_exit();
    }
}