<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Regen;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelBoardConfig;
use PDO;

class AdminBoardConfig extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->domain = $domain;
        $this->data_table = $this->domain->reference('config_table');
        $this->id_column = '';
        $this->panel_name = _gettext('Board Config');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_modify_board_config');
        $output_panel = new OutputPanelBoardConfig($this->domain, false);
        $output_panel->render(['defaults' => false], false);
    }

    public function update(): void
    {
        $this->verifyPermissions($this->domain, 'perm_modify_board_config');
        $lock_override = $this->session_user->checkPermission($this->domain, 'perm_manage_board_config_override');
        $user_can_raw_html = $this->session_user->checkPermission($this->domain, 'perm_raw_html');
        $columns = ' "' . NEL_SETTINGS_TABLE . '"."setting_category", "' . NEL_SETTINGS_TABLE . '"."setting_name"'; // Why isn't this covered by *
        $board_settings = $this->database->executeFetchAll(
            'SELECT *, ' . $columns . ' FROM "' . NEL_SETTINGS_TABLE . '"
                LEFT JOIN "' . NEL_SETTING_OPTIONS_TABLE . '"
                ON "' . NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_SETTING_OPTIONS_TABLE .
            '"."setting_name" WHERE "' . NEL_SETTINGS_TABLE . '"."setting_category" = \'board\' AND "' .
            NEL_SETTINGS_TABLE . '"."setting_owner" = \'nelliel\'', PDO::FETCH_ASSOC);
        $prepared = $this->database->prepare(
            'SELECT * FROM "' . NEL_BOARD_CONFIGS_TABLE . '" WHERE "board_id" = :board_id');
        $prepared->bindValue(':board_id', $this->domain->id(), PDO::PARAM_STR);
        $config_list = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);
        $config_list = nel_key_array_by_column('setting_name', $config_list);
        $changes = 0;

        foreach ($board_settings as $setting) {
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

            if ($setting_name === 'enabled_filetypes') {
                $filetypes_array = array();

                foreach ($new_value as $category => $entries) {
                    if ($category === 'lock' || $category === 'force_update') {
                        continue;
                    }

                    $filetypes_array[$category]['enabled'] = boolval($entries['enabled'] ?? false);
                    $filetypes_array[$category]['max_size'] = intval($entries['max_size'] ?? 0);
                    $formats = $entries['formats'] ?? array();

                    foreach ($formats as $format => $entries) {
                        if ($entries['enabled']) {
                            $filetypes_array[$category]['formats'][] = $format;
                        }
                    }
                }

                $new_value = json_encode($filetypes_array);
                $constructed = true;
            } else if ($setting_name === 'enabled_styles') {
                $styles_array = array();

                foreach ((array) $new_value as $style => $entries) {
                    $style_enabled = boolval($entries['enabled'] ?? false);

                    if ($style_enabled) {
                        $styles_array[] = $style;
                    }
                }

                $new_value = json_encode($styles_array);
                $constructed = true;
            } else if ($setting_name === 'enabled_content_ops') {
                $content_ops_array = array();

                foreach ((array) $new_value as $content_op => $entries) {
                    $content_op_enabled = boolval($entries['enabled'] ?? false);

                    if ($content_op_enabled) {
                        $content_ops_array[] = $content_op;
                    }
                }

                $new_value = json_encode($content_ops_array);
                $constructed = true;
            } else {
                if (is_array($new_value)) {
                    $new_value = nel_typecast($new_value['value'], $setting['data_type'], false);
                }
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

                $this->updateSetting($this->domain, $setting_name, $new_value, $lock_override, (int) $store_raw);
                $changes ++;
            }
        }

        if ($changes > 0) {
            $this->domain->regenCache();
            $this->domain->reload();
            nel_get_cached_domain(Domain::SITE)->reload();
            $regen = new Regen();
            $regen->boardPages($this->domain);
        }

        $this->panel();
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

    private function updateSetting(Domain $domain, $config_name, $setting, bool $lock_override, int $stored_raw)
    {
        $edit_lock = ($lock_override) ? '' : ' AND "edit_lock" = 0';

        if ($this->database->rowExists(NEL_BOARD_CONFIGS_TABLE, ['setting_name', 'board_id'],
            [$config_name, $domain->id()])) {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_BOARD_CONFIGS_TABLE .
                '" SET "setting_value" = :setting_value, "stored_raw" = :stored_raw WHERE "setting_name" = :setting_name AND "board_id" = :board_id' .
                $edit_lock);
        } else {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_BOARD_CONFIGS_TABLE .
                '" ("setting_name", "setting_value", "stored_raw", "board_id") VALUES (:setting_name, :setting_value, :stored_raw, :board_id)');
        }

        $prepared->bindValue(':setting_value', $setting, PDO::PARAM_STR);
        $prepared->bindValue(':stored_raw', $stored_raw, PDO::PARAM_INT);
        $prepared->bindValue(':setting_name', $config_name, PDO::PARAM_STR);
        $prepared->bindValue('board_id', $domain->id(), PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
    }
}
