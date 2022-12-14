<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Regen;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelSiteConfig;

class AdminSiteConfig extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_SITE_CONFIG_TABLE;
        $this->id_column = '';
        $this->panel_name = _gettext('Site Config');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_modify_site_config');
        $output_panel = new OutputPanelSiteConfig($this->domain, false);
        $output_panel->render([], false);
    }

    public function update(): void
    {
        $this->verifyPermissions($this->domain, 'perm_modify_site_config');

        foreach ($_POST as $key => $value) {
            if (is_array($value)) {
                $value = nel_form_input_default($value);
            }

            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_SITE_CONFIG_TABLE . '" SET "setting_value" = ? WHERE "setting_name" = ?');
            $this->database->executePrepared($prepared, [(string) $value, $key], true);
        }

        $this->domain->regenCache();
        $this->domain->reload();
        nel_site_domain()->reload();
        $regen = new Regen();
        $regen->allBoards(true, false);
        $regen->sitePages($this->domain);
        $regen->overboard($this->domain);
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_modify_site_config':
                nel_derp(380, _gettext('You are not allowed to modify the site configuration.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
