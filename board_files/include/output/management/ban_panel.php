<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}


function nel_render_main_ban_panel($dataforce)
{
    $dbh = nel_database();
    $render = new nel_render();
    nel_render_header($dataforce, $render, array());
    $render1 = new NellielTemplates\RenderCore();
    $dom = $render1->newDOMDocument();
    $render1->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $dom->loadTemplateFromFile('management/bans_panel.html');
    $xpath = new DOMXPath($dom);
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
        $ban_info_td_list = $xpath->query(".//td", $temp_ban_info_row);
        $temp_ban_info_row->item(0)->setContent($baninfo['ban_id']);
        $temp_ban_info_row->item(1)->setContent($baninfo['type']);
        $temp_ban_info_row->item(2)->setContent($baninfo['ip_address'] ? $baninfo['ip_address']: 'Unknown');
        $temp_ban_info_row->item(3)->setContent($baninfo['name']);
        $temp_ban_info_row->item(4)->setContent($baninfo['reason']);
        $temp_ban_info_row->item(5)->setContent(date("D F jS Y  H:i:s", $baninfo['length'] + $baninfo['ban_time']));
        $temp_ban_info_row->item(6)->setContent($baninfo['appeal']);
        $temp_ban_info_row->item(7)->setContent($baninfo['appeal_response']);
        $temp_ban_info_row->item(8)->setContent($baninfo['appeal_status']);

        $form_mod_ban = $dom->getElementById('form-mod-ban-');
        $form_mod_ban->extSetAttribute('action', $dotdot . PHP_SELF);
        $form_mod_ban->changeId('form_mod_ban-' . $baninfo['ban_id']);
        $xpath->query(".//input[@name='banid']", $form_mod_ban)->item(0)->extSetAttribute('value', $baninfo['ban_id']);

        $form_remove_ban = $dom->getElementById('form-remove-ban-');
        $form_remove_ban->extSetAttribute('action', $dotdot . PHP_SELF);
        $form_remove_ban->changeId('form_mod_ban-' . $baninfo['ban_id']);
        $xpath->query(".//input[@name='banid']", $form_remove_ban)->item(0)->extSetAttribute('value', $baninfo['ban_id']);

        $ban_info_table->appendChild($temp_ban_info_row);
        $i++;
    }

    $ban_info_row->removeSelf();

    $form_add_ban = $dom->getElementById('form-add-ban');
    $form_add_ban->extSetAttribute('action', $dotdot . PHP_SELF);

    nel_process_i18n($dom);
    $render->appendOutput($dom->outputHTML());
    nel_render_footer($render, false);
    $render->output(true);
}