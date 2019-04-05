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
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $ban_info = $parameters['ban_info'];

        // Temp
        $this->render_instance = $this->domain->renderInstance();
        $this->render_instance->startTimer();

        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $output_header->render(['header_type' => 'general', 'dotdot' => '']);
        $template_loader = new \Mustache_Loader_FilesystemLoader($this->domain->templatePath(), ['extension' => '.html']);
        $render_instance = new \Mustache_Engine(['loader' => $template_loader]);
        $template_loader->load('ban_page');

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

        $this->render_instance->appendToOutput($render_instance->render('ban_page', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $output_footer->render(['dotdot' => '', 'styles' => false]);
        echo $this->render_instance->getOutput();
    }
}