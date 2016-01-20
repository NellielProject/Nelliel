<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_settings_control($dataforce, $authorize, $dbh)
{
    $mode = $dataforce['mode_action'];

    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_config'))
    {
        nel_derp(102, array('origin' => 'ADMIN'));
    }

    require_once INCLUDE_PATH . 'output/admin-panel-generation.php';
    $update = FALSE;

    if ($mode === 'update')
    {
        // Apply settings from admin panel
        $dbh->query('UPDATE ' . CONFIGTABLE . ' SET setting=""');

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

                $dbh->query('UPDATE ' . CONFIGTABLE . ' SET setting="' . $item[1] . '" WHERE config_name="' . $item[0] . '"');
            }
        }

        nel_cache_rules($dbh);
        nel_cache_settings($dbh);
        nel_regen($dataforce, NULL, 'full', FALSE, $dbh);
    }

    nel_render_admin_panel($dataforce, $dbh);
}
?>