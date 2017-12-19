<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_settings_panel($dataforce)
{
    $dbh = nel_database();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_header($dataforce, $render, array());
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/settings_panel.html');
    $result = $dbh->query('SELECT * FROM ' . CONFIG_TABLE . '');
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    unset($result);
    $board_settings = array('iso' => '', 'com' => '', 'us' => '', 'archive' => '', 'prune' => '', 'nothing' => '');

    foreach ($rows as $config_line)
    {
        if ($config_line['data_type'] === 'bool')
        {
            $config_element = $dom->getElementById($config_line['config_name']);

            if (!is_null($config_element) && $config_line['setting'] == 1)
            {
                $config_element->extSetAttribute('checked', 'true');
            }
        }
        else
        {
            switch ($config_line['setting'])
            {
                case 'ISO':
                    $dom->getElementById('iso')->extSetAttribute('checked', 'true');
                    break;

                case 'COM':
                    $dom->getElementById('com')->extSetAttribute('checked', 'true');
                    break;

                case 'US':
                    $dom->getElementById('us')->extSetAttribute('checked', 'true');
                    break;

                case 'ARCHIVE':
                    $dom->getElementById('old_threads_a')->extSetAttribute('checked', 'true');
                    break;

                case 'PRUNE':
                    $dom->getElementById('old_threads_p')->extSetAttribute('checked', 'true');
                    break;

                case 'NOTHING':
                    $dom->getElementById('old_threads_n')->extSetAttribute('checked', 'true');
                    break;

                default:
                    $config_element = $dom->getElementById($config_line['config_name']);

                    if (!is_null($config_element))
                    {
                        $config_element->extSetAttribute('value', $config_line['setting']);
                    }
            }
        }
    }

    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_footer($render, false);
    echo $render->outputRenderSet();
}