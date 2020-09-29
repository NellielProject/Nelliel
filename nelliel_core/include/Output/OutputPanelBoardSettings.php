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

        if (!$user->checkPermission($this->domain, 'perm_board_config'))
        {
            nel_derp(330, _gettext('You are not allowed to access the board settings.'));
        }

        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $defaults = $parameters['defaults'] ?? false;
        $dotdot = $parameters['dotdot'] ?? '';
        $this->startTimer();
        $filetypes = new \Nelliel\FileTypes($this->database);
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
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

        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);

        if ($defaults)
        {
            $table_name = NEL_BOARD_DEFAULTS_TABLE;
            $manage_headers = ['header' => _gettext('Board Management'),
                'sub_header' => _gettext('Default Board Settings')];
            $this->render_data['form_action'] = NEL_MAIN_SCRIPT . '?module=board-defaults&action=update';
        }
        else
        {
            $table_name = $this->domain->reference('config_table');
            $manage_headers = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Board Settings')];
            $this->render_data['form_action'] = NEL_MAIN_SCRIPT . '?module=board-settings&action=update&board_id=' .
                    $this->domain->id();
        }

        $all_filetypes = $filetypes->allTypeData();
        $all_types = $filetypes->types();
        $type_nodes = array();
        $filetype_entries_nodes = array();
        $type_row_count = array();
        $prepared = $this->database->prepare('SELECT "setting_value" FROM "' . $table_name . '" WHERE "setting_name" = ?');
        $enabled_json = $this->database->executePreparedFetch($prepared, ['enabled_filetypes'], PDO::FETCH_COLUMN);
        $enabled_array = json_decode($enabled_json, true);

        foreach ($all_types as $type)
        {
            $type_data = array();
            $type_data['label'] = _gettext($type['label']);
            $type_data['type_select']['name'] = $type['type'];
            $type_data['type_select']['input_name'] = 'filetypes[' . $type['type'] . '][enabled]';

            if (isset($enabled_array[$type['type']]))
            {
                $type_enabled = $enabled_array[$type['type']]['enabled'] ?? false;
            }
            else
            {
                $type_enabled = false;
            }

            $type_data['type_select']['value'] = ($type_enabled) ? 'checked' : '';
            $enabled_formats = $enabled_array[$type['type']]['formats'] ?? array();
            $entry_count = 0;
            $filetype_set = array();

            foreach ($all_filetypes as $filetype)
            {
                if ($filetype['type'] != $type['type'])
                {
                    continue;
                }

                if ($filetype['extension'] == $filetype['parent_extension'])
                {
                    $filetype_set[$filetype['parent_extension']]['format'] = $filetype['format'];
                    $filetype_set[$filetype['parent_extension']]['input_name'] = 'filetypes[' . $type['type'] . '][formats][' . $filetype['format'] . ']';
                    $filetype_set[$filetype['parent_extension']]['label'] = _gettext($filetype['label']) . ' - .' .
                            $filetype['extension'];
                    $filetype_set[$filetype['parent_extension']]['value'] = (array_key_exists($filetype['format'],
                            $enabled_formats)) ? 'checked' : '';
                }
                else
                {
                    $filetype_set[$filetype['parent_extension']]['label'] .= ', .' . $filetype['extension'];
                }
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

        $user_lock_override = $user->checkPermission($this->domain, 'perm_board_config_lock_override');
        $this->render_data['show_locked'] = $defaults;
        $board_settings = $this->database->query(
                'SELECT * FROM "' . NEL_SETTINGS_TABLE . '" INNER JOIN "' . $table_name . '" ON "' .
                NEL_SETTINGS_TABLE . '"."setting_name" = "' . $table_name .
                '"."setting_name" WHERE "setting_category" = \'board\'')->fetchAll(PDO::FETCH_ASSOC);

        foreach ($board_settings as $setting)
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
                if (isset($input_attributes['type']) && $input_attributes['type'] == 'radio')
                {
                    foreach ($setting_options as $option => $values)
                    {
                        $options = array();
                        $options['option_name'] = $option;
                        $options['option_label'] = $values['label'];
                        $options['option_key'] = $setting_data['setting_name'] . '_' . $option;

                        if ($setting['setting_value'] === $option)
                        {
                            $options['option_checked'] = 'checked';
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
        /*$result = $this->database->query('SELECT * FROM "' . $table_name . '"');
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $config_line)
        {
            $config_data = array();
            $config_data['display'] = true;
            $config_data['setting_name'] = $config_line['config_name'];

            if ($config_line['data_type'] == 'boolean')
            {
                if ($config_line['setting'] == 1)
                {
                    $config_data['value'] = 'checked';
                }
            }
            else
            {
                // 0 is field or checkbox; 1 is radio button; 2 is menu
                switch ($config_line['select_type'])
                {
                    case 0:
                        $config_data['value'] = $config_line['setting'];
                        break;

                    case 1:
                        $config_data[$config_line['config_name'] . '_' . $config_line['setting']] = 'checked';
                        break;

                    case 2:
                        $config_data[$config_line['config_name'] . '_' . $config_line['setting']] = 'selected';
                        break;
                }
            }

            if ($config_line['edit_lock'] == 1)
            {
                $config_data['locked'] = 'checked';

                if (!$user_lock_override)
                {
                    $config_data['disabled'] = 'disabled';
                }
            }

            $this->render_data[$config_line['config_name']] = $config_data;
        }*/

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/board_settings_panel',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}