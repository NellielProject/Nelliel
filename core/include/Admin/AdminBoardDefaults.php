<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Regen;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;
use PDO;
use Nelliel\Output\OutputPanelBoardConfig;

class AdminBoardDefaults extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->domain = $domain;
        $this->data_table = NEL_BOARD_DEFAULTS_TABLE;
        $this->id_column = '';
        $this->panel_name = _gettext('Default Board Configuration');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_modify_board_defaults');
        $output_panel = new OutputPanelBoardConfig($this->domain, false);
        $output_panel->render(['defaults' => true], false);
    }

    public function update(): void
    {
        $this->verifyPermissions($this->domain, 'perm_modify_board_defaults');
        $lock_override = $this->session_user->checkPermission($this->domain, 'perm_manage_board_config_override');
        $board_domains = $this->getBoardDomains();

        $board_settings = $this->database->executeFetchAll(
            'SELECT * FROM "' . NEL_SETTINGS_TABLE . '"
                LEFT JOIN "' . NEL_SETTING_OPTIONS_TABLE . '"
                ON "' . NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_SETTING_OPTIONS_TABLE .
            '"."setting_name"
                INNER JOIN "' . NEL_BOARD_DEFAULTS_TABLE . '"
                ON "' . NEL_SETTINGS_TABLE . '"."setting_name" = "' . NEL_BOARD_DEFAULTS_TABLE .
            '"."setting_name"
                WHERE "' . NEL_SETTINGS_TABLE . '"."setting_category" = \'board\'', PDO::FETCH_ASSOC);
        $raw_html = $this->session_user->checkPermission($this->domain, 'perm_raw_html');
        $changes = 0;
        $force_updates = 0;

        foreach ($board_settings as $setting) {
            $setting_name = $setting['setting_name'];

            if (!isset($_POST[$setting_name])) {
                continue;
            }

            $lock = (bool) nel_form_input_default($_POST[$setting_name]['lock']);
            $force_update = isset($_POST[$setting_name]['force_update']);
            $old_value = $setting['setting_value'];
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
            } else if ($setting_name === 'enabled_styles') {
                $styles_array = array();

                foreach ($new_value as $style => $entries) {
                    $style_enabled = nel_form_input_default($entries) === '1';

                    if ($style_enabled) {
                        $styles_array[] = $style;
                    }
                }

                $new_value = json_encode($styles_array);
            } else if ($setting_name === 'enabled_content_ops') {
                $content_ops_array = array();

                foreach ($new_value as $content_op => $entries) {
                    $content_op_enabled = nel_form_input_default($entries) === '1';

                    if ($content_op_enabled) {
                        $content_ops_array[] = $content_op;
                    }
                }

                $new_value = json_encode($content_ops_array);
            } else {
                $new_value = nel_form_input_default($new_value);
                $new_value = nel_typecast($new_value, $setting_name);

                if (is_string($new_value) && !$raw_html && ($setting['raw_output'] ?? false)) {
                    $new_value = htmlspecialchars($new_value, ENT_QUOTES, 'UTF-8');
                }
            }

            if ($old_value != $new_value) {
                $this->updateDefault($setting_name, $new_value);
                $changes ++;
            }

            if ($force_update) {
                $force_updates ++;

                foreach ($board_domains as $board_domain) {
                    $this->updateBoardSetting($board_domain, $setting_name, $new_value, $lock_override);
                }
            }

            if ($lock && $setting['edit_lock'] == 0) {
                $this->toggleLock($setting_name, 1);
            }

            if (!$lock && $setting['edit_lock'] == 1) {
                $this->toggleLock($setting_name, 0);
            }
        }

        if ($force_updates > 0) {
            $regen = new Regen();
            $regen->allBoards(true, true);
        }

        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_modify_board_defaults':
                nel_derp(320, _gettext('You are not allowed to modify the default board configuration.'), 403);
                break;

            default:
                $this->defaultPermissionError();
        }
    }

    private function toggleLock(string $setting_name, int $new_status): void
    {
        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_BOARD_DEFAULTS_TABLE . '" SET "edit_lock" = ? WHERE "setting_name" = ?');
        $this->database->executePrepared($prepared, [$new_status, $setting_name]);
    }

    private function updateDefault(string $config_name, $setting): void
    {
        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_BOARD_DEFAULTS_TABLE . '" SET "setting_value" = ? WHERE "setting_name" = ?');
        $this->database->executePrepared($prepared, [(string) $setting, $config_name]);
    }

    private function updateBoardSetting(Domain $domain, string $config_name, $setting, bool $lock_override): void
    {
        if ($lock_override) {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_BOARD_CONFIGS_TABLE .
                '" SET "setting_value" = ? WHERE "setting_name" = ? AND "board_id" = ?');
            $this->database->executePrepared($prepared, [(string) $setting, $config_name, $domain->id()]);
        } else {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_BOARD_CONFIGS_TABLE .
                '" SET "setting_value" = ? WHERE "setting_name" = ? AND "board_id" = ? AND "edit_lock" = 0');
            $this->database->executePrepared($prepared, [(string) $setting, $config_name, $domain->id()]);
        }
    }

    private function getBoardDomains()
    {
        $query = 'SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"';
        $board_ids = $this->database->executeFetchAll($query, PDO::FETCH_COLUMN);
        $board_domains = array();

        foreach ($board_ids as $board_id) {
            $board_domains[] = new DomainBoard($board_id, $this->database);
        }

        return $board_domains;
    }
}
