<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelManageBoards extends OutputCore
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
        if (!isset($parameters['section']))
        {
            return;
        }

        $user = $parameters['user'];

        if (!$user->checkPermission($this->domain, 'perm_manage_boards'))
        {
            nel_derp(370, _gettext('You are not allowed to manage boards.'));
        }

        switch ($parameters['section'])
        {
            case 'panel':
                $output = $this->panel($parameters, $data_only);
                break;

            case 'remove_interstitial':
                $output = $this->removeInterstitial($parameters, $data_only);
                break;
        }

        return $output;
    }

    private function panel(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $this->startTimer();
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Manage Boards')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'manage_headers' => $manage_headers], true);
        $this->render_data['form_action'] = NEL_MAIN_SCRIPT .
                '?module=admin&section=manage-boards&actions=add&domain=_site_';
        $board_data = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_BOARD_DATA_TABLE . '" ORDER BY "board_id" DESC', PDO::FETCH_ASSOC);
        $bgclass = 'row1';

        foreach ($board_data as $board_info)
        {
            $board_data = array();
            $board_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $board_data['board_id'] = $board_info['board_id'];
            $board_data['board_uri'] = $board_info['board_uri'];
            $board_data['db_prefix'] = $board_info['db_prefix'];

            if ($board_info['locked'] == 0)
            {
                $board_data['lock_url'] = NEL_MAIN_SCRIPT_QUERY .
                        http_build_query(
                                ['module' => 'admin', 'section' => 'manage-boards',
                                    'board_id' => $board_info['board_id'], 'actions' => 'lock', 'domain_id' => '_site_']);
                $board_data['status'] = _gettext('Active');
                $board_data['lock_text'] = _gettext('Lock Board');
            }
            else
            {
                $board_data['lock_url'] = NEL_MAIN_SCRIPT_QUERY .
                        http_build_query(
                                ['module' => 'admin', 'section' => 'manage-boards',
                                    'board_id' => $board_info['board_id'], 'actions' => 'unlock', 'domain_id' => '_site_']);
                $board_data['status'] = _gettext('Locked');
                $board_data['lock_text'] = _gettext('Unlock Board');
            }

            $board_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'manage-boards', 'board_id' => $board_info['board_id'],
                                'actions' => 'remove', 'domain_id' => '_site_']);
            $this->render_data['board_list'][] = $board_data;
        }

        $this->render_data['alphanumeric_only'] = $this->domain->setting('only_alphanumeric_board_ids');
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/manage_boards_panel',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }

    private function removeInterstitial(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $this->startTimer();
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $manage_headers = ['header' => _gettext('General Management'),
            'sub_header' => _gettext('Confirm Board Deletion')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'manage_headers' => $manage_headers], true);
        $this->render_data['message'] = $parameters['message'];
        $this->render_data['continue_link_text'] = $parameters['continue_link']['text'];
        $this->render_data['continue_url'] = $parameters['continue_link']['href'];
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('interstitial/board_removal',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}