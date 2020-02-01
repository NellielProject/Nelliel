<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;

class AdminSiteSettings extends AdminHandler
{

    function __construct(Authorization $authorization, Domain $domain)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
    }

    public function actionDispatch($inputs)
    {
        $session = new \Nelliel\Account\Session();
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
        $output_panel = new \Nelliel\Output\OutputPanelSiteSettings($this->domain);
        $output_panel->render(['user' => $user], false);
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
        if (!$user->domainPermission($this->domain, 'perm_site_config'))
        {
            nel_derp(361, _gettext('You are not allowed to modify the site settings.'));
        }

        while ($item = each($_POST))
        {
            $prepared = $this->database->prepare('UPDATE "nelliel_site_config" SET "setting" = ? WHERE "config_name" = ?');
            $this->database->executePrepared($prepared, [$item[1], $item[0]], true);
        }

        $regen = new \Nelliel\Regen();
        $regen->siteCache($this->domain);
    }

    public function remove($user)
    {
    }
}
