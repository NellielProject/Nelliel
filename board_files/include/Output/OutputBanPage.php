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

    public function render(array $parameters = array())
    {
        $ban_info = $parameters['ban_info'];

        $this->render_core->startTimer();
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $this->render_core->appendToOutput($output_header->render(['header_type' => 'general', 'dotdot' => '']));
        $render_input['ban_board'] = ($ban_info['all_boards'] > 0) ? _gettext('All Boards') : $ban_info['board_id'];
        $render_input['ban_time'] = date("F jS, Y H:i e", $ban_info['start_time']);
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

        $render_input['ban_length'] = $duration;
        $render_input['ban_expiration'] = date("F jS, Y H:i e", $ban_expire);
        $render_input['ban_reason'] = $ban_info['reason'];
        $render_input['ban_ip'] = $_SERVER['REMOTE_ADDR'];
        $render_input['appealed'] = $ban_info['appeal_status'] != 0;
        $render_input['reviewed'] = $ban_info['appeal_status'] == 1;
        $render_input['responded'] = $ban_info['appeal_status'] > 1;

        if ($ban_info['appeal_status'] == 0)
        {
            $render_input['form_action'] = $this->url_constructor->dynamic(MAIN_SCRIPT, ['module' => 'ban-page', 'action' => 'add-appeal']);

            if (!empty($ban_info['board_id']))
            {
                $render_input['form_action'] .= '&board_id=' . $ban_info['board_id'];
            }
        }

        if ($ban_info['appeal_status'] == 2 || $ban_info['appeal_status'] == 3)
        {
            if ($ban_info['appeal_status'] == 2)
            {
                $render_input['what_done'] = _gettext('You appeal has been reviewed and denied. You cannot appeal again.');
            }

            if ($ban_info['appeal_status'] == 3)
            {
                $render_input['what_done'] = _gettext('Your appeal has been reviewed and the ban has been altered.');
            }

            if ($ban_info['appeal_response'] != '')
            {
                $render_input['appeal_response'] = $ban_info['appeal_response'];
            }
        }

        $this->render_core->appendToOutput($this->render_core->renderFromTemplateFile('ban_page', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_core->appendToOutput($output_footer->render(['dotdot' => '', 'generate_styles' => false]));
        echo $this->render_core->getOutput();
        nel_clean_exit();
    }
}