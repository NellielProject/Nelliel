<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelBans extends OutputCore
{
    private $database;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $domain->database();
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        if(!isset($parameters['section']))
        {
            return;
        }

        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_ban_access'))
        {
            nel_derp(341, _gettext('You are not allowed to access the bans panel.'));
        }

        switch ($parameters['section'])
        {
            case 'panel':
                $this->renderPanel($parameters);
                break;

            case 'add':
                $this->renderAdd($parameters);
                break;

            case 'modify':
                $this->renderModify($parameters);
                break;
        }
    }

    public function renderPanel(array $parameters)
    {
        $user = $parameters['user'];
        $final_output = '';

        // Temp
        $this->render_instance = $this->domain->renderInstance();
        $this->render_instance->startRenderTimer();

        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Bans')];
        $final_output .= $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $template_loader = new \Mustache_Loader_FilesystemLoader($this->domain->templatePath(), ['extension' => '.html']);
        $render_instance = new \Mustache_Engine(['loader' => $template_loader]);
        $template_loader->load('management/panels/bans_panel_main');
        $render_input['can_modify'] = $user->domainPermission($this->domain, 'perm_ban_modify');

        if ($this->domain->id() !== '')
        {
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . BANS_TABLE . '" WHERE "board_id" = ? ORDER BY "ban_id" DESC');
            $ban_list = $this->database->executePreparedFetchAll($prepared, [$this->domain->id()], PDO::FETCH_ASSOC);
        }
        else
        {
            $ban_list = $this->database->executeFetchAll('SELECT * FROM "' . BANS_TABLE . '" ORDER BY "ban_id" DESC', PDO::FETCH_ASSOC);
        }

        $bgclass = 'row1';

        foreach ($ban_list as $ban_info)
        {
            $ban_data = array();
            $ban_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $ban_data['ban_id'] = $ban_info['ban_id'];
            $ban_data['type'] = $ban_info['type'];
            $ban_data['ip_address_start'] = $ban_info['ip_address_start'] ? @inet_ntop($ban_info['ip_address_start']) : 'Unknown';
            $ban_data['board_id'] = $ban_info['board_id'];
            $ban_data['reason'] = $ban_info['reason'];
            $ban_data['expiration'] = date("D F jS Y  H:i:s", $ban_info['length'] + $ban_info['start_time']);
            $ban_data['appeal'] = $ban_info['appeal'];
            $ban_data['appeal_response'] = $ban_info['appeal_response'];
            $ban_data['appeal_status'] = $ban_info['appeal_status'];
            $ban_data['modify_url'] = MAIN_SCRIPT . '?module=board&module=bans&action=modify&ban_id=' . $ban_info['ban_id'] . '&board_id=' .
                    $this->domain->id();
            $ban_data['remove_url'] = MAIN_SCRIPT . '?module=board&module=bans&action=remove&ban_id=' . $ban_info['ban_id'] . '&board_id=' .
                    $this->domain->id();
            $render_input['ban_list'][] = $ban_data;
        }

        $render_input['new_ban_url'] = MAIN_SCRIPT . '?module=board&module=bans&action=new&board_id=' . $this->domain->id();

        $this->render_instance->appendHTML($render_instance->render('management/panels/bans_panel_main', $render_input));
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }

    public function renderAdd(array $parameters)
    {
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_ban_modify'))
        {
            nel_derp(321, _gettext('You are not allowed to modify bans.'));
        }

        $final_output = '';

        // Temp
        $this->render_instance = $this->domain->renderInstance();
        $this->render_instance->startRenderTimer();

        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Add Ban')];
        $final_output .= $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $template_loader = new \Mustache_Loader_FilesystemLoader($this->domain->templatePath(), ['extension' => '.html']);
        $render_instance = new \Mustache_Engine(['loader' => $template_loader]);
        $template_loader->load('management/panels/bans_panel_add');
        $render_input['ban_board'] = (!empty($this->domain->id())) ? $this->domain->id() : '';
        $ip = $parameters['ip'];
        $type = $parameters['type'];

        if ($type === 'POST' && isset($_GET['post-id']))
        {
            $render_input['is_post_ban'] = true;
            $post_param = '&post-id=' . $_GET['post-id'];
        }
        else
        {
            $post_param = '';
        }

        $render_input['form_action'] = MAIN_SCRIPT . '?module=board&module=bans&action=add&board_id=' . $this->domain->id() . $post_param;
        $render_input['ban_ip'] = $ip;
        $render_input['ban_type'] = $type;

        $this->render_instance->appendHTML($render_instance->render('management/panels/bans_panel_add', $render_input));
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }

    public function renderModify(array $parameters)
    {
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_ban_modify'))
        {
            nel_derp(321, _gettext('You are not allowed to modify bans.'));
        }

        $final_output = '';

        // Temp
        $this->render_instance = $this->domain->renderInstance();
        $this->render_instance->startRenderTimer();

        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Modify Ban')];
        $final_output .= $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $template_loader = new \Mustache_Loader_FilesystemLoader($this->domain->templatePath(), ['extension' => '.html']);
        $render_instance = new \Mustache_Engine(['loader' => $template_loader]);
        $template_loader->load('management/panels/bans_panel_modify');
        $render_input['form_action'] = MAIN_SCRIPT . '?module=board&module=bans&action=update&board_id=' . $this->domain->id();
        $ban_id = $_GET['ban_id'];
        $ban_hammer = new \Nelliel\BanHammer($this->database);
        $ban_info = $ban_hammer->getBanById($ban_id, true);
        $render_input['ban_id'] = $ban_info['ban_id'];
        $render_input['ip_address_start'] = inet_ntop($ban_info['ip_address_start']);
        $render_input['board_id'] = $ban_info['board_id'];
        $render_input['type'] = $ban_info['type'];
        $render_input['start_time_formatted'] = date("D F jS Y  H:i:s", $ban_info['start_time']);
        $render_input['expiration'] = date("D F jS Y  H:i:s", $ban_info['length'] + $ban_info['start_time']);
        $render_input['years'] = $ban_info['years'];
        $render_input['days'] = $ban_info['days'];
        $render_input['hours'] = $ban_info['hours'];
        $render_input['minutes'] = $ban_info['minutes'];
        $render_input['all_boards'] = ($ban_info['all_boards'] > 0) ? 'checked' : '';
        $render_input['start_time'] = $ban_info['start_time'];
        $render_input['reason'] = $ban_info['reason'];
        $render_input['creator'] = $ban_info['creator'];
        $render_input['appeal'] = $ban_info['appeal'];
        $render_input['appeal_response'] = $ban_info['appeal_response'];
        $render_input['appeal_status'] = ($ban_info['appeal_status'] > 1) ? 'checked' : '';

        $this->render_instance->appendHTML($render_instance->render('management/panels/bans_panel_modify', $render_input));
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }
}