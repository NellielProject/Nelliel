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
    private $database;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $user = $parameters['user'];
        $defaults = $parameters['defaults'];

        $this->render_core->startTimer();
        $filetypes = new \Nelliel\FileTypes($this->database);

        if ($defaults === true)
        {
            $table_name = BOARD_DEFAULTS_TABLE;
            $extra_data = ['header' => _gettext('Board Management'),
                'sub_header' => _gettext('Default Board Settings')];
            $render_input['form_action'] = MAIN_SCRIPT . '?module=board-defaults&action=update';
        }
        else
        {
            $table_name = $this->domain->reference('config_table');
            $extra_data = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Board Settings')];
            $render_input['form_action'] = MAIN_SCRIPT . '?module=board-settings&action=update&board_id=' .
                    $this->domain->id();
        }

        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $this->render_core->appendToOutput(
                $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]));

        $all_filetypes = $filetypes->getFiletypeData();
        $all_categories = $filetypes->getFiletypeCategories();
        $category_nodes = array();
        $filetype_entries_nodes = array();
        $category_row_count = array();

        foreach ($all_categories as $category)
        {
            $category_data = array();
            $category_data['label'] = $category['label'];
            $category_data['category_select']['label'] = 'Allow ' . $category['type'];
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
                    $filetype_set[$filetype['parent_extension']]['label'] = $filetype['label'] . ' - .' .
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
            $render_input['categories'][] = $category_data;
        }

        $user_lock_override = $user->domainPermission($this->domain, 'perm_board_config_lock_override');
        $render_input['defaults'] = $defaults;
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

            $render_input[$config_line['config_name']] = $config_data;
        }

        $this->render_core->appendToOutput(
                $this->render_core->renderFromTemplateFile('management/panels/board_settings_panel', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_core->appendToOutput($output_footer->render(['dotdot' => '', 'generate_styles' => false]));
        echo $this->render_core->getOutput();
        nel_clean_exit();
    }
}