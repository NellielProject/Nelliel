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

    while ($result && $ban_info = $result->fetch(PDO::FETCH_ASSOC))
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
        $ban_info_td_list->item(0)->setContent($ban_info['ban_id']);
        $ban_info_td_list->item(1)->setContent($ban_info['type']);
        $ban_info_td_list->item(2)->setContent($ban_info['ip_address'] ? $ban_info['ip_address']: 'Unknown');
        $ban_info_td_list->item(3)->setContent($ban_info['board']);
        $ban_info_td_list->item(4)->setContent($ban_info['reason']);
        $ban_info_td_list->item(5)->setContent(date("D F jS Y  H:i:s", $ban_info['length'] + $ban_info['start_time']));
        $ban_info_td_list->item(6)->setContent($ban_info['appeal']);
        $ban_info_td_list->item(7)->setContent($ban_info['appeal_response']);
        $ban_info_td_list->item(8)->setContent($ban_info['appeal_status']);

        $form_mod_ban = $temp_ban_info_row->getElementById('form-mod-ban-');
        $form_mod_ban->extSetAttribute('action', $dotdot . PHP_SELF);
        $form_mod_ban->changeId('form-mod-ban-' . $ban_info['ban_id']);
        $form_mod_ban->doXPathQuery(".//input[@name='ban_id']")->item(0)->extSetAttribute('value', $ban_info['ban_id']);

        $form_remove_ban = $temp_ban_info_row->getElementById('form-remove-ban-');
        $form_remove_ban->extSetAttribute('action', $dotdot . PHP_SELF);
        $form_remove_ban->changeId('form-remove-ban-' . $ban_info['ban_id']);
        $form_remove_ban->doXPathQuery(".//input[@name='ban_id']")->item(0)->extSetAttribute('value', $ban_info['ban_id']);

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
    $ban_hammer = nel_ban_hammer();
    $dbh = nel_database();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_header($dataforce, $render, array());
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/bans_panel_modify_ban.html');

    $ban_info = $ban_hammer->getBan($_POST['ban_id'], true);
    $dom->getElementById('ban-ip-field')->extSetAttribute('value', $ban_info['ip_address']);
    $dom->getElementById('ban-time-display')->setContent(date("D F jS Y  H:i:s", $ban_info['start_time']));
    $dom->getElementById('ban-expiration-display')->setContent(date("D F jS Y  H:i:s", $ban_info['length'] + $ban_info['start_time']));
    $dom->getElementById('ban-time-years')->extSetAttribute('value', $ban_info['years']);
    $dom->getElementById('ban-time-months')->extSetAttribute('value', $ban_info['months']);
    $dom->getElementById('ban-time-days')->extSetAttribute('value', $ban_info['days']);
    $dom->getElementById('ban-time-hours')->extSetAttribute('value', $ban_info['hours']);
    $dom->getElementById('ban-time-minutes')->extSetAttribute('value', $ban_info['minutes']);
    $dom->getElementById('ban-time-seconds')->extSetAttribute('value', $ban_info['seconds']);
    $dom->getElementById('ban-id-field')->extSetAttribute('value', $ban_info['ban_id']);
    $dom->getElementById('ban-start-field')->extSetAttribute('value', $ban_info['start_time']);
    $dom->getElementById('ban-reason-field')->setContent($ban_info['reason']);

    if ($ban_info['appeal'] === '')
    {
        $dom->getElementById('ban-appeal-display-row')->removeSelf();
    }
    else
    {
        $dom->getElementById('ban-appeal-display')->setContent($ban_info['appeal']);
    }

    if ($ban_info['appeal_response'] === '')
    {
        $dom->getElementById('ban-appeal-response-row')->removeSelf();
    }
    else
    {
        $dom->getElementById('ban-appeal-response')->setContent($ban_info['appeal_response']);
    }

    if ($ban_info['appeal_status'] > 1)
    {
        $dom->getElementById('ban-appealed-field')->extSetAttribute('checked', 'checked');
    }

    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_footer($render, false);
    echo $render->outputRenderSet();
}