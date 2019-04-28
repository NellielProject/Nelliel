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

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
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
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_manage_boards_access'))
        {
            nel_derp(370, _gettext('You are not allowed to access the board manager panel.'));
        }

        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new OutputHeader($this->domain);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Manage Boards')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);
        $this->render_data['form_action'] = MAIN_SCRIPT . '?module=manage-boards&action=add';
        $board_data = $this->database->executeFetchAll(
                'SELECT * FROM "' . BOARD_DATA_TABLE . '" ORDER BY "board_id" DESC', PDO::FETCH_ASSOC);
        $bgclass = 'row1';

        foreach ($board_data as $board_info)
        {
            $board_data = array();
            $board_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $board_data['id'] = $board_info['board_id'];
            $board_data['directory'] = $board_info['board_id'];
            $board_data['db_prefix'] = $board_info['db_prefix'];

            if ($board_info['locked'] == 0)
            {
                $this->render_data['lock_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'manage-boards', 'board_id' => $board_info['board_id'], 'action' => 'lock']);
                $this->render_data['status'] = _gettext('Active');
                $this->render_data['lock_text'] = _gettext('Lock Board');
            }
            else
            {
                $this->render_data['lock_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'manage-boards', 'board_id' => $board_info['board_id'], 'action' => 'unlock']);
                $this->render_data['status'] = _gettext('Locked');
                $this->render_data['lock_text'] = _gettext('Unlock Board');
            }

            $board_data['remove_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'manage-boards', 'board_id' => $board_info['board_id'], 'action' => 'remove']);
            $this->render_data['board_list'][] = $board_data;
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/manage_boards_panel',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }

    private function removeInterstitial(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new OutputHeader($this->domain);
        $manage_headers = ['header' => _gettext('General Management'),
            'sub_header' => _gettext('Confirm Board Deletion')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);
        $this->render_data['message'] = $parameters['message'];
        $this->render_data['continue_link_text'] = $parameters['continue_link']['text'];
        $this->render_data['continue_url'] = $parameters['continue_link']['href'];
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('management/interstials/board_removal',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}