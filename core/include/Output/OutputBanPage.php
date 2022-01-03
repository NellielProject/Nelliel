<?php

declare(strict_types=1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;

class OutputBanPage extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('banned');
        $ban_hammer = $parameters['ban_hammer'];
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->general([], true);
        $this->render_data['ban_board'] = ($ban_hammer->getData('board_id') === Domain::GLOBAL) ? _gettext('All Boards') : $ban_hammer->getData(
                'board_id');
        $this->render_data['ban_time'] = date("F jS, Y H:i e", intval($ban_hammer->getData('start_time')));
        $this->render_data['ban_id'] = $ban_hammer->getData('ban_id');
        $ban_expire = $ban_hammer->getData('length') + $ban_hammer->getData('start_time');
        $expire_interval = ($ban_expire - time() >= 0) ? $ban_expire - time() : 0;
        $dt = new \DateTime();
        $dt->add(new \DateInterval('PT' . ($expire_interval) . 'S'));
        $interval = $dt->diff(new \DateTime());
        $duration = '';

        if ($interval->d > 0)
        {
            $duration .= $interval->format('%a days %h hours');
        }
        else if ($interval->h > 0)
        {
            $duration .= $interval->format('%h hours %i minutes');
        }
        else
        {
            $duration .= $interval->format('%i minutes');
        }

        $this->render_data['ban_length'] = $duration;
        $this->render_data['ban_expiration'] = date("F jS, Y H:i e", intval($ban_expire));
        $this->render_data['ban_reason'] = $ban_hammer->getData('reason');
        $this->render_data['ban_ip'] = nel_request_ip_address();
        $this->render_data['ban_page_extra_text'] = $this->site_domain()->setting('ban_page_extra_text');
        $this->render_data['extra_text'] = !nel_true_empty($this->render_data['ban_page_extra_text']);
        $this->render_data['appealed'] = $ban_hammer->getData('appeal_status') != 0;
        $this->render_data['reviewed'] = $ban_hammer->getData('appeal_status') == 1;
        $this->render_data['responded'] = $ban_hammer->getData('appeal_status') > 1;
        $appeal_min_time = $this->site_domain->setting('min_time_before_ban_appeal');

        if ($ban_hammer->getData('length') < $appeal_min_time ||
                time() - $ban_hammer->getData('start_time') < $appeal_min_time)
        {
            $this->render_data['min_time_met'] = false;
        }
        else
        {
            $this->render_data['min_time_met'] = true;
        }

        if ($this->render_data['min_time_met'] && $this->site_domain->setting('allow_ban_appeals') &&
                $ban_hammer->getData('appeal_status') == 0 && empty($ban_hammer->getData('ip_address_end')))
        {
            $this->render_data['appeal_allowed'] = true;
            $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(['module' => 'ban-page', 'actions' => 'add-appeal']);

            if (!empty($ban_hammer->getData('board_id')))
            {
                $this->render_data['form_action'] .= '&board-id=' . $ban_hammer->getData('board_id');
            }
        }
        else
        {
            $this->render_data['appeal_allowed'] = false;
            $this->render_data['appeal_denied'] = $ban_hammer->getData('appeal_status') == 2;
            $this->render_data['appeal_modified'] = $ban_hammer->getData('appeal_status') == 3;
            $this->render_data['show_response'] = $ban_hammer->getData('appeal_response') != '';
            $this->render_data['appeal_response'] = $ban_hammer->getData('appeal_response');
            $this->render_data['is_range'] = !empty($ban_hammer->getData('ip_address_end'));
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}