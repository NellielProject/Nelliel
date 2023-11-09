<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\FileTypes;
use Nelliel\Domains\Domain;
use PDO;

class OutputPanelBoardConfig extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/board_config');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $defaults = $parameters['defaults'] ?? false;
        $filetypes = new FileTypes($this->database);
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $defaults_list = $this->defaultsList();

        if ($defaults) {
            $table_name = NEL_BOARD_DEFAULTS_TABLE;
            $parameters['panel'] = $parameters['panel'] ?? _gettext('Board Default Config');
            $this->render_data['form_action'] = nel_build_router_url([Domain::SITE, 'board-defaults', 'update']);
            $prepared = $this->database->prepare(
                'SELECT "setting_name","setting_value" FROM "' . $table_name . '" WHERE "setting_name" = ?');
            $enabled_types = $this->database->executePreparedFetch($prepared, ['enabled_filetypes'], PDO::FETCH_ASSOC);
        } else {
            $table_name = $this->domain->reference('config_table');
            $parameters['panel'] = $parameters['panel'] ?? _gettext('Board Config');
            $this->render_data['form_action'] = nel_build_router_url([$this->domain->uri(), 'config', 'update']);
            $prepared = $this->database->prepare(
                'SELECT "setting_name","setting_value" FROM "' . $table_name .
                '" WHERE "setting_name" = ? AND "board_id" = ?');
            $enabled_types = $this->database->executePreparedFetch($prepared, ['enabled_filetypes', $this->domain->id()],
                PDO::FETCH_ASSOC);
        }

        $this->render_data['header'] = $output_header->manage($parameters, true);
        $user_lock_override = $this->session->user()->checkPermission($this->domain, 'perm_manage_board_config_override');
        $user_raw_html = $this->session->user()->checkPermission($this->domain, 'perm_raw_html');
        $enabled_array = json_decode($enabled_types['setting_value'], true);
        $types_edit_lock = $defaults_list['enabled_filetypes']['edit_lock'] == 1 && !$defaults && !$user_lock_override;

        foreach ($filetypes->categories() as $category) {
            $category_data = $filetypes->categoryData($category);
            $category_output = array();
            $category_output['item_label'] = _gettext($category_data['label'] ?? '');
            $category_output['category_select']['name'] = $category_data['category'];
            $category_output['category_select']['input_name'] = 'enabled_filetypes[' . $category_data['category'] . ']';
            $category_output['category_max_size'] = intval(
                $filetypes->categorySetting($this->domain, $category, 'max_size'));

            if (isset($enabled_array[$category_data['category']]) &&
                $enabled_array[$category_data['category']]['enabled']) {
                $category_output['category_select']['checked'] = 'checked';
            }

            $category_output['category_select']['disabled'] = ($types_edit_lock) ? 'disabled' : '';
            $enabled_formats = $enabled_array[$category_data['category']] ?? array();
            $entry_row['entry'] = array();

            foreach ($filetypes->formats() as $format) {
                $format_data = $filetypes->formatData($format);

                if ($format_data['category'] !== $category_data['category']) {
                    continue;
                }

                $set = array();
                $set['input_name'] = 'enabled_filetypes[' . $format_data['category'] . '][formats][' . $format . ']';
                $set['item_label'] = _gettext($format_data['label'] ?? '');

                if (!empty($enabled_formats) && isset($enabled_formats['formats']) &&
                    array_search($format, $enabled_formats['formats']) !== false) {
                    $set['checked'] = 'checked';
                }

                $set['disabled'] = ($types_edit_lock) ? 'disabled' : '';
                $extensions = '';

                if (!empty($format_data['extensions'])) {
                    $extensions = ' - ';
                    foreach (json_decode($format_data['extensions'], true) as $extension) {
                        $extensions .= $extension . ', ';
                    }

                    $extensions = utf8_substr($extensions, 0, -2);
                }

                $set['item_label'] .= $extensions;

                if (count($entry_row['entry']) === 3) {
                    $category_output['entry_rows'][] = $entry_row;
                    $entry_row['entry'] = array();
                }

                $entry_row['entry'][] = $set;
            }

            $category_output['entry_rows'][] = $entry_row;
            $this->render_data['settings_data']['file_types'][] = $category_output;
        }

        $this->render_data['show_lock_update'] = $defaults;

        if ($defaults) {
            $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_SETTINGS_TABLE . '"
                LEFT JOIN "' . NEL_SETTING_OPTIONS_TABLE . '"
                ON "' . NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_SETTING_OPTIONS_TABLE .
                '"."setting_name"
                INNER JOIN "' . NEL_BOARD_DEFAULTS_TABLE . '"
                ON "' . NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_BOARD_DEFAULTS_TABLE .
                '"."setting_name"
                WHERE "' . NEL_SETTINGS_TABLE . '"."setting_category" = \'board\'');
            $board_settings = $this->database->executePreparedFetchAll($prepared, [], PDO::FETCH_ASSOC);
        } else {
            $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_SETTINGS_TABLE . '"
                LEFT JOIN "' . NEL_SETTING_OPTIONS_TABLE . '"
                ON "' . NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_SETTING_OPTIONS_TABLE .
                '"."setting_name"
                INNER JOIN "' . NEL_BOARD_CONFIGS_TABLE . '"
                ON "' . NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_BOARD_CONFIGS_TABLE .
                '"."setting_name"
                WHERE "' . NEL_BOARD_CONFIGS_TABLE . '"."board_id" = ? AND "' . NEL_SETTINGS_TABLE .
                '"."setting_category" = \'board\'');
            $board_settings = $this->database->executePreparedFetchAll($prepared, [$this->domain->id()],
                PDO::FETCH_ASSOC);
        }

        foreach ($board_settings as $setting) {
            $setting_data = array();
            $setting_data['setting_name'] = $setting['setting_name'];
            $setting_data['setting_description'] = _gettext($setting['setting_description']);
            $input_attributes = json_decode($setting['input_attributes'], true) ?? array();
            $setting_data['store_raw'] = $setting['raw_output'] == 1;
            $setting_data['show_raw'] = $user_raw_html;

            if ($defaults) {
                $setting_locked = $defaults_list[$setting['setting_name']]['edit_lock'] == 1;
                $setting_stored_raw = $defaults_list[$setting['setting_name']]['stored_raw'] == 1;
            } else {
                $setting_locked = $setting['edit_lock'] == 1;
                $setting_stored_raw = $setting['stored_raw'] == 1;
            }

            if ($setting_locked) {
                $setting_data['setting_locked'] = 'checked';

                if (!$defaults && !$user_lock_override) {
                    $setting_data['setting_disabled'] = 'disabled';
                }
            }

            if ($setting_stored_raw) {
                $setting_data['setting_stored_raw'] = 'checked';
            }

            if ($setting['setting_name'] === 'enabled_styles') {
                $styles_edit_lock = $setting_locked && !$defaults && !$user_lock_override;
                $styles = $this->domain->frontEndData()->getAllStyles(true);
                $styles_array = json_decode($setting['setting_value'] ?? '', true);
                $style_entries = array();

                foreach ($styles as $style) {
                    $style_id = $style->id();
                    $set = array();
                    $set['input_name'] = 'enabled_styles[' . $style_id . ']';
                    $set['item_label'] = $style->info('name');
                    $set['disabled'] = ($styles_edit_lock) ? 'disabled' : '';

                    if (in_array($style_id, $styles_array)) {
                        $set['checked'] = 'checked';
                    }

                    $style_entries['entry'][] = $set;
                }

                $this->render_data['settings_data']['styles'][] = $style_entries;
            }

            if ($setting['setting_name'] === 'enabled_content_ops') {
                $content_ops_edit_lock = $setting_locked && !$defaults && !$user_lock_override;
                $content_ops = $this->domain->frontEndData()->getAllContentOps(true);
                $content_ops_array = json_decode($setting['setting_value'] ?? '', true);
                $content_op_entries = array();

                foreach ($content_ops as $content_op) {
                    $op_id = $content_op->id();
                    $set = array();
                    $set['input_name'] = 'enabled_content_ops[' . $op_id . ']';
                    $set['item_label'] = $content_op->data('label');
                    $set['disabled'] = ($content_ops_edit_lock) ? 'disabled' : '';

                    if (in_array($op_id, $content_ops_array)) {
                        $set['checked'] = 'checked';
                    }

                    $content_op_entries['entry'][] = $set;
                }

                $this->render_data['settings_data']['content_ops'][] = $content_op_entries;
            }

            foreach ($input_attributes as $attribute => $value) {
                $setting_data['input_attributes']['input_' . $attribute] = $value;
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

        $output_menu = new OutputMenu($this->domain, false);
        $this->render_data['settings_data']['default_style']['options'] = $output_menu->configStyles(
            $this->render_data['settings_data']['default_style']['setting_value'] ?? '');
        $this->render_data['settings_data']['ui_image_set']['options'] = $output_menu->configImageSets(
            $this->render_data['settings_data']['ui_image_set']['setting_value'] ?? '');
        $this->render_data['settings_data']['filetype_image_set']['options'] = $output_menu->configImageSets(
            $this->render_data['settings_data']['filetype_image_set']['setting_value'] ?? '');
        $this->render_data['settings_data']['template_id']['options'] = $output_menu->configTemplates(
            $this->render_data['settings_data']['template_id']['setting_value'] ?? '');
        $this->render_data['settings_data']['time_zone']['options'] = $output_menu->timezones(
            $this->render_data['settings_data']['time_zone']['setting_value'] ?? '');
        $this->render_data['settings_data']['error_image_set']['options'] = $output_menu->configImageSets(
            $this->render_data['settings_data']['error_image_set']['setting_value'] ?? '');
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    private function defaultsList()
    {
        $defaults_data = $this->database->executeFetchAll('SELECT * FROM "' . NEL_BOARD_DEFAULTS_TABLE . '"',
            PDO::FETCH_ASSOC);
        $defaults = array();

        foreach ($defaults_data as $data) {
            $defaults[$data['setting_name']] = $data;
        }

        return $defaults;
    }
}