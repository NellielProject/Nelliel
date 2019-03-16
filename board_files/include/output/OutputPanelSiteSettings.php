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
    private $database;

    function __construct(Domain $domain)
    {
        $this->database = $domain->database();
        $this->domain = $domain;
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_site_config_access'))
        {
            nel_derp(360, _gettext('You are not allowed to access the site settings.'));
        }

        $this->prepare('management/panels/site_settings_panel.html');
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Site Settings')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $this->dom->getElementById('site-settings-form')->extSetAttribute('action',
                MAIN_SCRIPT . '?module=site-settings&action=update');
        $result = $this->database->query('SELECT * FROM "' . SITE_CONFIG_TABLE . '"');
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);
        unset($result);

        foreach ($rows as $config_line)
        {
            $config_element = $this->dom->getElementById($config_line['config_name']);

            if (is_null($config_element))
            {
                continue;
            }

            if ($config_line['data_type'] === 'boolean')
            {
                if ($config_line['setting'] == 1)
                {
                    $config_element->extSetAttribute('checked', 'true');
                }
            }
            else
            {
                $config_element->extSetAttribute('value', $config_line['setting']);
            }
        }

        $this->domain->translator()->translateDom($this->dom);
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }
}