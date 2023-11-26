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
        $prepared = $this->database->prepare(
            'SELECT * FROM "' . NEL_SETTINGS_TABLE . '"
                LEFT JOIN "' . NEL_SETTING_OPTIONS_TABLE . '"
                ON "' . NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_SETTING_OPTIONS_TABLE .
            '"."setting_name"
                INNER JOIN "' . NEL_BOARD_CONFIGS_TABLE . '"
                ON "' . NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_BOARD_CONFIGS_TABLE .
            '"."setting_name"
                WHERE "' . NEL_BOARD_CONFIGS_TABLE . '"."board_id" = ? AND "' . NEL_SETTINGS_TABLE .
            '"."setting_category" = \'board\'');
        $board_settings = $this->database->executePreparedFetchAll($prepared, [$this->domain->id()], PDO::FETCH_ASSOC);
        $changes = 0;

        foreach ($board_settings as $setting) {
            $setting_name = $setting['setting_name'];
            $store_raw = (bool) nel_form_input_default($_POST[$setting_name]['store_raw'] ?? array());
            $constructed = false;
            $status_change = false;

            if (!isset($_POST[$setting_name])) {
                continue;
            }

            $old_value = nel_typecast($setting['setting_value'], $setting['data_type']);
            $new_value = $_POST[$setting_name];

            if ($setting_name === 'enabled_filetypes') {
                $filetypes_array = array();

                foreach ($new_value as $category => $entries) {
                    if ($category === 'lock' || $category === 'force_update') {
                        continue;
                    }

                    $filetypes_array[$category]['enabled'] = nel_form_input_default($entries['enabled']) === '1';
                    $filetypes_array[$category]['max_size'] = intval($entries['max_size']);
                    $formats = $entries['formats'] ?? array();

                    foreach ($formats as $format => $entries) {
                        if (nel_form_input_default($entries['enabled']) === '1') {
                            $filetypes_array[$category]['formats'][] = $format;
                        }
                    }
                }

                $new_value = json_encode($filetypes_array);
                $constructed = true;
            } else if ($setting_name === 'enabled_styles') {
                $styles_array = array();

                foreach ($new_value as $style => $entries) {
                    $style_enabled = nel_form_input_default($entries) === '1';

                    if ($style_enabled) {
                        $styles_array[] = $style;
                    }
                }

                $new_value = json_encode($styles_array);
                $constructed = true;
            } else if ($setting_name === 'enabled_content_ops') {
                $content_ops_array = array();

                foreach ($new_value as $content_op => $entries) {
                    $content_op_enabled = nel_form_input_default($entries) === '1';

                    if ($content_op_enabled) {
                        $content_ops_array[] = $content_op;
                    }
                }

                $new_value = json_encode($content_ops_array);
                $constructed = true;
            } else {
                $new_value = nel_form_input_default($new_value);
                $new_value = nel_typecast($new_value, $setting['data_type']);

                if (!$user_can_raw_html) {
                    $store_raw = (bool) $setting['stored_raw'];
                }

                if ((bool) $setting['stored_raw'] !== $store_raw) {
                    $status_change = true;
                }
            }

            $value_change = $old_value != $new_value;

            if ($value_change || $status_change) {
                if (is_string($new_value)) {
                    if (!$constructed && (!$store_raw || !$user_can_raw_html || !($setting['raw_output'] ?? false))) {
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
            nel_site_domain()->reload();
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

    private function updateSetting(Domain $domain, $config_name, $setting, bool $lock_override, int $store_raw)
    {
        if ($lock_override) {
            $prepared = $this->database->prepare(
                'UPDATE "' . $domain->reference('config_table') .
                '" SET "setting_value" = ?, "stored_raw" = ? WHERE "setting_name" = ? AND "board_id" = ?');
        } else {
            $prepared = $this->database->prepare(
                'UPDATE "' . $domain->reference('config_table') .
                '" SET "setting_value" = ?, "stored_raw" = ? WHERE "setting_name" = ? AND "board_id" = ? AND "edit_lock" = 0');
        }

        $prepared->bindValue(1, $setting, PDO::PARAM_STR);
        $prepared->bindValue(2, $store_raw, PDO::PARAM_INT);
        $prepared->bindValue(3, $config_name, PDO::PARAM_STR);
        $prepared->bindValue(4, $domain->id(), PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
    }
}
