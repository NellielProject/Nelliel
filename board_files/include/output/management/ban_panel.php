<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_main_ban_panel($user, $board_id)
{
    if (!$user->boardPerm('', 'perm_ban_access'))
    {
        nel_derp(341, _gettext('You are not allowed to access the bans panel.'));
    }

    $dbh = nel_database();
    $authorization = new \Nelliel\Auth\Authorization($dbh);
    $language = new \Nelliel\Language\Language($authorization);
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, $board_id,
            array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Bans')));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/bans_panel_main.html');

    $ban_list = $dbh->executeFetchAll('SELECT * FROM "' . BAN_TABLE . '" ORDER BY "ban_id" DESC', PDO::FETCH_ASSOC);
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
                PHP_SELF . '?module=board&module=bans&action=modify&ban_id=' . $ban_info['ban_id'] . '&board_id=' .
                $board_id);
        $ban_nodes['link-remove-ban']->extSetAttribute('href',
                PHP_SELF . '?module=board&module=bans&action=remove&ban_id=' . $ban_info['ban_id'] . '&board_id=' .
                $board_id);
        $ban_info_table->appendChild($temp_ban_info_row);
    }

    $ban_info_row->remove();

    $form_add_ban = $dom->getElementById('link-new-ban');
    $form_add_ban->extSetAttribute('href', PHP_SELF . '?module=board&module=bans&action=new&board_id=' . $board_id);
    $language->i18nDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}

function nel_render_ban_panel_add($board_id, $ip = '', $type = 'GENERAL')
{
    $authorize = new \Nelliel\Auth\Authorization(nel_database());
    $language = new \Nelliel\Language\Language($authorize);
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, $board_id,
            array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Bans')));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/bans_panel_add_ban.html');

    if (!empty($board_id))
    {
        $add_ban_form = $dom->getElementById('ban-board-field')->extSetAttribute('value', $board_id);
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
            PHP_SELF . '?module=board&module=bans&action=add&board_id=' . $board_id . $post_param);
    $ban_nodes = $add_ban_form->getElementsByAttributeName('data-parse-id', true);
    $ban_nodes['ban-ip']->extSetAttribute('value', $ip);
    $dom->getElementById('ban-type')->extSetAttribute('value', $type);
    $language->i18nDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}

function nel_render_ban_panel_modify($board_id)
{
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $language = new \Nelliel\Language\Language($authorization);
    $ban_hammer = new \Nelliel\BanHammer(nel_database(), $authorization);
    $dbh = nel_database();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, $board_id,
            array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Bans')));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/bans_panel_modify_ban.html');

    $dom->getElementById('modify-ban-form')->extSetAttribute('action',
            PHP_SELF . '?module=board&module=bans&action=update&board_id=' . $board_id);
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

    $language->i18nDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}