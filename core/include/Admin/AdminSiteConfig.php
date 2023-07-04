<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Regen;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelSiteConfig;
use PDO;

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
        $prepared = $this->database->prepare(
            'SELECT * FROM "' . NEL_SETTINGS_TABLE . '"
                LEFT JOIN "' . NEL_SETTING_OPTIONS_TABLE . '"
                ON "' . NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_SETTING_OPTIONS_TABLE .
            '"."setting_name"
                INNER JOIN "' . NEL_SITE_CONFIG_TABLE . '"
                ON "' . NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_SITE_CONFIG_TABLE . '"."setting_name"
                WHERE "' . NEL_SETTINGS_TABLE . '"."setting_category" = \'site\'');
        $site_settings = $this->database->executePreparedFetchAll($prepared, [], PDO::FETCH_ASSOC);
        $changes = 0;

        foreach ($site_settings as $setting) {
            $setting_name = $setting['setting_name'];

            if (!isset($_POST[$setting_name])) {
                continue;
            }

            $old_value = $setting['setting_value'];
            $new_value = $_POST[$setting_name];

            if (is_array($new_value)) {
                $new_value = nel_form_input_default($new_value);
            }

            $new_value = nel_typecast($new_value, $setting_name);

            if (is_string($new_value) && !$this->session_user->checkPermission($this->domain, 'perm_raw_html') &&
                ($setting['raw_output'] ?? false)) {
                $new_value = htmlspecialchars($new_value, ENT_QUOTES, 'UTF-8');
            }

            if ($old_value != $new_value) {
                $this->updateSetting($setting_name, $new_value);
                $changes ++;
            }
        }

        if ($changes > 0) {
            $this->domain->regenCache();
            $this->domain->reload();
            nel_site_domain()->reload();
            $regen = new Regen();
            $regen->allBoards(true, false);
            $regen->sitePages($this->domain);
            $regen->overboard($this->domain);
        }

        $this->panel();
    }

    private function updateSetting($config_name, $setting)
    {
        // TODO: Bind to string instead of cast
        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_SITE_CONFIG_TABLE . '" SET "setting_value" = ? WHERE "setting_name" = ?');
        $this->database->executePrepared($prepared, [(string) $setting, $config_name]);
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_modify_site_config':
                nel_derp(380, _gettext('You are not allowed to modify the site configuration.'), 403);
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
