<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelSiteConfig extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/site_config');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Site Config');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $user_raw_html = $this->session->user()->checkPermission($this->domain, 'perm_raw_html');
        $this->render_data['form_action'] = nel_build_router_url([Domain::SITE, 'config', 'update']);
        $colspan = 3;

        if ($user_raw_html) {
            $colspan ++;
        }

        $site_settings = $this->database->query(
            'SELECT * FROM "' . NEL_SETTINGS_TABLE . '"
            LEFT JOIN "' . NEL_SETTING_OPTIONS_TABLE . '"
            ON "' . NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_SETTING_OPTIONS_TABLE .
            '"."setting_name"
            INNER JOIN "' . NEL_SITE_CONFIG_TABLE . '"
            ON "' . NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_SITE_CONFIG_TABLE . '"."setting_name" WHERE "' .
            NEL_SETTINGS_TABLE . '"."setting_category" = \'site\'')->fetchAll(PDO::FETCH_ASSOC);

        foreach ($site_settings as $setting) {
            $setting_data = array();
            $setting_data['setting_name'] = $setting['setting_name'];
            $setting_data['setting_description'] = _gettext($setting['setting_description']);
            $setting_data['show_raw'] = $user_raw_html;
            $input_attributes = json_decode($setting['input_attributes'], true) ?? array();

            if ($setting['raw_output'] == 1) {
                $setting_data['store_raw'] = true;
            }

            foreach ($input_attributes as $attribute => $value) {
                $setting_data['input_attributes']['input_' . $attribute] = $value;
            }

            $setting_stored_raw = $setting['stored_raw'] == 1;

            if ($setting_stored_raw) {
                $setting_data['setting_stored_raw'] = 'checked';
            }

            if ($setting['data_type'] === 'boolean') {
                if ($setting['setting_value'] == 1) {
                    $setting_data['setting_checked'] = 'checked';
                }
            } else {
                $type = $input_attributes['type'] ?? null;

                if (($type == 'radio' || $type == 'select') && !nel_true_empty($setting['menu_data'])) {
                    $menu_data = json_decode($setting['menu_data'], true) ?? array();

                    foreach ($menu_data as $label => $value) {
                        $options = array();
                        $options['option_label'] = $label;
                        $options['option_value'] = $value;
                        $options['option_key'] = $setting_data['setting_name'] . '_' . $label;

                        if ($setting['setting_value'] === $value) {
                            if ($type == 'radio') {
                                $options['option_checked'] = 'checked';
                            } else if ($type == 'select') {
                                $options['option_selected'] = 'selected';
                            }
                        }

                        $setting_data['options'][] = $options;
                    }
                } else {
                    $setting_data['setting_value'] = $setting['setting_value'];
                }
            }

            $this->render_data['settings_data'][$setting['setting_name']] = $setting_data;
        }

        $this->render_data['show_raw_column'] = $user_raw_html;

        $output_menu = new OutputMenu($this->domain, false);
        $this->render_data['settings_data']['base_image_set']['options'] = $output_menu->configImageSets(
            $this->render_data['settings_data']['base_image_set']['setting_value'] ?? '');
        $this->render_data['settings_data']['template_id']['options'] = $output_menu->configTemplates(
            $this->render_data['settings_data']['template_id']['setting_value'] ?? '');
        $this->render_data['settings_data']['default_style']['options'] = $output_menu->configStyles(
            $this->render_data['settings_data']['default_style']['setting_value'] ?? '');
        $this->render_data['settings_data']['time_zone']['options'] = $output_menu->timezones(
            $this->render_data['settings_data']['time_zone']['setting_value'] ?? '');
        $this->render_data['settings_data']['error_image_set']['options'] = $output_menu->configImageSets(
            $this->render_data['settings_data']['error_image_set']['setting_value'] ?? '');
        $this->render_data['colspan'] = $colspan;
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}