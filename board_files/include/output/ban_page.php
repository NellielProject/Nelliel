<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_ban_page($board_id, $ban_info)
{
    require_once INCLUDE_PATH . 'output/header.php';
    require_once INCLUDE_PATH . 'output/footer.php';
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $language = new \Nelliel\Language\Language($authorization);
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_board_header($board_id, $render);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'ban_page.html');
    $banned_board = ($ban_info['all_boards'] > 0) ? _gettext('All Boards') : $ban_info['board_id'];
    $ban_page_node_array = $dom->getElementsByAttributeName('data-parse-id', true);
    $ban_page_node_array['banned-board']->setContent($banned_board);
    $ban_page_node_array['banned-time']->setContent(date("F jS, Y H:i e", $ban_info['start_time']));
    $ban_expire = $ban_info['length'] + $ban_info['start_time'];
    $dt = new DateTime();
    $dt->add(new DateInterval('PT' . ($ban_expire - time()) . 'S'));
    $interval = $dt->diff(new DateTime());

    if ($interval->d >= 1)
    {
        $duration = $interval->format('%d days %h hours');
    }
    else if ($interval->h >= 1)
    {
        $duration = $interval->format('%h hours %i minutes');
    }
    else
    {
        $duration = $interval->format('%i minutes');
    }

    $ban_page_node_array['banned-length']->setContent($duration);
    $ban_page_node_array['banned-expire']->setContent(date("F jS, Y H:i e", $ban_expire));
    $ban_page_node_array['banned-reason']->setContent($ban_info['reason']);
    $ban_page_node_array['banned-ip']->setContent($_SERVER['REMOTE_ADDR']);
    $appeal_form_element = $dom->getElementById('appeal-form');

    if ($ban_info['appeal_status'] == 0)
    {
        $appeal_form_element->extSetAttribute('action', PHP_SELF . '?module=ban-page&action=add-appeal');

        if (!empty($ban_info['board_id']))
        {
            $appeal_form_element->modifyAttribute('action', '&board-id=' . $ban_info['board_id'], 'after');
        }
    }
    else
    {
        $appeal_form_element->remove();
    }

    if ($ban_info['appeal_status'] != 1)
    {
        $ban_page_node_array['appeal-pending']->remove();
    }

    if ($ban_info['appeal_status'] != 2 && $ban_info['appeal_status'] != 3)
    {
        $dom->getElementById('appeal-response-div')->remove();
    }
    else
    {
        if ($ban_info['appeal_status'] == 2)
        {
            $ban_page_node_array['appeal-what-done']->setContent(
                    _gettext('You appeal has been reviewed and denied. You cannot appeal again.'));
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

    $language->i18nDom($dom, nel_parameters_and_data()->boardSettings($board_id, 'board_language'));
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render, $board_id, null, true);
    echo $render->outputRenderSet();
}
