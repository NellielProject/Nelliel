<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_board_settings_panel($board_id)
{
    $dbh = nel_database();
    $references = nel_board_references($board_id);
    require_once INCLUDE_PATH . 'post/filetypes.php';
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, $board_id, array('header' => 'MANAGE_BOARD',
        'sub_header' => 'MANAGE_SETTINGS'));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/board_settings_panel.html');
    $dom->getElementById('board-settings-form')->extSetAttribute('action', PHP_SELF .
         '?manage=board&module=board-settings&board_id=' . $board_id);
    $dom->getElementById('board_id_field')->extSetAttribute('value', $board_id);
    $result = $dbh->query('SELECT * FROM "' . $references['config_table'] . '"');
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    unset($result);

    foreach ($rows as $config_line)
    {
        if ($config_line['data_type'] === 'bool')
        {
            $config_element = $dom->getElementById($config_line['config_name']);

            if(is_null($config_element))
            {
                continue;
            }

            if ($config_line['config_type'] === 'filetype_enable')
            {
                foreach ($filetypes as $filetype)
                {
                    if ($filetype['format'] === $config_line['config_name'])
                    {
                        $dom->getElementById('l_' . $filetype['format'])->setContent($filetype['label']);
                    }
                }
            }

            if ($config_line['setting'] == 1)
            {
                $config_element->extSetAttribute('checked', 'true');
            }
        }
        else
        {
            // TODO: Can be simplified
            switch ($config_line['setting'])
            {
                case 'ISO':
                    $dom->getElementById('date_iso')->extSetAttribute('checked', 'true');
                    break;

                case 'COM':
                    $dom->getElementById('date_com')->extSetAttribute('checked', 'true');
                    break;

                case 'US':
                    $dom->getElementById('date_us')->extSetAttribute('checked', 'true');
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

    nel_process_i18n($dom, nel_board_settings($board_id, 'board_language'));
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    die();
}