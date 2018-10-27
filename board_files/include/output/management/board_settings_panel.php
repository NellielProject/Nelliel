<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_board_settings_panel($board_id, $defaults)
{
    $dbh = nel_database();
    $authorization = new \Nelliel\Auth\Authorization($dbh);
    $language = new \Nelliel\language\Language($authorization);
    $filetypes = nel_parameters_and_data()->filetypeData();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/board_settings_panel.html');

    if ($defaults === true)
    {
        nel_render_general_header($render, null, null,
                array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Default Board Settings')));
        $result = $dbh->query('SELECT * FROM "' . BOARD_DEFAULTS_TABLE . '"');
        $dom->getElementById('board-settings-form')->extSetAttribute('action',
                PHP_SELF . '?module=default-board-settings&action=update');
    }
    else
    {
        $references = nel_parameters_and_data()->boardReferences($board_id);
        nel_render_general_header($render, null, $board_id,
                array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Board Settings')));
        $result = $dbh->query('SELECT * FROM "' . $references['config_table'] . '"');
        $dom->getElementById('board-settings-form')->extSetAttribute('action',
                PHP_SELF . '?module=board-settings&action=update&board_id=' . $board_id);
    }

    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    unset($result);

    foreach ($rows as $config_line)
    {
        if ($config_line['data_type'] === 'bool')
        {
            $config_element = $dom->getElementById($config_line['config_name']);

            if (is_null($config_element))
            {
                continue;
            }

            if ($config_line['config_type'] === 'filetype_enable')
            {
                foreach ($filetypes as $filetype)
                {
                    if ($filetype['format'] != $config_line['config_name'])
                    {
                        continue;
                    }

                    if ($filetype['extension'] == $filetype['parent_extension'])
                    {
                        $dom->getElementById('l_' . $filetype['format'])->addContent(
                                $filetype['label'] . ' - .' . $filetype['extension'], 'before');
                    }
                    else
                    {
                        $dom->getElementById('l_' . $filetype['format'])->addContent(', .' . $filetype['extension']);
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

    $language->i18nDom($dom, nel_parameters_and_data()->boardSettings($board_id, 'board_language'));
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}