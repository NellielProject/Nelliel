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
        $user_can_raw_html = $this->session_user->checkPermission($this->domain, 'perm_raw_html');
        $columns = ' "' . NEL_SETTINGS_TABLE . '"."setting_category", "' . NEL_SETTINGS_TABLE . '"."setting_name"'; // Why isn't this covered by *
        $site_settings = $this->database->executeFetchAll(
            'SELECT *, ' . $columns . ' FROM "' . NEL_SETTINGS_TABLE . '"
                LEFT JOIN "' . NEL_SETTING_OPTIONS_TABLE . '"
                ON "' . NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_SETTING_OPTIONS_TABLE .
            '"."setting_name" WHERE "' . NEL_SETTINGS_TABLE . '"."setting_category" = \'site\' AND "' .
            NEL_SETTINGS_TABLE . '"."setting_owner" = \'nelliel\'', PDO::FETCH_ASSOC);
        $config_list = $this->database->executeFetchAll('SELECT * FROM "' . NEL_SITE_CONFIG_TABLE . '"',
            PDO::FETCH_ASSOC);
        $config_list = nel_key_array_by_column('setting_name', $config_list);
        $changes = 0;

        foreach ($site_settings as $setting) {
            $setting_name = $setting['setting_name'];
            $config = $config_list[$setting_name] ?? array();
            $old_value = nel_typecast($config['setting_value'] ?? $setting['default_value'], $setting['data_type']);
            $config_stored_raw = boolval($config['stored_raw'] ?? false);
            $raw_output = $setting['raw_output'] ?? false;
            $constructed = false;

            if ($setting['data_type'] === 'boolean') {
                $new_value = $_POST[$setting_name] ?? false;
            } else {
                $new_value = $_POST[$setting_name] ?? $old_value;
            }

            if (!$user_can_raw_html) {
                $store_raw = false;
            } else {
                $store_raw = boolval($_POST[$setting_name]['store_raw'] ?? false) && $raw_output;
            }

            if (is_array($new_value)) {
                $new_value = nel_typecast($new_value['value'], $setting['data_type'], false);
            }

            $new_value = nel_typecast($new_value, $setting['data_type']);

            if ($old_value != $new_value || ($user_can_raw_html && $config_stored_raw !== $store_raw)) {
                if ($setting['json']) {
                    if (is_string($new_value)) {
                        $new_value = json_decode($new_value, true) ?? array();
                    }

                    if (is_array($new_value)) {
                        if (!$store_raw) {
                            $new_value = nel_array_htmlspecialchars($new_value, ENT_QUOTES);
                        }

                        $new_value = json_encode($new_value);
                    }
                } else {
                    if (is_string($new_value) && !$constructed && !$store_raw) {
                        $new_value = htmlspecialchars($new_value, ENT_QUOTES, 'UTF-8');
                    }
                }

                $this->updateSetting($setting_name, $new_value, (int) $store_raw);
                $changes ++;
            }
        }

        if ($changes > 0) {
            $this->domain->regenCache();
            $this->domain->reload();
            nel_get_cached_domain(Domain::SITE)->reload();
            $regen = new Regen();
            $regen->allBoards(true, false);
            $regen->sitePages($this->domain);
            $regen->overboard($this->domain);
        }

        $this->panel();
    }

    private function updateSetting($config_name, $setting, int $stored_raw)
    {
        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_SITE_CONFIG_TABLE .
            '" SET "setting_value" = :setting_value, "stored_raw" = :stored_raw WHERE "setting_name" = :setting_name');
        $prepared->bindValue(':setting_value', $setting, PDO::PARAM_STR);
        $prepared->bindValue(':stored_raw', $stored_raw, PDO::PARAM_INT);
        $prepared->bindValue(':setting_name', $config_name, PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
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
