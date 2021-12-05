<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\FileTypes;
use Nelliel\Domains\Domain;
use PDO;

class OutputPanelBoardSettings extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/board_settings');
        $parameters['is_panel'] = true;
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $defaults = $parameters['defaults'] ?? false;
        $filetypes = new FileTypes($this->database);
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $defaults_list = $this->defaultsList();

        if ($defaults) {
            $table_name = NEL_BOARD_DEFAULTS_TABLE;
            $parameters['panel'] = $parameters['panel'] ?? _gettext('Board Default Settings');
            $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(['module' => 'admin', 'section' => 'board-defaults', 'actions' => 'update']);
            $prepared = $this->database->prepare(
                'SELECT "setting_name","setting_value" FROM "' . $table_name . '" WHERE "setting_name" = ?');
            $enabled_types = $this->database->executePreparedFetch($prepared, ['enabled_filetypes'], PDO::FETCH_ASSOC);
        } else {
            $table_name = $this->domain->reference('config_table');
            $parameters['panel'] = $parameters['panel'] ?? _gettext('Board Settings');
            $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                    ['module' => 'admin', 'section' => 'board-settings', 'actions' => 'update',
                        'board-id' => $this->domain->id()]);
            $prepared = $this->database->prepare(
                'SELECT "setting_name","setting_value" FROM "' . $table_name .
                '" WHERE "setting_name" = ? AND "board_id" = ?');
            $enabled_types = $this->database->executePreparedFetch($prepared, ['enabled_filetypes', $this->domain->id()],
                PDO::FETCH_ASSOC);
        }

        $this->render_data['header'] = $output_header->manage($parameters, true);
        $user_lock_override = $this->session->user()->checkPermission($this->domain, 'perm_manage_board_config_override');
        $formats_data = $filetypes->formatData();
        $enabled_array = json_decode($enabled_types['setting_value'], true);
        $types_edit_lock = $defaults_list['enabled_filetypes']['edit_lock'] == 1 && !$defaults && !$user_lock_override;

        foreach ($filetypes->categories() as $category) {
            $category_data = array();
            $category_data['item_label'] = _gettext($category['type_label'] ?? '');
            $category_data['category_select']['name'] = $category['category'];
            $category_data['category_select']['input_name'] = 'enabled_filetypes[' . $category['category'] . '][enabled]';

            if (isset($enabled_array[$category['category']]) && $enabled_array[$category['category']]['enabled']) {
                $category_data['category_select']['checked'] = 'checked';
            }

            $category_data['category_select']['disabled'] = ($types_edit_lock) ? 'disabled' : '';
            $enabled_formats = $enabled_array[$category['category']] ?? array();
            $entry_row['entry'] = array();

            foreach ($formats_data as $format => $data) {
                if ($data['category'] !== $category['category']) {
                    continue;
                }

                $set = array();
                $set['input_name'] = 'enabled_filetypes[' . $data['category'] . '][formats][' . $format . ']';
                $set['item_label'] = _gettext($data['type_label'] ?? '');

                if (!empty($enabled_formats) && isset($enabled_formats['formats']) &&
                    array_search($format, $enabled_formats['formats']) !== false) {
                    $set['checked'] = 'checked';
                }

                $set['disabled'] = ($types_edit_lock) ? 'disabled' : '';
                $extensions = '';

                if (!empty($data['extensions'])) {
                    $extensions = ' - ';

                    foreach (json_decode($data['extensions'], true) as $extension) {
                        $extensions .= $extension . ', ';
                    }

                    $extensions = utf8_substr($extensions, 0, -2);
                }

                $set['item_label'] .= $extensions;

                if (count($entry_row['entry']) === 3) {
                    $category_data['entry_rows'][] = $entry_row;
                    $entry_row['entry'] = array();
                }

                $entry_row['entry'][] = $set;
            }

            $category_data['entry_rows'][] = $entry_row;
            $this->render_data['settings_data']['file_types'][] = $category_data;
        }

        $this->render_data['show_lock_update'] = $defaults;

        if ($defaults) {
            $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_SETTINGS_TABLE . '" INNER JOIN "' . NEL_BOARD_DEFAULTS_TABLE . '" ON "' .
                NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_BOARD_DEFAULTS_TABLE . '"."setting_name" WHERE "' .
                NEL_SETTINGS_TABLE . '"."setting_category" = \'board\'');
            $board_settings = $this->database->executePreparedFetchAll($prepared, [], PDO::FETCH_ASSOC);
        } else {
            $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_SETTINGS_TABLE . '" INNER JOIN "' . NEL_BOARD_CONFIGS_TABLE . '" ON "' .
                NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_BOARD_CONFIGS_TABLE . '"."setting_name" WHERE "' .
                NEL_BOARD_CONFIGS_TABLE . '"."board_id" = ? AND "' . NEL_SETTINGS_TABLE .
                '"."setting_category" = \'board\'');
            $board_settings = $this->database->executePreparedFetchAll($prepared, [$this->domain->id()],
                PDO::FETCH_ASSOC);
        }

        foreach ($board_settings as $setting) {
            $setting_data = array();
            $setting_data['setting_name'] = $setting['setting_name'];
            $setting_data['setting_description'] = _gettext($setting['setting_description']);
            $setting_options = json_decode($setting['setting_options'], true) ?? array();
            $input_attributes = json_decode($setting['input_attributes'], true) ?? array();

            if ($defaults_list[$setting['setting_name']]['edit_lock'] == 1) {
                $setting_data['setting_locked'] = 'checked';

                if (!$defaults && !$user_lock_override) {
                    $setting_data['setting_disabled'] = 'disabled';
                }
            }

            if ($setting['setting_name'] === 'enabled_styles') {
                $styles_edit_lock = $defaults_list['enabled_styles']['edit_lock'] == 1 && !$defaults &&
                    !$user_lock_override;
                $styles = $this->domain->frontEndData()->getAllStyles();
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

            foreach ($input_attributes as $attribute => $value) {
                $setting_data['input_attributes']['input_' . $attribute] = $value;
            }

            if ($setting['data_type'] === 'boolean') {
                if ($setting['setting_value'] == 1) {
                    $setting_data['setting_checked'] = 'checked';
                }
            } else {
                $type = $input_attributes['type'] ?? null;

                if ($type == 'radio' || $type == 'select') {
                    foreach ($setting_options as $option => $values) {
                        $options = array();
                        $options['option_name'] = $option;
                        $options['option_label'] = $values['label'] ?? $options['option_name'];
                        $options['option_key'] = $setting_data['setting_name'] . '_' . $option;

                        if ($setting['setting_value'] === $option) {
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

        $this->render_data['settings_data']['default_style']['options'] = $this->stylesSelect(
            $this->render_data['settings_data']['default_style']['setting_value'] ?? '');
        $this->render_data['settings_data']['ui_image_set']['options'] = $this->imageSetsSelect(
            $this->render_data['settings_data']['ui_image_set']['setting_value'] ?? '');
        $this->render_data['settings_data']['filetype_image_set']['options'] = $this->imageSetsSelect(
            $this->render_data['settings_data']['filetype_image_set']['setting_value'] ?? '');
        $this->render_data['settings_data']['template_id']['options'] = $this->templatesSelect(
            $this->render_data['settings_data']['template_id']['setting_value'] ?? '');
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

        foreach ($styles as $style) {
            $option_data = array();
            $option_data['option_name'] = $style->id();
            $option_data['option_label'] = $style->info('name');

            if ($option_data['option_name'] === $selected) {
                $option_data['option_selected'] = 'selected';
            }

            $options[] = $option_data;
        }

        return $options;
    }

    private function imageSetsSelect(string $selected): array
    {
        $sets = $this->domain->frontEndData()->getAllImageSets();
        $options = array();

        foreach ($sets as $set) {
            $option_data = array();
            $option_data['option_name'] = $set->id();
            $option_data['option_label'] = $set->info('name');

            if ($option_data['option_name'] === $selected) {
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

        foreach ($templates as $template) {
            $option_data = array();
            $option_data['option_name'] = $template->id();
            $option_data['option_label'] = $template->info('name');

            if ($option_data['option_name'] === $selected) {
                $option_data['option_selected'] = 'selected';
            }

            $options[] = $option_data;
        }

        return $options;
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