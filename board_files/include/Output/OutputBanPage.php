<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;

class OutputBanPage extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters = array(), bool $data_only = false)
    {
        $render_data = array();
        $ban_info = $parameters['ban_info'];
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $render_data['head'] = $output_head->render(['dotdot' => $dotdot]);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $render_data['header'] = $output_header->render(['header_type' => 'general', 'dotdot' => $dotdot], true);
        $render_data['ban_board'] = ($ban_info['all_boards'] > 0) ? _gettext('All Boards') : $ban_info['board_id'];
        $render_data['ban_time'] = date("F jS, Y H:i e", $ban_info['start_time']);
        $ban_expire = $ban_info['length'] + $ban_info['start_time'];
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

        $render_data['ban_length'] = $duration;
        $render_data['ban_expiration'] = date("F jS, Y H:i e", $ban_expire);
        $render_data['ban_reason'] = $ban_info['reason'];
        $render_data['ban_ip'] = $_SERVER['REMOTE_ADDR'];
        $render_data['appealed'] = $ban_info['appeal_status'] != 0;
        $render_data['reviewed'] = $ban_info['appeal_status'] == 1;
        $render_data['responded'] = $ban_info['appeal_status'] > 1;

        if ($ban_info['appeal_status'] == 0)
        {
            $render_data['form_action'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'ban-page', 'action' => 'add-appeal']);

            if (!empty($ban_info['board_id']))
            {
                $render_data['form_action'] .= '&board_id=' . $ban_info['board_id'];
            }
        }

        if ($ban_info['appeal_status'] == 2 || $ban_info['appeal_status'] == 3)
        {
            if ($ban_info['appeal_status'] == 2)
            {
                $render_data['what_done'] = _gettext(
                        'You appeal has been reviewed and denied. You cannot appeal again.');
            }

            if ($ban_info['appeal_status'] == 3)
            {
                $render_data['what_done'] = _gettext('Your appeal has been reviewed and the ban has been altered.');
            }

            if ($ban_info['appeal_response'] != '')
            {
                $render_data['appeal_response'] = $ban_info['appeal_response'];
            }
        }

        $render_data['body'] = $this->render_core->renderFromTemplateFile('ban_page', $render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output($render_data, 'page');
        echo $output;
        return $output;
    }
}