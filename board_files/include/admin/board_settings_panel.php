<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/board_settings_panel.php';

function nel_board_settings_control($board_id, $action, $defaults = false)
{
    $dbh = nel_database();
    $authorize = nel_authorize();
    $references = nel_board_references($board_id);
    $config_table = ($defaults) ? DEFAULT_BOARD_CONFIG_TABLE : $references['config_table'];
    $update = FALSE;

    if ($action === 'update')
    {
        if (!$authorize->get_user_perm($_SESSION['username'], 'perm_config_modify', $board_id))
        {
            nel_derp(331, nel_stext('ERROR_331'));
        }

        while ($item = each($_POST))
        {
            if ($item[0] !== 'action' && $item[0] !== 'board_id')
            {
                if ($item[0] === 'jpeg_quality' && $item[1] > 100)
                {
                    $item[0] = 100;
                }

                $prepared = $dbh->prepare('UPDATE "' . $config_table . '" SET "setting" = ? WHERE "config_name" = ?');
                $dbh->executePrepared($prepared, array($item[1], $item[0]), true);
            }
        }

        if(!$defaults)
        {
            nel_regen_board_cache($board_id);
            nel_regen_all_pages($board_id);
        }
    }

    nel_render_board_settings_panel($board_id, $defaults);
}
