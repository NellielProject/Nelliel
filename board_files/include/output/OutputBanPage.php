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
    }

    public function render(array $parameters = array())
    {
        $this->prepare('ban_page.html');
        $ban_info = $parameters['ban_info'];
        $url_constructor = new \Nelliel\URLConstructor();
        $output_header = new \Nelliel\Output\OutputHeader($this->domain, nel_database());
        $output_header->render(['header_type' => 'board']);
        $banned_board = ($ban_info['all_boards'] > 0) ? _gettext('All Boards') : $ban_info['board_id'];
        $ban_page_nodes = $this->dom->getElementsByAttributeName('data-parse-id', true);
        $ban_page_nodes['banned-board']->setContent($banned_board);
        $ban_page_nodes['banned-time']->setContent(date("F jS, Y H:i e", $ban_info['start_time']));
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

        $ban_page_nodes['banned-length']->setContent($duration);
        $ban_page_nodes['banned-expire']->setContent(date("F jS, Y H:i e", $ban_expire));
        $ban_page_nodes['banned-reason']->setContent($ban_info['reason']);
        $ban_page_nodes['banned-ip']->setContent($_SERVER['REMOTE_ADDR']);

        if ($ban_info['appeal_status'] == 0)
        {
            $ban_page_nodes['appeal-form']->extSetAttribute('action', $url_constructor->dynamic(MAIN_SCRIPT, ['module' => 'ban-page', 'action' => 'add-appeal']));

            if (!empty($ban_info['board_id']))
            {
                $ban_page_nodes['appeal-form']->modifyAttribute('action', '&board-id=' . $ban_info['board_id'], 'after');
            }
        }
        else
        {
            $ban_page_nodes['appeal-form']->remove();
        }

        if ($ban_info['appeal_status'] != 1)
        {
            $ban_page_nodes['appeal-pending']->remove();
        }

        if ($ban_info['appeal_status'] != 2 && $ban_info['appeal_status'] != 3)
        {
            $ban_page_nodes['appeal-response']->remove();
        }
        else
        {
            if ($ban_info['appeal_status'] == 2)
            {
                $ban_page_nodes['appeal-what-done']->setContent(
                        _gettext('You appeal has been reviewed and denied. You cannot appeal again.'));
            }

            if ($ban_info['appeal_status'] == 3)
            {
                $ban_page_nodes['appeal-what-done']->setContent(
                        _gettext('Your appeal has been reviewed and the ban has been altered.'));
            }

            if ($ban_info['appeal_response'] != '')
            {
                $ban_page_nodes['appeal-response-text']->setContent($ban_info['appeal_response']);
            }
        }

        $this->domain->translator()->translateDom($this->dom, $this->domain->setting('language'));
        $this->domain->renderInstance()->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain, null, true);
        echo $this->domain->renderInstance()->outputRenderSet();
    }
}