<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelBoardSettings extends OutputCore
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
        $this->renderSetup();
        $defaults = $parameters['defaults'] ?? false;
        $filetypes = new \Nelliel\FileTypes($this->database);
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);

        if ($defaults)
        {
            $manage_headers = ['header' => _gettext('General Management'),
                'sub_header' => _gettext('Board Default Settings')];
        }
        else
        {
            $manage_headers = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Board Settings')];
        }

        $this->render_data['header'] = $output_header->general(['manage_headers' => $manage_headers], true);

        if ($defaults)
        {
            $table_name = NEL_BOARD_DEFAULTS_TABLE;
            $manage_headers = ['header' => _gettext('Board Management'),
                'sub_header' => _gettext('Default Board Settings')];
            $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=board-defaults&actions=update';
        }
        else
        {
            $table_name = $this->domain->reference('config_table');
            $manage_headers = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Board Settings')];
            $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    'module=admin&section=board-settings&actions=update&board_id=' . $this->domain->id();
        }

        $user_lock_override = $user->checkPermission($this->domain, 'perm_board_config_lock_override');
        $all_filetypes = $filetypes->allTypeData();
        $all_types = $filetypes->types();
        $type_nodes = array();
        $filetype_entries_nodes = array();
        $type_row_count = array();
        $prepared = $this->database->prepare(
                'SELECT "setting_name","setting_value","edit_lock" FROM "' . $table_name . '" WHERE "setting_name" = ?');
        $enabled_types = $this->database->executePreparedFetch($prepared, ['enabled_filetypes'], PDO::FETCH_ASSOC);
        $enabled_array = json_decode($enabled_types['setting_value'], true);
        $types_edit_lock = $enabled_types['edit_lock'] == 1 && !$defaults && !$user_lock_override;
        $available_formats = $filetypes->availableFormats();

        foreach ($all_types as $type)
        {
            $type_data = array();
            $type_data['label'] = _gettext($type['label']);
            $type_data['type_select']['name'] = $type['type'];
            $type_data['type_select']['input_name'] = 'enabled_filetypes[types][' . $type['type'] . '][enabled]';

            if (isset($enabled_array[$type['type']]))
            {
                $type_enabled = $enabled_array[$type['type']]['enabled'] ?? false;
            }
            else
            {
                $type_enabled = false;
            }

            $type_data['type_select']['value'] = ($type_enabled) ? 'checked' : '';
            $type_data['type_select']['disabled'] = ($types_edit_lock) ? 'disabled' : '';
            $enabled_formats = $enabled_array[$type['type']]['formats'] ?? array();
            $entry_count = 0;
            $filetype_set = array();

            foreach ($all_filetypes as $filetype)
            {
                if ($filetype['type'] != $type['type'] || !isset($available_formats[$filetype['format']]))
                {
                    continue;
                }

                $filetype_set[$filetype['base_extension']]['format'] = $filetype['format'];
                $filetype_set[$filetype['base_extension']]['input_name'] = 'enabled_filetypes[types][' . $type['type'] .
                        '][formats][' . $filetype['format'] . ']';
                $filetype_set[$filetype['base_extension']]['label'] = _gettext($filetype['label']);
                $filetype_set[$filetype['base_extension']]['value'] = (array_key_exists($filetype['format'],
                        $enabled_formats)) ? 'checked' : '';
                $filetype_set[$filetype['base_extension']]['disabled'] = ($types_edit_lock) ? 'disabled' : '';
                $sub_extensions = ' - ';

                if (!empty($filetype['sub_extensions']))
                {
                    foreach (json_decode($filetype['sub_extensions'], true) as $sub_extension)
                    {
                        $sub_extensions .= $sub_extension . ', ';
                    }
                }

                $filetype_set[$filetype['base_extension']]['label'] .= substr($sub_extensions, 0, -2);
            }

            $entry_row['entry'] = array();

            foreach ($filetype_set as $data)
            {
                if (count($entry_row['entry']) >= 4)
                {
                    $type_data['entry_rows'][] = $entry_row;
                    $entry_row['entry'] = array();
                }

                $entry_row['entry'][] = $data;
            }

            $type_data['entry_rows'][] = $entry_row;
            $this->render_data['file_types'][] = $type_data;
        }

        $this->render_data['show_lock_update'] = $defaults;
        $board_settings = $this->database->query(
                'SELECT * FROM "' . NEL_SETTINGS_TABLE . '" INNER JOIN "' . $table_name . '" ON "' . NEL_SETTINGS_TABLE .
                '"."setting_name" = "' . $table_name . '"."setting_name" WHERE "setting_category" = \'board\'')->fetchAll(
                PDO::FETCH_ASSOC);

        foreach ($board_settings as $setting)
        {
            $setting_data = array();
            $setting_data['setting_name'] = $setting['setting_name'];
            $setting_data['setting_label'] = $setting['setting_label'];
            $setting_data['setting_description'] = $setting['setting_description'];
            $setting_options = json_decode($setting['setting_options'], true) ?? array();
            $input_attributes = json_decode($setting['input_attributes'], true) ?? array();

            if ($setting['edit_lock'] == 1)
            {
                $setting_data['setting_locked'] = 'checked';

                if (!$defaults && !$user_lock_override)
                {
                    $setting_data['setting_disabled'] = 'disabled';
                }
            }

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

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/board_settings_panel',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}