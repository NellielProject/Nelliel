<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_ban_page($board_id, $ban_info)
{
    require_once INCLUDE_PATH . 'output/header.php';
    require_once INCLUDE_PATH . 'output/footer.php';
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_board_header($board_id, $render);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'ban_page.html');
    $banned_board = ($ban_info['all_boards'] > 0) ? 'All Boards' : $ban_info['board'];
    $ban_page_node_array = $dom->getAssociativeNodeArray('data-parse-id');
    $ban_page_node_array['banned-board']->setContent($banned_board);
    $ban_page_node_array['banned-time']->setContent(date("D F jS Y  H:i", $ban_info['start_time']));
    $ban_page_node_array['banned-reason']->setContent($ban_info['reason']);
    $ban_page_node_array['banned-length']->setContent(date("D F jS Y  H:i:s", $ban_info['length'] + $ban_info['start_time']));
    $ban_page_node_array['banned-ip']->setContent(@inet_ntop($ban_info['ip_address_start']));
    $appeal_form_element = $dom->getElementById('appeal-form');

    if ($ban_info['appeal_status'] == 0)
    {
        $appeal_form_element->extSetAttribute('action', PHP_SELF . '?module=ban-page');
        $appeal_form_element->doXPathQuery(".//input[@name='ban_ip']")->item(0)->extSetAttribute('value', @inet_ntop($ban_info['ip_address_start']));
        $appeal_form_element->doXPathQuery(".//input[@name='ban_board']")->item(0)->extSetAttribute('value', $banned_board);
    }
    else
    {
        $appeal_form_element->removeSelf();
    }

    if ($ban_info['appeal_status'] != 1)
    {
        $ban_page_node_array['appeal-pending']->removeSelf();
    }

    if ($ban_info['appeal_status'] != 2 && $ban_info['appeal_status'] != 3)
    {
        $dom->getElementById('appeal-response-div')->removeSelf();
    }
    else
    {
        if ($ban_info['appeal_status'] == 2)
        {
            $ban_page_node_array['appeal-what-done']->setContent(_gettext('You appeal has been reviewed. You cannot appeal again.'));
        }

        if ($ban_info['appeal_status'] == 3)
        {
            $ban_page_node_array['appeal-what-done']->setContent(_gettext('Your appeal has been reviewed and the ban has been altered.'));
        }

        if ($ban_info['appeal_response'] != '')
        {
            $ban_page_node_array['appeal-response-text']->setContent($ban_info['appeal_response']);
        }
        else
        {
            $ban_page_node_array['appeal-response-text']->setContent(_gettext('No response has been given.'));
        }
    }

    nel_process_i18n($dom, nel_board_settings($board_id, 'board_language'));
    $render->appendHTMLFromDOM($dom);
    nel_render_board_footer($board_id, $render, null, true);
    echo $render->outputRenderSet();
}
