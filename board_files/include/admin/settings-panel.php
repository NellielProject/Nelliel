<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_settings_control($dataforce)
{
    $dbh = nel_database();
    $authorize = nel_authorize();
    $mode = $dataforce['mode'];

    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_config_change'))
    {
        nel_derp(102, array('origin' => 'ADMIN'));
    }

    require_once INCLUDE_PATH . 'output/management/settings_panel.php';
    $update = FALSE;

    if ($mode === 'admin->settings->update')
    {
        // Apply settings from admin panel
        $dbh->query('UPDATE ' . CONFIG_TABLE . ' SET setting=""');

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

                $dbh->query('UPDATE "' . CONFIG_TABLE . '" SET setting=' . $item[1] . ' WHERE config_name=\'' . $item[0] . '\'');
            }
        }

        nel_regen_cache($dataforce);
        nel_regen_all_pages($dataforce);
    }

    nel_render_settings_panel($dataforce);
}
