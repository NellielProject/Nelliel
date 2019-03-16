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
        $this->prepare('management/panels/bans_panel_main.html');
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Bans')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);

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

        $ban_info_table = $this->dom->getElementById('ban-info-table');
        $ban_info_row = $this->dom->getElementById('ban-info-row');
        $bgclass = 'row1';

        foreach ($ban_list as $ban_info)
        {
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $temp_ban_info_row = $ban_info_row->cloneNode(true);
            $temp_ban_info_row->extSetAttribute('class', $bgclass);
            $ban_nodes = $temp_ban_info_row->getElementsByAttributeName('data-parse-id', true);
            $ban_nodes['ban-id']->setContent($ban_info['ban_id']);
            $ban_nodes['ban-type']->setContent($ban_info['type']);
            $ban_nodes['ip-address-start']->setContent(
                    $ban_info['ip_address_start'] ? @inet_ntop($ban_info['ip_address_start']) : 'Unknown');
            $ban_nodes['board-id']->setContent($ban_info['board_id']);
            $ban_nodes['ban-reason']->setContent($ban_info['reason']);
            $ban_nodes['ban-expiration']->setContent(date("D F jS Y  H:i:s", $ban_info['length'] + $ban_info['start_time']));
            $ban_nodes['ban-appeal']->setContent($ban_info['appeal']);
            $ban_nodes['ban-appeal-response']->setContent($ban_info['appeal_response']);
            $ban_nodes['ban-appeal-status']->setContent($ban_info['appeal_status']);
            $ban_nodes['link-modify-ban']->extSetAttribute('href',
                    MAIN_SCRIPT . '?module=board&module=bans&action=modify&ban_id=' . $ban_info['ban_id'] . '&board_id=' .
                    $this->domain->id());
            $ban_nodes['link-remove-ban']->extSetAttribute('href',
                    MAIN_SCRIPT . '?module=board&module=bans&action=remove&ban_id=' . $ban_info['ban_id'] . '&board_id=' .
                    $this->domain->id());
            $ban_info_table->appendChild($temp_ban_info_row);
        }

        $ban_info_row->remove();

        $form_add_ban = $this->dom->getElementById('link-new-ban');
        $form_add_ban->extSetAttribute('href', MAIN_SCRIPT . '?module=board&module=bans&action=new&board_id=' . $this->domain->id());

        $this->domain->translator()->translateDom($this->dom);
        $this->render_instance->appendHTMLFromDOM($this->dom);
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

        $this->prepare('management/panels/bans_panel_add_ban.html');
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Add Ban')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $ip = $parameters['ip'];
        $type = $parameters['type'];

        if (!$user->domainPermission($this->domain, 'perm_ban_modify'))
        {
            $this->dom->getElementById('ban-all-boards-row')->remove();
        }

        if (!empty($this->domain->id()))
        {
            $add_ban_form = $this->dom->getElementById('ban-board-field')->extSetAttribute('value', $this->domain->id());
        }

        if ($type === 'POST' && isset($_GET['post-id']))
        {
            $post_param = '&post-id=' . $_GET['post-id'];
        }
        else
        {
            $post_param = '';
            $this->dom->getElementById('ban-mod-comment')->remove();
        }

        $add_ban_form = $this->dom->getElementById('add-ban-form');
        $add_ban_form->extSetAttribute('action',
                MAIN_SCRIPT . '?module=board&module=bans&action=add&board_id=' . $this->domain->id() . $post_param);
        $ban_nodes = $add_ban_form->getElementsByAttributeName('data-parse-id', true);
        $ban_nodes['ban-ip']->extSetAttribute('value', $ip);
        $this->dom->getElementById('ban-type')->extSetAttribute('value', $type);

        $this->domain->translator()->translateDom($this->dom);
        $this->render_instance->appendHTMLFromDOM($this->dom);
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

        $ban_hammer = new \Nelliel\BanHammer($this->domain->database());
        $this->prepare('management/panels/bans_panel_modify_ban.html');
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Modify Ban')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $this->dom->getElementById('modify-ban-form')->extSetAttribute('action',
                MAIN_SCRIPT . '?module=board&module=bans&action=update&board_id=' . $this->domain->id());
        $ban_id = $_GET['ban_id'];
        $ban_info = $ban_hammer->getBanById($ban_id, true);
        $this->dom->getElementById('ban-ip-field')->extSetAttribute('value', @inet_ntop($ban_info['ip_address_start']));
        $this->dom->getElementById('ban-board-field')->setContent($ban_info['board_id']);
        $this->dom->getElementById('ban-type-display')->setContent($ban_info['type']);
        $this->dom->getElementById('ban-time-display')->setContent(date("D F jS Y  H:i:s", $ban_info['start_time']));
        $this->dom->getElementById('ban-expiration-display')->setContent(
                date("D F jS Y  H:i:s", $ban_info['length'] + $ban_info['start_time']));
        $this->dom->getElementById('ban-time-years')->extSetAttribute('value', $ban_info['years']);
        $this->dom->getElementById('ban-time-days')->extSetAttribute('value', $ban_info['days']);
        $this->dom->getElementById('ban-time-hours')->extSetAttribute('value', $ban_info['hours']);
        $this->dom->getElementById('ban-time-minutes')->extSetAttribute('value', $ban_info['minutes']);

        if (($ban_info['all_boards'] > 0))
        {
            $this->dom->getElementById('ban-all-boards-field')->extSetAttribute('checked', true);
        }

        $this->dom->getElementById('ban-id-field')->extSetAttribute('value', $ban_info['ban_id']);
        $this->dom->getElementById('ban-start-field')->extSetAttribute('value', $ban_info['start_time']);
        $this->dom->getElementById('ban-reason-field')->setContent($ban_info['reason']);
        $this->dom->getElementById('ban-name-display')->setContent($ban_info['creator']);

        if ($ban_info['appeal'] === '')
        {
            $this->dom->getElementById('ban-appeal-display-row')->remove();
        }
        else
        {
            $this->dom->getElementById('ban-appeal-display')->setContent($ban_info['appeal']);
        }

        if ($ban_info['appeal_response'] === '')
        {
            $this->dom->getElementById('ban-appeal-response-row')->remove();
        }
        else
        {
            $this->dom->getElementById('ban-appeal-response')->setContent($ban_info['appeal_response']);
        }

        if ($ban_info['appeal_status'] > 1)
        {
            $this->dom->getElementById('ban-appealed-field')->extSetAttribute('checked', 'checked');
        }

        $this->domain->translator()->translateDom($this->dom);
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }
}