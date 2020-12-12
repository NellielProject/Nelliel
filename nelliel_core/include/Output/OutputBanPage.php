<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;

class OutputBanPage extends OutputCore
{

    function __construct(Domain $domain, bool $write_mode)
    {
        $this->domain = $domain;
        $this->write_mode = $write_mode;
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $ban_hammer = $parameters['ban_hammer'];
        $this->startTimer();
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->render(['header_type' => 'general'], true);
        $this->render_data['ban_board'] = ($ban_hammer->getData('all_boards') > 0) ? _gettext('All Boards') : $ban_hammer->getData(
                'board_id');
        $this->render_data['ban_time'] = date("F jS, Y H:i e", $ban_hammer->getData('start_time'));
        $this->render_data['ban_id'] = $ban_hammer->getData('ban_id');
        $ban_expire = $ban_hammer->getData('length') + $ban_hammer->getData('start_time');
        $dt = new \DateTime();
        $dt->add(new \DateInterval('PT' . ($ban_expire - time()) . 'S'));
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
        $this->render_data['ban_expiration'] = date("F jS, Y H:i e", $ban_expire);
        $this->render_data['ban_reason'] = $ban_hammer->getData('reason');
        $this->render_data['ban_ip'] = nel_request_ip_address();
        $this->render_data['appealed'] = $ban_hammer->getData('appeal_status') != 0;
        $this->render_data['reviewed'] = $ban_hammer->getData('appeal_status') == 1;
        $this->render_data['responded'] = $ban_hammer->getData('appeal_status') > 1;

        if ($ban_hammer->getData('appeal_status') == 0 && empty($ban_hammer->getData('ip_address_end')))
        {
            $this->render_data['appeal_allowed'] = true;
            $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY .
                    http_build_query(['module' => 'ban-page', 'actions' => 'add-appeal']);

            if (!empty($ban_hammer->getData('board_id')))
            {
                $this->render_data['form_action'] .= '&board_id=' . $ban_hammer->getData('board_id');
            }
        }
        else
        {
            $this->render_data['appeal_allowed'] = false;

            if ($ban_hammer->getData('appeal_status') == 2)
            {
                $this->render_data['what_done'] = _gettext(
                        'You appeal has been reviewed and denied. You cannot appeal again.');
            }

            if ($ban_hammer->getData('appeal_status') == 3)
            {
                $this->render_data['what_done'] = _gettext(
                        'Your appeal has been reviewed and the ban has been altered.');
            }

            if ($ban_hammer->getData('appeal_response') != '')
            {
                $this->render_data['appeal_response'] = $ban_hammer->getData('appeal_response');
            }

            if (!empty($ban_hammer->getData('ip_address_end')))
            {
                $this->render_data['is_range'] = true;
            }
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('banned_user', $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}