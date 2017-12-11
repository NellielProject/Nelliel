<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_ban_page($dataforce, $bandata)
{
    $render = new nel_render();
    nel_render_header($dataforce, $render, array());
    $render1 = new NellielTemplates\RenderCore();
    $dom = $render1->newDOMDocument();
    $render1->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $dom->loadTemplateFromFile('ban_page.html');
    $xpath = new DOMXPath($dom);
    $dotdot = isset($dataforce['dotdot']) ? $dataforce['dotdot'] : '';
    $ip_address = ($bandata['ip_address']) ? $bandata['ip_address'] : 'Unknown';
    $dom->getElementById('banned-board')->setContent($bandata['board']);
    $dom->getElementById('banned-time')->setContent(date("D F jS Y  H:i", $bandata['ban_time']));
    $dom->getElementById('banned-reason')->setContent($bandata['reason']);
    $dom->getElementById('banned-length')->setContent(date("D F jS Y  H:i", $bandata['length_base']));
    $dom->getElementById('banned-ip')->setContent($ip_address);
    $dom->getElementById('banned-name')->setContent($bandata['name']);
    $appeal_form_element = $dom->getElementById('appeal-form');

    if ($bandata['appeal_status'] === 0)
    {
        $appeal_form_element->extSetAttribute('action', $dotdot . PHP_SELF);
        $xpath->query(".//input[@name='banned_ip']")->item(0)->extSetAttribute('value', $ip_address);
        $xpath->query(".//input[@name='banned_board']")->item(0)->extSetAttribute('value', $bandata['board']);
    }
    else
    {
        $appeal_form_element->removeSelf();
    }

    if ($bandata['appeal_status'] != 1)
    {
        $dom->getElementById('appeal-pending')->removeSelf();
    }

    if ($bandata['appeal_status'] != 2 && $bandata['appeal_status'] != 3)
    {
        $dom->getElementById('appeal-response-div')->removeSelf();
    }
    else
    {
        if ($bandata['appeal_status'] == 2)
        {
            $dom->getElementById('appeal-what-done')->setContent(nel_stext('APPEAL_REVIEWED'));
        }

        if ($bandata['appeal_status'] == 3)
        {
            $dom->getElementById('appeal-what-done')->setContent(nel_stext('BAN_ALTERED'));
        }

        if ($bandata['appeal_response'] != '')
        {
            $dom->getElementById('appeal-response-text')->setContent($bandata['appeal_response']);
        }
        else
        {
            $dom->getElementById('appeal-response-text')->setContent(nel_stext('BAN_NO_RESPONSE'));
        }
    }

    nel_process_i18n($dom);
    $render->appendOutput($dom->outputHTML());
    nel_render_footer($render, false);
    $render->output(true);
}
