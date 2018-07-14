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
    $banned_board = ($ban_info['all_boards'] > 0) ? _gettext('All Boards') : $ban_info['board_id'];
    $ban_page_node_array = $dom->getAssociativeNodeArray('data-parse-id');
    $ban_page_node_array['banned-board']->setContent($banned_board);
    $ban_page_node_array['banned-time']->setContent(date("D F jS Y  H:i", $ban_info['start_time']));
    $ban_page_node_array['banned-reason']->setContent($ban_info['reason']);
    $ban_page_node_array['banned-length']->setContent(
            date("D F jS Y  H:i:s", $ban_info['length'] + $ban_info['start_time']));
    $ban_page_node_array['banned-ip']->setContent(@inet_ntop($ban_info['ip_address_start']));
    $appeal_form_element = $dom->getElementById('appeal-form');

    if ($ban_info['appeal_status'] == 0)
    {
       // $dom->getElementById('ban-ip')->extSetAttribute('value', @inet_ntop($ban_info['ip_address_start']));
        //$dom->getElementById('ban-board')->extSetAttribute('value', $banned_board);
        $appeal_form_element->extSetAttribute('action', PHP_SELF . '?module=ban-page&action=add-appeal');

        if(!empty($ban_info['board_id']))
        {
            $appeal_form_element->modifyAttribute('action', '&board-id=' . $ban_info['board_id'], 'after');
        }
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
            $ban_page_node_array['appeal-what-done']->setContent(
                    _gettext('You appeal has been reviewed. You cannot appeal again.'));
        }

        if ($ban_info['appeal_status'] == 3)
        {
            $ban_page_node_array['appeal-what-done']->setContent(
                    _gettext('Your appeal has been reviewed and the ban has been altered.'));
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

    nel_language()->i18nDom($dom, nel_parameters_and_data()->boardSettings($board_id, 'board_language'));
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render, $board_id, null, true);
    echo $render->outputRenderSet();
}
