<?php

declare(strict_types=1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelSiteSettings extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
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
                '"."setting_name" WHERE "setting_category" = \'site\'')->fetchAll(PDO::FETCH_ASSOC);

        foreach ($site_settings as $setting)
        {
            $setting_data = array();
            $setting_data['setting_name'] = $setting['setting_name'];
            $setting_data['setting_description'] = _gettext($setting['setting_description']);
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
                        $options['option_label'] = $values['label'] ?? $options['option_name'];
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

            $this->render_data['settings_data'][$setting['setting_name']] = $setting_data;
        }

        $this->render_data['settings_data']['base_icon_set']['options'] = $this->iconSetsSelect($this->domain->setting('base_icon_set'));
        $this->render_data['settings_data']['template_id']['options'] = $this->templatesSelect($this->domain->setting('template_id'));
        $this->render_data['settings_data']['default_style']['options'] = $this->stylesSelect($this->domain->setting('default_style'));
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    private function stylesSelect(string $selected): array
    {
        $styles = $this->domain->frontEndData()->getAllStyles();
        $options = array();

        foreach ($styles as $style)
        {
            $option_data = array();
            $option_data['option_name'] = $style->id();
            $option_data['option_label'] = $style->info('name');

            if ($option_data['option_name'] === $selected)
            {
                $option_data['option_selected'] = 'selected';
            }

            $options[] = $option_data;
        }

        return $options;
    }

    private function iconSetsSelect(string $selected): array
    {
        $sets = $this->domain->frontEndData()->getAllIconSets();
        $options = array();

        foreach ($sets as $set)
        {
            $option_data = array();
            $option_data['option_name'] = $set->id();
            $option_data['option_label'] = $set->info('name');

            if ($option_data['option_name'] === $selected)
            {
                $option_data['option_selected'] = 'selected';
            }

            $options[] = $option_data;
        }

        return $options;
    }

    private function templatesSelect(string $selected): array
    {
        $templates = $this->domain->frontEndData()->getAllTemplates();
        $options = array();

        foreach ($templates as $template)
        {
            $option_data = array();
            $option_data['option_name'] = $template->id();
            $option_data['option_label'] = $template->info('name');

            if ($option_data['option_name'] === $selected)
            {
                $option_data['option_selected'] = 'selected';
            }

            $options[] = $option_data;
        }

        return $options;
    }
}