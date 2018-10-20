<?php

namespace Nelliel\Panels;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/site_settings_panel.php';

class PanelSiteSettings extends PanelBase
{
    function __construct($database, $authorize)
    {
        $this->database = $database;
        $this->authorize = $authorize;
    }

    public function actionDispatch($inputs)
    {
        $user = $this->authorize->getUser($_SESSION['username']);

        if($inputs['action'] === 'update')
        {
            $this->add($user);
            $this->renderPanel($user);
        }
        else
        {
            $this->renderPanel($user);
        }
    }

    public function renderPanel($user)
    {
        if (!$user->boardPerm('', 'perm_site_config_access'))
        {
            nel_derp(360, _gettext('You are not allowed to access the site settings.'));
        }

        nel_render_site_settings_panel();
    }

    public function creator($user)
    {
    }

    public function add($user)
    {
    }

    public function editor($user)
    {
    }

    public function update($user)
    {
        if (!$user->boardPerm('', 'perm_site_config_modify'))
        {
            nel_derp(361, _gettext('You are not allowed to modify the site settings.'));
        }

        while ($item = each($_POST))
        {
            $prepared = $this->database->prepare('UPDATE "nelliel_site_config" SET "setting" = ? WHERE "config_name" = ?');
            $this->database->executePrepared($prepared, array($item[1], $item[0]), true);
        }

        $regen = new \Nelliel\Regen();
        $regen->siteCache();
    }

    public function remove($user)
    {
    }


}
