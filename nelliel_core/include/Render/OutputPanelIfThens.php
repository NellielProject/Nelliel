<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelIfThens extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function main(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/if_thens_main');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('File Filters');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);

        if ($this->domain->id() === Domain::SITE)
        {
            $ifthens = $this->database->executeFetchAll(
                    'SELECT * FROM "' . NEL_IF_THENS_TABLE . '" ORDER BY "entry" DESC', PDO::FETCH_ASSOC);
        }
        else
        {
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . NEL_IF_THENS_TABLE . '" WHERE "board_id" = ? ORDER BY "entry" DESC');
            $ifthens = $this->database->executePreparedFetchAll($prepared, [$this->domain->id()], PDO::FETCH_ASSOC);
        }

        $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(['module' => 'admin', 'section' => 'file-filters', 'actions' => 'add']);
        $this->render_data['new_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(['module' => 'admin', 'section' => 'ifthens', 'actions' => 'new']);
        $bgclass = 'row1';

        foreach ($ifthens as $ifthen)
        {
            $ifthen_data = array();
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $ifthen_data['bgclass'] = $bgclass;
            $ifthen_data['entry'] = $ifthen['entry'];
            $ifthen_data['board_id'] = $ifthen['board_id'];
            $ifthen_data['if_conditions'] = $ifthen['if_conditions'];
            $ifthen_data['then_actions'] = $ifthen['then_actions'];
            $ifthen_data['notes'] = $ifthen['notes'];
            $ifthen_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'ifthens', 'actions' => 'remove',
                                'ifthen-id' => $ifthen['entry']]);
            $ifthen_data['edit_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'ifthens', 'actions' => 'edit',
                                'ifthen-id' => $ifthen['entry']]);

            if ($ifthen['enabled'] == 1)
            {
                $ifthen_data['enable_disable_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                        http_build_query(
                                ['module' => 'admin', 'section' => 'ifthens', 'actions' => 'disable',
                                    'ifthen-id' => $ifthen['entry']]);
                $ifthen_data['enable_disable_text'] = _gettext('Disable');
            }

            if ($ifthen['enabled'] == 0)
            {
                $ifthen_data['enable_disable_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                        http_build_query(
                                ['module' => 'admin', 'section' => 'ifthens', 'actions' => 'enable',
                                    'ifthen-id' => $ifthen['entry']]);
                $ifthen_data['enable_disable_text'] = _gettext('Enable');
            }

            $ifthen_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'ifthens', 'actions' => 'remove',
                                'ifthen-id' => $ifthen['entry']]);
            $this->render_data['ifthen_list'][] = $ifthen_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function new(array $parameters, bool $data_only)
    {
        $parameters['section'] = $parameters['section'] ?? _gettext('New');
        $parameters['submit_add'] = true;
        return $this->edit($parameters, $data_only);
    }

    public function edit(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/if_thens_edit');
        $editing = $parameters['editing'] ?? false;
        $this->render_data['submit_add'] = $parameters['submit_add'] ?? false;
        $this->render_data['submit_edit'] = $editing;
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('If-Thens');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);

        $form_action = '';

        if ($editing)
        {
            $entry = $parameters['entry'] ?? 0;
            $form_action = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'ifthens', 'actions' => 'update', 'ifthen-id' => $entry]);
            $prepared = $this->database->prepare('SELECT * FROM "' . NEL_IF_THENS_TABLE . '" WHERE "entry" = ?');
            $ifthen_data = $this->database->executePreparedFetch($prepared, [$entry], PDO::FETCH_ASSOC);

            if ($ifthen_data !== false)
            {
                $this->render_data['entry'] = $ifthen_data['entry'];
                $this->render_data['board_id'] = $ifthen_data['board_id'];
                $this->render_data['if_conditions'] = $ifthen_data['if_conditions'];
                $this->render_data['then_actions'] = $ifthen_data['then_actions'];
                $this->render_data['notes'] = $ifthen_data['notes'];
                $this->render_data['enabled_checked'] = $ifthen_data['enabled'] == 1 ? 'checked' : '';
            }
        }
        else
        {
            $form_action = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(['module' => 'admin', 'section' => 'ifthens', 'actions' => 'add']);
        }

        $this->render_data['form_action'] = $form_action;
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}