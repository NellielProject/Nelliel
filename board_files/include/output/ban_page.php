<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_ban_page($board_id, $dataforce, $ban_info)
{
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_board_header($board_id, $render);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'ban_page.html');
    $dotdot = isset($dataforce['dotdot']) ? $dataforce['dotdot'] : '../';
    $banned_board = ($ban_info['all_boards'] > 0) ? 'All Boards' : $ban_info['board'];
    $dom->getElementById('banned-board')->setContent($banned_board);
    $dom->getElementById('banned-time')->setContent(date("D F jS Y  H:i", $ban_info['start_time']));
    $dom->getElementById('banned-reason')->setContent($ban_info['reason']);
    $dom->getElementById('banned-length')->setContent(date("D F jS Y  H:i:s", $ban_info['length'] + $ban_info['start_time']));
    $dom->getElementById('banned-ip')->setContent(@inet_ntop($ban_info['ip_address_start']));
    $appeal_form_element = $dom->getElementById('appeal-form');

    if ($ban_info['appeal_status'] == 0)
    {
        $appeal_form_element->extSetAttribute('action', $dotdot . PHP_SELF);
        $appeal_form_element->doXPathQuery(".//input[@name='ban_ip']")->item(0)->extSetAttribute('value', @inet_ntop($ban_info['ip_address_start']));
        $appeal_form_element->doXPathQuery(".//input[@name='ban_board']")->item(0)->extSetAttribute('value', $ban_info['board']);
    }
    else
    {
        $appeal_form_element->removeSelf();
    }

    if ($ban_info['appeal_status'] != 1)
    {
        $dom->getElementById('appeal-pending')->removeSelf();
    }

    if ($ban_info['appeal_status'] != 2 && $ban_info['appeal_status'] != 3)
    {
        $dom->getElementById('appeal-response-div')->removeSelf();
    }
    else
    {
        if ($ban_info['appeal_status'] == 2)
        {
            $dom->getElementById('appeal-what-done')->setContent(nel_stext('APPEAL_REVIEWED'));
        }

        if ($ban_info['appeal_status'] == 3)
        {
            $dom->getElementById('appeal-what-done')->setContent(nel_stext('BAN_ALTERED'));
        }

        if ($ban_info['appeal_response'] != '')
        {
            $dom->getElementById('appeal-response-text')->setContent($ban_info['appeal_response']);
        }
        else
        {
            $dom->getElementById('appeal-response-text')->setContent(nel_stext('BAN_NO_RESPONSE'));
        }
    }

    nel_process_i18n($dom, nel_board_settings($board_id, 'board_language'));
    $render->appendHTMLFromDOM($dom);
    nel_render_board_footer($board_id, $render, true);
    echo $render->outputRenderSet();
}
