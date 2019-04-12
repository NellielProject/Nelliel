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

    public function render(array $parameters = array(), bool $data_only = false)
    {
        if (!isset($parameters['section']))
        {
            return;
        }

        switch ($parameters['section'])
        {
            case 'panel':
                $output = $this->panel($parameters);
                break;

            case 'remove_interstitial':
                $output = $this->removeInterstitial($parameters);
                break;
        }

        return $output;
    }

    public function panel(array $parameters)
    {
        $render_data = array();
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_manage_boards_access'))
        {
            nel_derp(370, _gettext('You are not allowed to access the board manager panel.'));
        }

        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $render_data['head'] = $output_head->render(['dotdot' => $dotdot]);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Manage Boards')];
        $render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'extra_data' => $extra_data], true);
        $render_data['form_action'] = MAIN_SCRIPT . '?module=manage-boards&action=add';
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
                $render_data['lock_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'manage-boards', 'board_id' => $board_info['board_id'], 'action' => 'lock']);
                $render_data['status'] = _gettext('Active');
                $render_data['lock_text'] = _gettext('Lock Board');
            }
            else
            {
                $render_data['lock_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'manage-boards', 'board_id' => $board_info['board_id'], 'action' => 'unlock']);
                $render_data['status'] = _gettext('Locked');
                $render_data['lock_text'] = _gettext('Unlock Board');
            }

            $board_data['remove_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'manage-boards', 'board_id' => $board_info['board_id'], 'action' => 'remove']);
            $render_data['board_list'][] = $board_data;
        }

        $render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/manage_boards_panel',
                $render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output($render_data, 'page', true);
        echo $output;
        return $output;
    }

    public function removeInterstitial(array $parameters)
    {
        $render_data = array();
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $render_data['head'] = $output_head->render(['dotdot' => $dotdot]);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'),
            'sub_header' => _gettext('Confirm Board Deletion')];
        $render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'extra_data' => $extra_data], true);
        $render_data['message'] = $parameters['message'];
        $render_data['continue_link_text'] = $parameters['continue_link']['text'];
        $render_data['continue_url'] = $parameters['continue_link']['href'];
        $render_data['body'] = $this->render_core->renderFromTemplateFile('management/interstials/board_removal',
                $render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output($render_data, 'page', true);
        echo $output;
        return $output;
    }
}