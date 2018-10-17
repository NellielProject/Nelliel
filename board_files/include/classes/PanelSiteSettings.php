<?php

namespace Nelliel;

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

        if (!$user->boardPerm('', 'perm_manage_site_config'))
        {
            nel_derp(360, _gettext('You are not allowed to access the site settings.'));
        }

        if($inputs['action'] === 'update')
        {
            $this->add();
            $this->renderPanel();
        }
        else
        {
            $this->renderPanel();
        }
    }

    public function renderPanel()
    {
        nel_render_site_settings_panel();
    }

    public function add()
    {
    }

    public function edit()
    {
    }

    public function update()
    {
        while ($item = each($_POST))
        {
            $prepared = $this->database->prepare('UPDATE "nelliel_site_config" SET "setting" = ? WHERE "config_name" = ?');
            $this->database->executePrepared($prepared, array($item[1], $item[0]), true);
        }

        $regen = new \Nelliel\Regen();
        $regen->siteCache();
    }

    public function remove()
    {
    }


}
