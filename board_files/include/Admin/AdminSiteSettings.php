<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/site_settings_panel.php';

class AdminSiteSettings extends AdminHandler
{
    private $domain;

    function __construct($database, $authorization, $domain)
    {
        $this->database = $database;
        $this->authorization = $authorization;
        $this->domain = $domain;
    }

    public function actionDispatch($inputs)
    {
        $session = new \Nelliel\Session($this->authorization, true);
        $user = $session->sessionUser();

        if($inputs['action'] === 'update')
        {
            $this->update($user);
            $this->renderPanel($user);
        }
        else
        {
            $this->renderPanel($user);
        }
    }

    public function renderPanel($user)
    {
        nel_render_site_settings_panel($this->domain, $user);
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
        $regen->siteCache($this->domain);
    }

    public function remove($user)
    {
    }
}
