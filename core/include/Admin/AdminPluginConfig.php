<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Regen;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelPluginControls;
use PDO;

class AdminPluginConfig extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->domain = $domain;
        $this->data_table = NEL_PLUGIN_CONFIGS_TABLE;
        $this->id_column = '';
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_modify_board_config');
        $output_panel = new OutputPanelPluginControls($this->domain, false);
        $output_panel->main(['defaults' => false], false);
    }

    public function update(string $plugin_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_modify_board_config');
        $user_can_raw_html = $this->session_user->checkPermission($this->domain, 'perm_raw_html');
        $columns = ' "' . NEL_SETTINGS_TABLE . '"."setting_category", "' . NEL_SETTINGS_TABLE . '"."setting_name"'; // Why isn't this covered by *
        $prepared = $this->database->prepare(
            'SELECT *, ' . $columns . ' FROM "' . NEL_SETTINGS_TABLE . '"
                LEFT JOIN "' . NEL_SETTING_OPTIONS_TABLE . '"
                ON "' . NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_SETTING_OPTIONS_TABLE .
            '"."setting_name" WHERE "' . NEL_SETTINGS_TABLE . '"."setting_owner" = :setting_owner');
        $prepared->bindValue(':setting_owner', $plugin_id, PDO::PARAM_STR);
        $plugin_settings = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);
        $prepared = $this->database->prepare(
            'SELECT * FROM "' . NEL_PLUGIN_CONFIGS_TABLE . '" WHERE "plugin_id" = :plugin_id');
        $prepared->bindValue(':plugin_id', $plugin_id, PDO::PARAM_STR);
        $config_list = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);
        $config_list = nel_key_array_by_column('setting_name', $config_list);
        $changes = 0;

        foreach ($plugin_settings as $setting) {
            $setting_name = $setting['setting_name'];
            $config = $config_list[$setting_name] ?? array();
            $raw_output = $setting['raw_output'] ?? false;
            $constructed = false;
            $old_value = nel_typecast($config['setting_value'] ?? $setting['default_value'], $setting['data_type']);
            $config_stored_raw = boolval($config['stored_raw'] ?? false);

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

                $this->updateSetting($plugin_id, $this->domain, $setting_name, $new_value, (int) $store_raw);
                $changes ++;
            }
        }

        if ($changes > 0) {
            $this->domain->regenCache();
            $this->domain->reload();
            nel_get_cached_domain(Domain::SITE)->reload();
            $regen = new Regen();

            if ($this->domain->id() === Domain::SITE || $this->domain->id() === Domain::GLOBAL) {
                $regen->sitePages(nel_get_cached_domain(Domain::SITE));
                $regen->allBoards(true, false);
                $regen->overboard(nel_get_cached_domain(Domain::SITE));
            }
        }
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_modify_board_config':
                nel_derp(310, _gettext('You are not allowed to modify the board configuration.'), 403);
                break;

            default:
                $this->defaultPermissionError();
        }
    }

    private function updateSetting(string $plugin_id, Domain $domain, $config_name, $setting, int $stored_raw)
    {
        if ($this->database->rowExists(NEL_PLUGIN_CONFIGS_TABLE, ['setting_name', 'board_id'],
            [$config_name, $domain->id()])) {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_PLUGIN_CONFIGS_TABLE .
                '" SET "setting_value" = :setting_value, "stored_raw" = :stored_raw WHERE "plugin_id" = :plugin_id AND "setting_name" = :setting_name AND "board_id" = :board_id');
        } else {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_PLUGIN_CONFIGS_TABLE .
                '" ("plugin_id", "setting_name", "setting_value", "stored_raw", "board_id") VALUES (:plugin_id, :setting_name, :setting_value, :stored_raw, :board_id)');
        }

        $prepared->bindValue(':plugin_id', $plugin_id, PDO::PARAM_STR);
        $prepared->bindValue(':setting_value', $setting, PDO::PARAM_STR);
        $prepared->bindValue(':stored_raw', $stored_raw, PDO::PARAM_INT);
        $prepared->bindValue(':setting_name', $config_name, PDO::PARAM_STR);
        $prepared->bindValue('board_id', $domain->id(), PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
    }
}
