<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/site_settings_panel.php';

function nel_site_settings_control($inputs)
{
    $dbh = nel_database();
    $authorize = nel_authorize();
    $user = $authorize->getUser($_SESSION['username']);

    if (!$user->boardPerm('', 'perm_manage_site_config'))
    {
        nel_derp(360, _gettext('You are not allowed to access the site settings.'));
    }

    if ($inputs['action'] === 'update')
    {
        while ($item = each($_POST))
        {
            $prepared = $dbh->prepare('UPDATE "nelliel_site_config" SET "setting" = ? WHERE "config_name" = ?');
            $dbh->executePrepared($prepared, array($item[1], $item[0]), true);
        }

        $regen = new \Nelliel\Regen();
        $regen->siteCache();
    }

    nel_render_site_settings_panel();
}
