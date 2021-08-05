<?php

declare(strict_types=1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelFiletypes extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function main(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/filetypes_main');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Filetypes');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $filetypes = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_FILETYPES_TABLE . '" ORDER BY "is_category", "entry" ASC',
                PDO::FETCH_ASSOC);
        $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(['module' => 'admin', 'section' => 'filetypes', 'actions' => 'add']);
        $this->render_data['new_filetype_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(['module' => 'admin', 'section' => 'filetypes', 'actions' => 'new']);
        $bgclass = 'row1';

        foreach ($filetypes as $filetype)
        {
            $filetype_data = array();
            $filetype_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $filetype_data['base_extension'] = $filetype['base_extension'];
            $filetype_data['type'] = $filetype['type'];
            $filetype_data['format'] = $filetype['format'];
            $filetype_data['mime'] = $filetype['mime'];
            $sub_extensions = '';

            if (!empty($filetype['sub_extensions']))
            {
                foreach (json_decode($filetype['sub_extensions'], true) as $sub_extension)
                {
                    $sub_extensions .= $sub_extension . ' ';
                }
            }

            $filetype_data['sub_extensions'] = substr($sub_extensions, 0, -1);
            $filetype_data['mime'] = $filetype['mime'];
            $filetype_data['id_regex'] = $filetype['id_regex'];
            $filetype_data['type_label'] = $filetype['type_label'];
            $filetype_data['edit_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'filetypes', 'actions' => 'edit',
                                'filetype-id' => $filetype['entry']]);

            if ($filetype['enabled'] == 1)
            {
                $filetype_data['enable_disable_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                        http_build_query(
                                ['module' => 'admin', 'section' => 'filetypes', 'actions' => 'disable',
                                    'filetype-id' => $filetype['entry']]);
                $filetype_data['enable_disable_text'] = _gettext('Disable');
            }

            if ($filetype['enabled'] == 0)
            {
                $filetype_data['enable_disable_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                        http_build_query(
                                ['module' => 'admin', 'section' => 'filetypes', 'actions' => 'enable',
                                    'filetype-id' => $filetype['entry']]);
                $filetype_data['enable_disable_text'] = _gettext('Enable');
            }

            $filetype_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'filetypes', 'actions' => 'remove',
                                'filetype-id' => $filetype['entry']]);
            $this->render_data['filetype_list'][] = $filetype_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function new(array $parameters, bool $data_only)
    {
        $parameters['section'] = $parameters['section'] ?? _gettext('New');
        return $this->edit($parameters, $data_only);
    }

    public function edit(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/filetypes_edit');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Filetypes');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $editing = $parameters['editing'] ?? false;

        if ($editing)
        {
            $entry = $parameters['entry'] ?? 0;
            $form_action = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'filetypes', 'actions' => 'update',
                                'filetype-id' => $entry]);
            $prepared = $this->database->prepare('SELECT * FROM "' . NEL_FILETYPES_TABLE . '" WHERE "entry" = ?');
            $filetype_data = $this->database->executePreparedFetch($prepared, [$entry], PDO::FETCH_ASSOC);

            if ($filetype_data !== false)
            {
                $this->render_data['entry'] = $filetype_data['entry'];
                $this->render_data['base_extension'] = $filetype_data['base_extension'];
                $this->render_data['type'] = $filetype_data['type'];
                $this->render_data['format'] = $filetype_data['format'];
                $this->render_data['mime'] = $filetype_data['mime'];
                $sub_extensions = '';

                if (!empty($filetype_data['sub_extensions']))
                {
                    foreach (json_decode($filetype_data['sub_extensions'], true) as $sub_extension)
                    {
                        $sub_extensions .= $sub_extension . ' ';
                    }
                }

                $this->render_data['sub_extensions'] = substr($sub_extensions, 0, -1);
                $this->render_data['id_regex'] = $filetype_data['id_regex'];
                $this->render_data['type_label'] = $filetype_data['type_label'];
                $this->render_data['is_category_checked'] = $filetype_data['is_category'] == 1 ? 'checked' : '';
                $this->render_data['enabled_checked'] = $filetype_data['enabled'] == 1 ? 'checked' : '';
            }
        }
        else
        {
            $form_action = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(['module' => 'admin', 'section' => 'filetypes', 'actions' => 'update']);
        }

        $this->render_data['form_action'] = $form_action;
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}