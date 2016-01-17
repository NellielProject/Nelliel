<?php

function nel_render_admin_panel($dataforce, $dbh)
{
    $render = new nel_render();
    $render->input(nel_render_header($dataforce, $render, array()));
    $result = $dbh->query('SELECT * FROM ' . CONFIGTABLE . '');
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    unset($result);
    $board_settings = array('iso' => '', 'com' => '', 'us' => '', 'archive' => '', 'prune' => '', 'nothing' => '');
    
    foreach ($rows as $config_line)
    {
        if ($config_line['config_type'] !== 'board_setting')
        {
            if ($config_line['setting'] === '1')
            {
                $board_settings[$config_line['config_name']] = 'checked';
            }
            else
            {
                $board_settings[$config_line['config_name']] = '';
            }
        }
        else if ($config_line['config_type'] === 'board_setting')
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
    
    $render->add_multiple_data($board_settings);
    $render->parse('admin_panel.tpl', 'management');
    $render->input(nel_render_basic_footer($render));
    echo $render->output();
}
?>