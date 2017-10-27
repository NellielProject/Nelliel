<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_settings_panel($dataforce)
{
    $dbh = nel_database();
    $render = new nel_render();
    $render->input(nel_render_header($dataforce, $render, array()));
    $result =  $dbh->query('SELECT * FROM ' . CONFIG_TABLE . '');
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    unset($result);
    $board_settings = array('iso' => '', 'com' => '', 'us' => '', 'archive' => '', 'prune' => '', 'nothing' => '');

    foreach ($rows as $config_line)
    {
        if ($config_line['config_type'] === 'board_setting')
        {
            if ($config_line['data_type'] === 'bool')
            {
                $board_settings[$config_line['config_name']] = $config_line['setting'] === '1' ? 'checked' : '';
            }
            else
            {
                switch ($config_line['setting'])
                {
                    case 'ISO':
                        $board_settings['iso'] = 'checked';
                        break;

                    case 'COM':
                        $board_settings['com'] = 'checked';
                        break;

                    case 'US':
                        $board_settings['us'] = 'checked';
                        break;

                    case 'ARCHIVE':
                        $board_settings['archive'] = 'checked';
                        break;

                    case 'PRUNE':
                        $board_settings['prune'] = 'checked';
                        break;

                    case 'NOTHING':
                        $board_settings['nothing'] = 'checked';
                        break;

                    default:
                        $board_settings[$config_line['config_name']] = $config_line['setting'];
                }
            }
        }
        else if (substr($config_line['config_type'], 0, 14) === 'filetype_allow')
        {
            if ($config_line['data_type'] === 'bool')
            {
                $board_settings[$config_line['config_name']] = $config_line['setting'] === '1' ? 'checked' : '';
            }
        }
    }

    $render->add_multiple_data($board_settings);
    $render->parse('settings_panel.tpl', 'management');
    $render->input(nel_render_basic_footer($render));
    $render->output(TRUE);
}
