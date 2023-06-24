<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\BanHammer;
use Nelliel\BansAccess;
use Nelliel\IPInfo;
use Nelliel\Domains\Domain;
use PDO;

class OutputPanelBans extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function main(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/bans_main');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Bans');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $this->render_data['can_add'] = $this->session->user()->checkPermission($this->domain, 'perm_add_bans');
        $bans_access = new BansAccess($this->database);
        $ban_list = $bans_access->getBans($this->domain->id());
        $this->render_data['new_ban_url'] = nel_build_router_url([$this->domain->id(), 'bans', 'new']);
        $bgclass = 'row1';

        foreach ($ban_list as $ban_hammer) {
            $ban_data = array();
            $ban_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $ban_data['ban_id'] = $ban_hammer->getData('ban_id');

            switch ($ban_hammer->getData('ban_type')) {
                case BansAccess::IP:
                    $ban_data['target'] = $ban_hammer->getData('ip_address');
                    $ban_data['type'] = __('IP');
                    break;

                case BansAccess::HASHED_IP:
                    $ban_data['target'] = $ban_hammer->getData('hashed_ip_address');
                    $ban_data['type'] = __('IP');
                    break;

                case BansAccess::RANGE:
                    $ban_data['target'] = $ban_hammer->getData('range_start') . '-' . $ban_hammer->getData('range_end');
                    $ban_data['type'] = __('Range');
                    break;

                case BansAccess::HASHED_SUBNET:
                    $ban_data['target'] = $ban_hammer->getData('hashed_subnet');
                    $ban_data['type'] = __('Subnet');
                    break;
            }

            $ban_data['board_id'] = $ban_hammer->getData('board_id');

            if ($ban_data['board_id'] === Domain::GLOBAL) {
                $ban_data['board_id'] = 'Global';
            }

            $ban_data['reason'] = $ban_hammer->getData('reason');
            $ban_data['seen'] = $ban_hammer->getData('seen');
            $ban_data['expiration'] = $this->domain->domainDateTime(
                intval($ban_hammer->getData('length') + $ban_hammer->getData('start_time')))->format(
                $this->site_domain->setting('control_panel_list_time_format'));
            $ban_data['can_modify'] = $this->session->user()->checkPermission($this->domain, 'perm_modify_bans');
            $ban_data['can_delete'] = $this->session->user()->checkPermission($this->domain, 'perm_delete_bans');
            $ban_data['modify_url'] = nel_build_router_url(
                [$this->domain->id(), 'bans', $ban_hammer->getData('ban_id'), 'modify']);
            $ban_data['delete_url'] = nel_build_router_url(
                [$this->domain->id(), 'bans', $ban_hammer->getData('ban_id'), 'delete']);
            $ban_data['appealed'] = ($ban_hammer->appealCount() > 0) ? __('Yes') : __('No');

            $this->render_data['ban_list'][] = $ban_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function new(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/bans_new');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Bans');
        $parameters['section'] = $parameters['section'] ?? _gettext('New Ban');
        $content_id = $parameters['content_id'] ?? null;

        $this->render_data['ban_type_select']['select_name'] = 'ban_type';
        $this->render_data['ban_type_select']['options'][] = ['option_label' => __('IP address'),
            'option_value' => 'ip'];

        $can_range_ban = $this->session->user()->checkPermission($this->domain, 'perm_add_range_bans');
        $from_content = !is_null($content_id);
        $this->render_data['from_content'] = $from_content;

        if ($can_range_ban) {
            $this->render_data['ban_type_select']['options'][] = ['option_label' => __('Range'),
                'option_value' => 'range'];

            if (!$from_content) {
                $this->render_data['ban_type_select']['options'][] = ['option_label' => __('Subnet'),
                    'option_value' => 'subnet'];
            }
        }

        if ($from_content) {
            $content = $content_id->getInstanceFromID($this->domain);
            $ip_info = new IPInfo($content->data('hashed_ip_address'));

            if (empty($ip_info->getInfo('ip_address')) ||
                !$this->session->user()->checkPermission($this->domain, 'perm_view_unhashed_ip')) {
                $this->render_data['ban_ip'] = $ip_info->getInfo('hashed_ip_address');
            } else {
                $this->render_data['ban_ip'] = nel_convert_ip_from_storage($content->data('ip_address'));
            }

            if ($can_range_ban) {
                if (!is_null($ip_info->getInfo('hashed_small_subnet'))) {
                    $this->render_data['ban_type_select']['options'][] = ['option_label' => __('Small subnet'),
                        'option_value' => 'small_subnet'];
                }

                if (!is_null($ip_info->getInfo('hashed_large_subnet'))) {
                    $this->render_data['ban_type_select']['options'][] = ['option_label' => __('Large subnet'),
                        'option_value' => 'large_subnet'];
                }
            }
        }

        $this->render_data['allow_appeal'] = $this->domain->setting('allow_ban_appeals') ? 'checked' : '';
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);

        if ($this->domain->id() !== Domain::SITE) {
            $this->render_data['ban_board'] = $this->domain->id();
        }

        $this->render_data['form_action'] = nel_build_router_url([$this->domain->id(), 'bans', 'new']);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function modify(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/bans_modify');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Bans');
        $parameters['section'] = $parameters['section'] ?? _gettext('Modify Ban');
        $ban_id = intval($parameters['ban_id'] ?? 0);
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $this->render_data['form_action'] = nel_build_router_url([$this->domain->id(), 'bans', $ban_id, 'modify']);
        $ban_hammer = new BanHammer($this->database, $ban_id);
        $ban_type = $ban_hammer->getData('ban_type');

        if ($this->session->user()->checkPermission($this->domain, 'perm_view_unhashed_ip')) {
            $this->render_data['ban_ip'] = $this->formatIP($ban_hammer);
        } else {
            $this->render_data['ban_ip'] = $ban_hammer->getData('hashed_ip_address');
        }

        $this->render_data['ban_type_select']['select_name'] = 'ban_type';
        $this->render_data['ban_type_select']['options'][] = ['option_label' => __('IP address'),
            'option_value' => 'ip',
            'option_selected' => ($ban_type === BansAccess::IP || $ban_type === BansAccess::HASHED_IP) ? 'selected' : ''];

        $can_range_ban = $this->session->user()->checkPermission($this->domain, 'perm_add_range_bans');

        if ($can_range_ban) {
            $this->render_data['ban_type_select']['options'][] = ['option_label' => __('Range'),
                'option_value' => 'range', 'option_selected' => $ban_type === BansAccess::RANGE ? 'selected' : ''];
            $this->render_data['ban_type_select']['options'][] = ['option_label' => __('Subnet'),
                'option_value' => 'subnet',
                'option_selected' => $ban_type === BansAccess::HASHED_SUBNET ? 'selected' : ''];
        }

        $this->render_data['ban_id'] = $ban_hammer->getData('ban_id');
        $this->render_data['ban_board'] = $ban_hammer->getData('board_id');
        $this->render_data['start_time_formatted'] = $this->domain->domainDateTime(
            intval($ban_hammer->getData('start_time')))->format("D F jS Y  H:i:s");
        $this->render_data['expiration'] = $this->domain->domainDateTime(
            intval($ban_hammer->getData('length') + $ban_hammer->getData('start_time')))->format("D F jS Y  H:i:s");
        $times = $ban_hammer->getData('times');
        $this->render_data['years'] = $times['years'];
        $this->render_data['days'] = $times['days'];
        $this->render_data['hours'] = $times['hours'];
        $this->render_data['minutes'] = $times['minutes'];
        $this->render_data['global'] = ($ban_hammer->getData('board_id') === Domain::GLOBAL) ? 'checked' : '';
        $this->render_data['start_time'] = $ban_hammer->getData('start_time');
        $this->render_data['ban_reason'] = $ban_hammer->getData('reason');
        $this->render_data['seen'] = $ban_hammer->getData('seen');
        $this->render_data['creator'] = $ban_hammer->getData('creator');
        $this->render_data['allow_appeal'] = $ban_hammer->getData('appeal_allowed') == 1 ? 'checked' : '';

        $prepared = $this->database->prepare(
            'SELECT * FROM "' . NEL_BAN_APPEALS_TABLE . '" WHERE "ban_id" = ? ORDER BY "time" DESC');
        $appeals = $this->database->executePreparedFetchAll($prepared, [$ban_hammer->getData('ban_id')],
            PDO::FETCH_ASSOC);

        foreach ($appeals as $appeal) {
            if ($appeal['pending']) {
                $this->render_data['appeal_id'] = $appeal['appeal_id'];
                $this->render_data['appeal'] = $appeal['appeal'];
                $this->render_data['appeal_response'] = $appeal['response'];
                $this->render_data['has_appeal'] = true;
                $this->render_data['pending_appeal'] = boolval($appeal['pending']);
                $this->render_data['denied'] = $appeal['denied'] == 1 ? 'checked' : '';

                $this->render_data['appeal_status_select']['select_name'] = 'appeal_status';
                $this->render_data['appeal_status_select']['options'][] = ['option_label' => __('Unreviewed'),
                    'option_value' => 'unreviewed', 'option_selected' => ($appeal['pending']) ? 'selected' : ''];
                $this->render_data['appeal_status_select']['options'][] = ['option_label' => __('Approved'),
                    'option_value' => 'approved', 'option_selected' => (!$appeal['denied'] && !$appeal['pending']) ? 'selected' : ''];
                $this->render_data['appeal_status_select']['options'][] = ['option_label' => __('Denied'),
                    'option_value' => 'denied', 'option_selected' => ($appeal['denied']) ? 'selected' : ''];
                break;
            }
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    private function formatIP(BanHammer $ban_hammer)
    {
        $ip_address = null;

        if (!is_null($ban_hammer->getData('ip_address_start'))) {
            $ip_address = $ban_hammer->getData('ip_address_start');

            if (!is_null($ban_hammer->getData('ip_address_end'))) {
                $ip_address .= '-' . $ban_hammer->getData('ip_address_end');
            }
        } else {
            $ip_address = $ban_hammer->getData('hashed_ip_address');
        }

        return $ip_address;
    }
}