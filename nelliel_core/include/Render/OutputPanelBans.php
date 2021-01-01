<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\BanHammer;
use Nelliel\BansAccess;
use Nelliel\Domains\Domain;

class OutputPanelBans extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function main(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $user = $parameters['user'];
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $manage_headers = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Bans')];
        $this->render_data['header'] = $output_header->general(['manage_headers' => $manage_headers], true);
        $this->render_data['can_modify'] = $user->checkPermission($this->domain, 'perm_manage_bans');
        $bans_access = new BansAccess($this->database);

        if ($this->domain->id() !== '_site_')
        {
            $ban_list = $bans_access->getBans($this->domain->id());
        }
        else
        {
            $ban_list = $bans_access->getBans();
        }

        $bgclass = 'row1';

        foreach ($ban_list as $ban_hammer)
        {
            $ban_data = array();
            $ban_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $ban_data['ban_id'] = $ban_hammer->getData('ban_id');
            $ban_data['ban_type'] = $ban_hammer->getData('ban_type');
            $ban_data['ip_address'] = $this->formatIP($ban_hammer) ?? nel_truncate_hash(
                    $ban_hammer->getData('hashed_ip_address'));
            $ban_data['board_id'] = $ban_hammer->getData('board_id');
            $ban_data['all_boards'] = $ban_hammer->getData('all_boards');
            $ban_data['reason'] = $ban_hammer->getData('reason');
            $ban_data['expiration'] = date("D F jS Y  H:i:s",
                    $ban_hammer->getData('length') + $ban_hammer->getData('start_time'));
            $ban_data['appeal'] = $ban_hammer->getData('appeal');
            $ban_data['appeal_response'] = $ban_hammer->getData('appeal_response');
            $ban_data['appeal_status'] = $ban_hammer->getData('appeal_status');
            $ban_data['modify_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=bans&actions=edit&ban_id=' .
                    $ban_hammer->getData('ban_id') . '&board-id=' . $this->domain->id();
            $ban_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=bans&actions=remove&ban_id=' .
                    $ban_hammer->getData('ban_id') . '&board-id=' . $this->domain->id();
            $this->render_data['ban_list'][] = $ban_data;
        }

        if ($this->domain->id() !== '_site_')
        {
            $this->render_data['new_ban_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    'module=admin&section=bans&actions=new&board-id=' . $this->domain->id();
        }
        else
        {
            $this->render_data['new_ban_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=bans&actions=new';
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/bans_panel_main',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }

    public function new(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $manage_headers = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Add Ban')];
        $this->render_data['header'] = $output_header->general(['manage_headers' => $manage_headers], true);

        if ($this->domain->id() !== '_site_')
        {
            $this->render_data['ban_board'] = $this->domain->id();
        }

        $this->render_data['ban_ip'] = $parameters['ip_start'];
        $this->render_data['ban_hashed_ip'] = $parameters['hashed_ip'];
        $this->render_data['ban_type'] = $parameters['ban_type'];
        $this->render_data['content_ban'] = $this->render_data['ban_type'] === 'CONTENT';
        $post_param = '';
        $this->render_data['unhashed_ip'] = nel_site_domain()->setting('store_unhashed_ip');
        $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                'module=admin&section=bans&actions=add&board-id=' . $this->domain->id() . $post_param;
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/bans_panel_add',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }

    public function modify(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $user = $parameters['user'];
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $manage_headers = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Modify Ban')];
        $this->render_data['header'] = $output_header->general(['manage_headers' => $manage_headers], true);
        $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                'module=admin&section=bans&actions=update&board-id=' . $this->domain->id();
        $ban_id = $_GET['ban_id'];
        $this->render_data['view_unhashed_ip'] = $user->checkPermission($this->domain, 'perm_view_unhashed_ip');
        $ban_hammer = new \Nelliel\BanHammer($this->database);
        $ban_hammer->loadFromID($ban_id);
        $this->render_data['ip_address'] = $this->formatIP($ban_hammer);
        $this->render_data['ban_id'] = $ban_hammer->getData('ban_id');
        $this->render_data['hashed_ip'] = $ban_hammer->getData('hashed_ip_address');
        $this->render_data['ban_board'] = $ban_hammer->getData('board_id');
        $this->render_data['ban_type'] = $ban_hammer->getData('ban_type');
        $this->render_data['start_time_formatted'] = date("D F jS Y  H:i:s", $ban_hammer->getData('start_time'));
        $this->render_data['expiration'] = date("D F jS Y  H:i:s",
                $ban_hammer->getData('length') + $ban_hammer->getData('start_time'));
        $times = $ban_hammer->getData('times');
        $this->render_data['years'] = $times['years'];
        $this->render_data['days'] = $times['days'];
        $this->render_data['hours'] = $times['hours'];
        $this->render_data['minutes'] = $times['minutes'];
        $this->render_data['all_boards'] = ($ban_hammer->getData('all_boards') > 0) ? 'checked' : '';
        $this->render_data['start_time'] = $ban_hammer->getData('start_time');
        $this->render_data['ban_reason'] = $ban_hammer->getData('reason');
        $this->render_data['creator'] = $ban_hammer->getData('creator');
        $this->render_data['appeal'] = $ban_hammer->getData('appeal');
        $this->render_data['appeal_response'] = $ban_hammer->getData('appeal_response');
        $this->render_data['appeal_status_' . $ban_hammer->getData('appeal_status')] = 'selected';
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/bans_panel_modify',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }

    private function formatIP(BanHammer $ban_hammer)
    {
        $ip_address = null;

        if (!is_null($ban_hammer->getData('ip_address_start')))
        {
            $ip_address = $ban_hammer->getData('ip_address_start');

            if (!is_null($ban_hammer->getData('ip_address_end')))
            {
                $ip_address .= ' - ' . $ban_hammer->getData('ip_address_end');
            }
        }

        return $ip_address;
    }
}