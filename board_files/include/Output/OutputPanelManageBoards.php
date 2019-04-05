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

    public function render(array $parameters = array())
    {
        if (!isset($parameters['section']))
        {
            return;
        }

        switch ($parameters['section'])
        {
            case 'panel':
                $this->panel($parameters);
                break;

            case 'remove_interstitial':
                $this->removeInterstitial($parameters);
                break;
        }
    }

    public function panel(array $parameters)
    {
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_manage_boards_access'))
        {
            nel_derp(370, _gettext('You are not allowed to access the board manager panel.'));
        }

        $this->render_core->startTimer();
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Manage Boards')];
        $this->render_core->appendToOutput(
                $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]));
        $render_input['form_action'] = MAIN_SCRIPT . '?module=manage-boards&action=add';
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
                $render_input['lock_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'manage-boards', 'board_id' => $board_info['board_id'], 'action' => 'lock']);
                $render_input['status'] = _gettext('Active');
                $render_input['lock_text'] = _gettext('Lock Board');
            }
            else
            {
                $render_input['lock_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'manage-boards', 'board_id' => $board_info['board_id'], 'action' => 'unlock']);
                $render_input['status'] = _gettext('Locked');
                $render_input['lock_text'] = _gettext('Unlock Board');
            }

            $board_data['remove_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'manage-boards', 'board_id' => $board_info['board_id'], 'action' => 'remove']);
            $render_input['board_list'][] = $board_data;
        }

        $this->render_core->appendToOutput(
                $this->render_core->renderFromTemplateFile('management/panels/manage_boards_panel', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_core->appendToOutput($output_footer->render(['dotdot' => '', 'generate_styles' => false]));
        echo $this->render_core->getOutput();
        nel_clean_exit();
    }

    public function removeInterstitial(array $parameters)
    {
        $this->render_core->startTimer();
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'),
            'sub_header' => _gettext('Confirm Board Deletion')];
        $this->render_core->appendToOutput(
                $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]));
        $render_input['message'] = $parameters['message'];
        $render_input['continue_link_text'] = $parameters['continue_link']['text'];
        $render_input['continue_url'] = $parameters['continue_link']['href'];

        $this->render_core->appendToOutput(
                $this->render_core->renderFromTemplateFile('management/interstitials/board_removal', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_core->appendToOutput($output_footer->render(['dotdot' => '', 'generate_styles' => false]));
        echo $this->render_core->getOutput();
        nel_clean_exit();
    }
}