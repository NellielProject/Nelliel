<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/board_settings_panel.php';

function nel_board_settings_control($inputs, $defaults = false)
{
    $dbh = nel_database();
    $authorize = nel_authorize();
    $board_id = $inputs['board_id'];

    if ($defaults && !$authorize->getUserPerm($_SESSION['username'], 'perm_manage_board_defaults'))
    {
        nel_derp(332, _gettext('You are not allowed to modify the default board settings.'));
    }

    if (!$authorize->getUserPerm($_SESSION['username'], 'perm_manage_board_config', $board_id))
    {
        nel_derp(330, _gettext('You are not allowed to modify the board settings.'));
    }

    if ($inputs['action'] === 'update')
    {
        $references = nel_parameters_and_data()->boardReferences($board_id);
        $config_table = ($defaults) ? BOARD_DEFAULTS_TABLE : $references['config_table'];

        while ($item = each($_POST))
        {
            if ($item[0] === 'jpeg_quality' && $item[1] > 100)
            {
                $item[0] = 100;
            }

            $prepared = $dbh->prepare('UPDATE "' . $config_table . '" SET "setting" = ? WHERE "config_name" = ?');
            $dbh->executePrepared($prepared, array($item[1], $item[0]), true);
        }

        if (!$defaults)
        {
            $regen = new \Nelliel\Regen();
            $regen->boardCache($board_id);
            $regen->allPages($board_id);
        }
    }

    nel_render_board_settings_panel($board_id, $defaults);
}
