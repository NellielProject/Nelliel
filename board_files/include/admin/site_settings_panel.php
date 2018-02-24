<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/site_settings_panel.php';

function nel_site_settings_control($action, $dataforce)
{
    $dbh = nel_database();
    $authorize = nel_authorize();

    if ($action === 'update') // TODO: Set up perm for this
    {
        while ($item = each($_POST))
        {
            if ($item[0] !== 'action' && $item[0] !== 'username' && $item[0] !== 'super_sekrit')
            {
                $prepared = $dbh->prepare('UPDATE "nelliel_site_config" SET "setting" = ? WHERE "config_name" = ?');
                $dbh->executePrepared($prepared, array($item[1], $item[0]), true);
            }
        }

        nel_regen_cache(INPUT_BOARD_ID, $dataforce);
    }

    nel_render_site_settings_panel($dataforce);
}
