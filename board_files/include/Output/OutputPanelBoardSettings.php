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

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $session = new \Nelliel\Account\Session();
        $user = $parameters['user'];
        $defaults = $parameters['defaults'] ?? false;
        $dotdot = $parameters['dotdot'] ?? '';
        $this->startTimer();
        $filetypes = new \Nelliel\FileTypes($this->database);
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new OutputHeader($this->domain);

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
            $table_name = BOARD_DEFAULTS_TABLE;
            $manage_headers = ['header' => _gettext('Board Management'),
                'sub_header' => _gettext('Default Board Settings')];
            $this->render_data['form_action'] = MAIN_SCRIPT . '?module=board-defaults&action=update';
        }
        else
        {
            $table_name = $this->domain->reference('config_table');
            $manage_headers = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Board Settings')];
            $this->render_data['form_action'] = MAIN_SCRIPT . '?module=board-settings&action=update&board_id=' .
                    $this->domain->id();
        }

        $all_filetypes = $filetypes->getFiletypeData();
        $all_categories = $filetypes->getFiletypeCategories();
        $category_nodes = array();
        $filetype_entries_nodes = array();
        $category_row_count = array();

        // TODO: Needs optimizing
        foreach ($all_categories as $category)
        {
            $category_data = array();
            $category_data['label'] = _gettext($category['label']);
            $category_data['category_select']['name'] = $category['type'];
            $prepared = $this->database->prepare(
                    'SELECT "setting" FROM "' . $table_name .
                    '" WHERE "config_type" = \'filetype_enable\' AND "config_name" = ?');
            $enabled = $this->database->executePreparedFetch($prepared, [$category['type']], PDO::FETCH_COLUMN);
            $category_data['category_select']['value'] = ($enabled == 1) ? 'checked' : '';
            $entry_count = 0;
            $filetype_set = array();

            foreach ($all_filetypes as $filetype)
            {
                if ($filetype['type'] != $category['type'])
                {
                    continue;
                }

                if ($filetype['extension'] == $filetype['parent_extension'])
                {
                    $filetype_set[$filetype['parent_extension']]['format'] = $filetype['format'];
                    $filetype_set[$filetype['parent_extension']]['label'] = _gettext($filetype['label']) . ' - .' .
                            $filetype['extension'];
                    $prepared = $this->database->prepare(
                            'SELECT "setting" FROM "' . $table_name .
                            '" WHERE "config_type" = \'filetype_enable\' AND "config_name" = ?');
                    $enabled = $this->database->executePreparedFetch($prepared, [$filetype['format']],
                            PDO::FETCH_COLUMN);
                    $filetype_set[$filetype['parent_extension']]['value'] = ($enabled == 1) ? 'checked' : '';
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
                    $category_data['entry_rows'][] = $entry_row;
                    $entry_row['entry'] = array();
                }

                $entry_row['entry'][] = $data;
            }

            $category_data['entry_rows'][] = $entry_row;
            $this->render_data['categories'][] = $category_data;
        }

        $user_lock_override = $user->checkPermission($this->domain, 'perm_board_config_lock_override');
        $this->render_data['defaults'] = $defaults;
        $result = $this->database->query('SELECT * FROM "' . $table_name . '"');
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $config_line)
        {
            $config_data = array('display' => true);

            if ($config_line['data_type'] == 'boolean')
            {
                if ($config_line['setting'] == 1)
                {
                    $config_data['value'] = 'checked';
                }
            }
            else
            {
                if ($config_line['select_type'] == 1)
                {
                    $config_data[$config_line['config_name'] . '_' . $config_line['setting']] = 'checked';
                }
                else
                {
                    $config_data['value'] = $config_line['setting'];
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
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile(
                'management/panels/board_settings_panel', $this->render_data);
        $output_footer = new OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}