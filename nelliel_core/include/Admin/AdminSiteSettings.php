<?php

declare(strict_types=1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Regen;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;

class AdminSiteSettings extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel(): void
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Output\OutputPanelSiteSettings($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator(): void
    {
    }

    public function add(): void
    {
    }

    public function editor(): void
    {
    }

    public function update(): void
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
        nel_site_domain()->reload();
        $regen = new Regen();
        $regen->allBoards(true, false);
        $regen->overboard($this->domain);
        $regen->boardList($this->domain);
        $this->outputMain(true);
    }

    public function remove(): void
    {
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
