<?php

declare(strict_types=1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Admin\AdminBoardSettings;
use Nelliel\Auth\Authorization;
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
        $filetypes = new \Nelliel\FileTypes($this->database);
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $admin_board_settings = new AdminBoardSettings(new Authorization($this->database), $this->domain, $this->session, array());
        $defaults_list = $admin_board_settings->defaultsList();

        if ($defaults)
        {
            $table_name = NEL_BOARD_DEFAULTS_TABLE;
            $parameters['panel'] = $parameters['panel'] ?? _gettext('Board Default Settings');
            $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(['module' => 'admin', 'section' => 'board-settings', 'actions' => 'update']);
        }
        else
        {
            $table_name = $this->domain->reference('config_table');
            $parameters['panel'] = $parameters['panel'] ?? _gettext('Board Settings');
            $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'board-settings', 'actions' => 'update',
                                'board-id' => $this->domain->id()]);
        }

        $this->render_data['header'] = $output_header->manage($parameters, true);
        $user_lock_override = $this->session->user()->checkPermission($this->domain,
                'perm_manage_board_config_override');
        $all_filetypes = $filetypes->allTypeData();
        $all_types = $filetypes->types();
        $prepared = $this->database->prepare(
                'SELECT "setting_name","setting_value" FROM "' . $table_name . '" WHERE "setting_name" = ?');
        $enabled_types = $this->database->executePreparedFetch($prepared, ['enabled_filetypes'], PDO::FETCH_ASSOC);
        $enabled_array = json_decode($enabled_types['setting_value'], true);
        $types_edit_lock = $defaults_list['enabled_filetypes']['edit_lock'] == 1 && !$defaults && !$user_lock_override;
        $available_formats = $filetypes->availableFormats();

        foreach ($all_types as $type)
        {
            $type_data = array();
            $type_data['type_label'] = _gettext($type['type_label'] ?? '');
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
                $filetype_set[$filetype['base_extension']]['type_label'] = _gettext($filetype['type_label'] ?? '');
                $filetype_set[$filetype['base_extension']]['value'] = (array_key_exists($filetype['format'],
                        $enabled_formats)) ? 'checked' : '';
                $filetype_set[$filetype['base_extension']]['disabled'] = ($types_edit_lock) ? 'disabled' : '';
                $extensions = ' - ' . $filetype['base_extension'];

                if (!empty($filetype['sub_extensions']))
                {
                    foreach (json_decode($filetype['sub_extensions'], true) as $sub_extension)
                    {
                        $extensions .= ', ' . $sub_extension;
                    }
                }

                $filetype_set[$filetype['base_extension']]['type_label'] .= $extensions;
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
            $this->render_data['settings_data']['file_types'][] = $type_data;
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
            $setting_data['setting_description'] = $setting['setting_description'];
            $setting_options = json_decode($setting['setting_options'], true) ?? array();
            $input_attributes = json_decode($setting['input_attributes'], true) ?? array();

            if ($defaults_list[$setting['setting_name']]['edit_lock'] == 1)
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

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}