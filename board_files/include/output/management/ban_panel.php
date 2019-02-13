<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_main_ban_panel($user, \Nelliel\Domain $domain)
{
    $database = nel_database();
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    nel_render_general_header($domain, null,
            array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Bans')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/bans_panel_main.html');

    if ($domain->id() !== '')
    {
        $prepared = $database->prepare(
                'SELECT * FROM "' . BANS_TABLE . '" WHERE "board_id" = ? ORDER BY "ban_id" DESC');
        $ban_list = $database->executePreparedFetchAll($prepared, [$domain->id()], PDO::FETCH_ASSOC);
    }
    else
    {
        $ban_list = $database->executeFetchAll('SELECT * FROM "' . BANS_TABLE . '" ORDER BY "ban_id" DESC', PDO::FETCH_ASSOC);
    }

    $ban_info_table = $dom->getElementById('ban-info-table');
    $ban_info_row = $dom->getElementById('ban-info-row');
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
                $domain->id());
        $ban_nodes['link-remove-ban']->extSetAttribute('href',
                MAIN_SCRIPT . '?module=board&module=bans&action=remove&ban_id=' . $ban_info['ban_id'] . '&board_id=' .
                $domain->id());
        $ban_info_table->appendChild($temp_ban_info_row);
    }

    $ban_info_row->remove();

    $form_add_ban = $dom->getElementById('link-new-ban');
    $form_add_ban->extSetAttribute('href', MAIN_SCRIPT . '?module=board&module=bans&action=new&board_id=' . $domain->id());
    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}

function nel_render_ban_panel_add($user, \Nelliel\Domain $domain, $ip = '', $type = 'GENERAL')
{
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    nel_render_general_header($domain, null,
            array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Bans')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/bans_panel_add_ban.html');

    if (!$user->domainPermission($domain, 'perm_ban_modify'))
    {
        $dom->getElementById('ban-all-boards-row')->remove();
    }

    if (!empty($domain->id()))
    {
        $add_ban_form = $dom->getElementById('ban-board-field')->extSetAttribute('value', $domain->id());
    }

    if ($type === 'POST' && isset($_GET['post-id']))
    {
        $post_param = '&post-id=' . $_GET['post-id'];
    }
    else
    {
        $post_param = '';
        $dom->getElementById('ban-mod-comment')->remove();
    }

    $add_ban_form = $dom->getElementById('add-ban-form');
    $add_ban_form->extSetAttribute('action',
            MAIN_SCRIPT . '?module=board&module=bans&action=add&board_id=' . $domain->id() . $post_param);
    $ban_nodes = $add_ban_form->getElementsByAttributeName('data-parse-id', true);
    $ban_nodes['ban-ip']->extSetAttribute('value', $ip);
    $dom->getElementById('ban-type')->extSetAttribute('value', $type);
    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}

function nel_render_ban_panel_modify($user, \Nelliel\Domain $domain)
{
    $translator = new \Nelliel\Language\Translator();
    $ban_hammer = new \Nelliel\BanHammer(nel_database());
    $database = nel_database();
    $domain->renderInstance()->startRenderTimer();
    nel_render_general_header($domain, null,
            array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Bans')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/bans_panel_modify_ban.html');

    if (!$user->domainPermission($domain, 'perm_ban_modify'))
    {
        $dom->getElementById('ban-all-boards-field')->extSetAttribute('disabled', 'true');
    }

    $dom->getElementById('modify-ban-form')->extSetAttribute('action',
            MAIN_SCRIPT . '?module=board&module=bans&action=update&board_id=' . $domain->id());
    $ban_id = $_GET['ban_id'];
    $ban_info = $ban_hammer->getBanById($ban_id, true);
    $dom->getElementById('ban-ip-field')->extSetAttribute('value', @inet_ntop($ban_info['ip_address_start']));
    $dom->getElementById('ban-board-field')->setContent($ban_info['board_id']);
    $dom->getElementById('ban-type-display')->setContent($ban_info['type']);
    $dom->getElementById('ban-time-display')->setContent(date("D F jS Y  H:i:s", $ban_info['start_time']));
    $dom->getElementById('ban-expiration-display')->setContent(
            date("D F jS Y  H:i:s", $ban_info['length'] + $ban_info['start_time']));
    $dom->getElementById('ban-time-years')->extSetAttribute('value', $ban_info['years']);
    $dom->getElementById('ban-time-days')->extSetAttribute('value', $ban_info['days']);
    $dom->getElementById('ban-time-hours')->extSetAttribute('value', $ban_info['hours']);
    $dom->getElementById('ban-time-minutes')->extSetAttribute('value', $ban_info['minutes']);

    if (($ban_info['all_boards'] > 0))
    {
        $dom->getElementById('ban-all-boards-field')->extSetAttribute('checked', true);
    }

    $dom->getElementById('ban-id-field')->extSetAttribute('value', $ban_info['ban_id']);
    $dom->getElementById('ban-start-field')->extSetAttribute('value', $ban_info['start_time']);
    $dom->getElementById('ban-reason-field')->setContent($ban_info['reason']);
    $dom->getElementById('ban-name-display')->setContent($ban_info['creator']);

    if ($ban_info['appeal'] === '')
    {
        $dom->getElementById('ban-appeal-display-row')->remove();
    }
    else
    {
        $dom->getElementById('ban-appeal-display')->setContent($ban_info['appeal']);
    }

    if ($ban_info['appeal_response'] === '')
    {
        $dom->getElementById('ban-appeal-response-row')->remove();
    }
    else
    {
        $dom->getElementById('ban-appeal-response')->setContent($ban_info['appeal_response']);
    }

    if ($ban_info['appeal_status'] > 1)
    {
        $dom->getElementById('ban-appealed-field')->extSetAttribute('checked', 'checked');
    }

    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}