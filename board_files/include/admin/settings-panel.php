<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/settings_panel.php';

function nel_settings_control($dataforce)
{
    $dbh = nel_database();
    $authorize = nel_authorize();
    $mode = $dataforce['mode'];

    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_config_modify', CONF_BOARD_DIR) &&
         !$authorize->get_user_perm($_SESSION['username'], 'perm_all_config_modify'))
    {
        nel_derp(331, nel_stext('ERROR_331'));
    }

    $update = FALSE;

    if ($mode === 'admin->settings->update')
    {
        while ($item = each($_POST))
        {
            if ($item[0] !== 'mode' && $item[0] !== 'username' && $item[0] !== 'super_sekrit')
            {
                if ($item[0] === 'jpeg_quality' && $item[1] > 100)
                {
                    $item[0] = 100;
                }

                if ($item[0] === 'page_limit')
                {
                    $dataforce['max_pages'] = (int) $item[1];
                }

                $prepared = $dbh->prepare('UPDATE "' . CONFIG_TABLE . '" SET "setting" = ? WHERE "config_name" = ?');
                $dbh->executePrepared($prepared, array($item[1], $item[0]), true);
            }
        }

        nel_regen_cache($dataforce);
        nel_regen_all_pages($dataforce);
    }

    nel_render_settings_panel($dataforce);
}
