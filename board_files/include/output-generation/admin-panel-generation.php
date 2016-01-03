<?php

function nel_render_admin_panel($dataforce, $dbh)
{
    nel_render_init(TRUE);
    $dat = '';
    $dat .= nel_render_header($dataforce, 'ADMIN', array());

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

    nel_render_multiple_in($board_settings);
    $dat .= nel_parse_template('admin_panel.tpl', 'management', '', FALSE);
    $dat .= nel_render_basic_footer();
    return $dat;
}
?>