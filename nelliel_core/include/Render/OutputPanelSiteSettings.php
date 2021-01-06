<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelSiteSettings extends Output
{
    protected $render_data = array();

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->setupTimer($this->domain, $this->render_data);
        $this->render_data['page_language'] = $this->domain->locale();
        $this->setBodyTemplate('panels/site_settings');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Site Settings');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                'module=admin&section=site-settings&actions=update';
        $site_settings = $this->database->query(
                'SELECT * FROM "' . NEL_SETTINGS_TABLE . '" INNER JOIN "' . NEL_SITE_CONFIG_TABLE . '" ON "' .
                NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_SITE_CONFIG_TABLE .
                '"."setting_name" WHERE "setting_category" = \'core\'')->fetchAll(PDO::FETCH_ASSOC);

        foreach ($site_settings as $setting)
        {
            $setting_data = array();
            $setting_data['setting_name'] = $setting['setting_name'];
            $setting_data['setting_label'] = $setting['setting_label'];
            $setting_data['setting_description'] = $setting['setting_description'];
            $setting_options = json_decode($setting['setting_options'], true) ?? array();
            $input_attributes = json_decode($setting['input_attributes'], true) ?? array();

            foreach ($input_attributes as $attribute => $value)
            {
                $setting_data['input_attributes']['input_' . $attribute] = $value;
            }

            if ($setting['data_type'] === 'boolean')
            {
                if ($setting['setting_value'] == 1)
                {
                    $setting_data['setting_checked'] = 'checked';
                }
            }
            else
            {
                $type = $input_attributes['type'] ?? null;

                if ($type == 'radio' || $type == 'select')
                {
                    foreach ($setting_options as $option => $values)
                    {
                        $options = array();
                        $options['option_name'] = $option;
                        $options['option_label'] = $values['label'];
                        $options['option_key'] = $setting_data['setting_name'] . '_' . $option;

                        if ($setting['setting_value'] === $option)
                        {
                            if ($type == 'radio')
                            {
                                $options['option_checked'] = 'checked';
                            }
                            else if ($type == 'select')
                            {
                                $options['option_selected'] = 'selected';
                            }
                        }

                        $setting_data['options'][] = $options;
                    }
                }
                else
                {
                    $setting_data['setting_value'] = $setting['setting_value'];
                }
            }

            $this->render_data[$setting['setting_name']] = $setting_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}