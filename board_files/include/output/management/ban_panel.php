<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}


function nel_render_main_ban_panel($dataforce)
{
    $dbh = nel_database();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_header($dataforce, $render, array());
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/bans_panel_main.html');
    $dotdot = isset($dataforce['dotdot']) ? $dataforce['dotdot'] : '';

    $result =  $dbh->query('SELECT * FROM "' . BAN_TABLE . '" ORDER BY "ban_id" DESC');
    $ban_info_table = $dom->getElementById('ban-info-table');
    $ban_info_row = $dom->getElementById('ban-info-row');
    $i = 0;

    while ($result && $baninfo = $result->fetch(PDO::FETCH_ASSOC))
    {
        if($i & 1)
        {
            $bgclass = 'row1';
        }
        else
        {
            $bgclass = 'row2';
        }

        $temp_ban_info_row = $ban_info_row->cloneNode(true);
        $temp_ban_info_row->extSetAttribute('class', $bgclass);
        $ban_info_td_list = $temp_ban_info_row->doXPathQuery(".//td");
        $ban_info_td_list->item(0)->setContent($baninfo['ban_id']);
        $ban_info_td_list->item(1)->setContent($baninfo['type']);
        $ban_info_td_list->item(2)->setContent($baninfo['ip_address'] ? $baninfo['ip_address']: 'Unknown');
        $ban_info_td_list->item(3)->setContent($baninfo['name']);
        $ban_info_td_list->item(4)->setContent($baninfo['reason']);
        $ban_info_td_list->item(5)->setContent(date("D F jS Y  H:i:s", $baninfo['length'] + $baninfo['ban_time']));
        $ban_info_td_list->item(6)->setContent($baninfo['appeal']);
        $ban_info_td_list->item(7)->setContent($baninfo['appeal_response']);
        $ban_info_td_list->item(8)->setContent($baninfo['appeal_status']);

        $form_mod_ban = $temp_ban_info_row->getElementById('form-mod-ban-');
        $form_mod_ban->extSetAttribute('action', $dotdot . PHP_SELF);
        $form_mod_ban->changeId('form-mod-ban-' . $baninfo['ban_id']);
        $form_mod_ban->doXPathQuery(".//input[@name='banid']")->item(0)->extSetAttribute('value', $baninfo['ban_id']);

        $form_remove_ban = $temp_ban_info_row->getElementById('form-remove-ban-');
        $form_remove_ban->extSetAttribute('action', $dotdot . PHP_SELF);
        $form_remove_ban->changeId('form-remove-ban-' . $baninfo['ban_id']);
        $form_remove_ban->doXPathQuery(".//input[@name='banid']")->item(0)->extSetAttribute('value', $baninfo['ban_id']);

        $ban_info_table->appendChild($temp_ban_info_row);
        $i++;
    }

    unset($result);
    $ban_info_row->removeSelf();

    $form_add_ban = $dom->getElementById('form-add-ban');
    $form_add_ban->extSetAttribute('action', $dotdot . PHP_SELF);

    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_footer($render, false);
    echo $render->outputRenderSet();
}

function nel_render_ban_panel_add($dataforce)
{
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_header($dataforce, $render, array());
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/bans_panel_add_ban.html');
    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_footer($render, false);
    echo $render->outputRenderSet();
}

function nel_render_ban_panel_modify($dataforce)
{
    $dbh = nel_database();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_header($dataforce, $render, array());
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/bans_panel_modify_ban.html');

    $result =  $dbh->query('SELECT * FROM ' . BAN_TABLE . ' WHERE ban_id=' . $dataforce['banid'] . '');
    $baninfo = $result->fetch(PDO::FETCH_ASSOC);
    unset($result);

    $dom->getElementById('ban-time-display')->setContent(date("D F jS Y  H:i:s", $baninfo['ban_time']));
    $dom->getElementById('ban-expiration-display')->setContent(date("D F jS Y  H:i:s", $baninfo['length'] + $baninfo['ban_time']));
    $length2 = $baninfo['length'] / 3600;

    if ($length2 >= 24)
    {
        $length2 = $length2 / 24;
        $dom->getElementById('ban-length-days')->extSetAttribute('value', floor($length2));
        $length2 = $length2 - floor($length2);
        $dom->getElementById('ban-length-hours')->extSetAttribute('value', floor($length2 * 24));
    }

    $dom->getElementById('ban-name-display')->setContent($baninfo['name']);
    $dom->getElementById('ban-id-field')->extSetAttribute('value', $baninfo['ban_id']);
    $dom->getElementById('ban-length-field')->extSetAttribute('value', $baninfo['length']);
    $dom->getElementById('ban-reason-field')->setContent($baninfo['reason']);

    if ($baninfo['appeal'] === '')
    {
        $dom->getElementById('ban-appeal-display-row')->removeSelf();
    }
    else
    {
        $dom->getElementById('ban-appeal-display')->setContent($baninfo['appeal']);
    }

    if ($baninfo['appeal_status'] > 1)
    {
        $dom->getElementById('ban-appealed-field')->extSetAttribute('checked', 'checked');
    }

    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_footer($render, false);
    echo $render->outputRenderSet();
}