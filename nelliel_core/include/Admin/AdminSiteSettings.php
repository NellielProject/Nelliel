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
        $this->validateUser();
    }

    public function actionDispatch(string $action, bool $return)
    {
        if($action === 'update')
        {
            $this->update();
        }

        if ($return)
        {
            return;
        }

        $this->renderPanel();
    }

    public function renderPanel()
    {
        $output_panel = new \Nelliel\Output\OutputPanelSiteSettings($this->domain, false);
        $output_panel->render(['user' => $this->session_user], false);
    }

    public function creator()
    {
    }

    public function add()
    {
    }

    public function editor()
    {
    }

    public function update()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_site_config'))
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

    public function remove()
    {
    }
}
