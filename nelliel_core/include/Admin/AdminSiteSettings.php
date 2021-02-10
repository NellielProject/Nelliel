<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;

class AdminSiteSettings extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session, array $inputs)
    {
        parent::__construct($authorization, $domain, $session, $inputs);
    }

    public function renderPanel()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelSiteSettings($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator()
    {
        $this->verifyAccess();
    }

    public function add()
    {
        $this->verifyAction();
    }

    public function editor()
    {
        $this->verifyAccess();
    }

    public function update()
    {
        $this->verifyAction();

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
        $this->verifyAction();
    }

    public function enable()
    {
        $this->verifyAction();
    }

    public function disable()
    {
        $this->verifyAction();
    }

    public function makeDefault()
    {
        $this->verifyAction();
    }

    public function verifyAccess()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_site_config'))
        {
            nel_derp(360, _gettext('You do not have access to the Site Settings panel.'));
        }
    }

    public function verifyAction()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_site_config'))
        {
            nel_derp(361, _gettext('You are not allowed to manage site settings.'));
        }
    }
}
