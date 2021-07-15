<?php

declare(strict_types=1);

namespace Nelliel\Modules\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Modules\Account\Session;
use Nelliel\Auth\Authorization;

class AdminSiteSettings extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function renderPanel()
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Render\OutputPanelSiteSettings($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator()
    {
        $this->verifyAccess($this->domain);
    }

    public function add()
    {
        $this->verifyAction($this->domain);
    }

    public function editor()
    {
        $this->verifyAccess($this->domain);
    }

    public function update()
    {
        $this->verifyAction($this->domain);

        foreach ($_POST as $key => $value)
        {
            if (is_array($value))
            {
                $value = nel_form_input_default($value);
            }

            $prepared = $this->database->prepare(
                    'UPDATE "' . NEL_SITE_CONFIG_TABLE . '" SET "setting_value" = ? WHERE "setting_name" = ?');
            $this->database->executePrepared($prepared, [$value, $key], true);
        }

        $this->domain->regenCache();
        $this->domain->reload();
        $this->outputMain(true);
    }

    public function remove()
    {
        $this->verifyAction($this->domain);
    }

    public function enable()
    {
        $this->verifyAction($this->domain);
    }

    public function disable()
    {
        $this->verifyAction($this->domain);
    }

    public function makeDefault()
    {
        $this->verifyAction($this->domain);
    }

    public function verifyAccess(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_site_config'))
        {
            nel_derp(360, _gettext('You do not have access to the Site Settings panel.'));
        }
    }

    public function verifyAction(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_site_config'))
        {
            nel_derp(361, _gettext('You are not allowed to manage site settings.'));
        }
    }
}
