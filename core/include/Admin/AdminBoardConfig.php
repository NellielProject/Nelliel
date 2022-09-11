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
        $this->id_field = '';
        $this->id_column = '';
        $this->panel_name = _gettext('Board Config');
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_board_config_modify');
        $output_panel = new OutputPanelBoardConfig($this->domain, false);
        $output_panel->render(['defaults' => false], false);
    }

    public function creator(): void
    {}

    public function add(): void
    {}

    public function editor(): void
    {}

    public function update(): void
    {
        $this->verifyPermissions($this->domain, 'perm_board_config_modify');
        $lock_override = $this->session_user->checkPermission($this->domain, 'perm_manage_board_config_override');
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
        $raw_html = $this->session_user->checkPermission($this->domain, 'perm_raw_html');
        $changes = 0;

        foreach ($board_settings as $setting) {
            $setting_name = $setting['setting_name'];

            if (!isset($_POST[$setting_name])) {
                continue;
            }

            $old_value = $setting['setting_value'];
            $new_value = $_POST[$setting_name];

            if ($setting_name === 'enabled_filetypes') {
                $filetypes_array = array();

                foreach ($new_value as $category => $entries) {
                    $category_enabled = nel_form_input_default($entries['enabled']) === '1';
                    $filetypes_array[$category]['enabled'] = $category_enabled;
                    $formats = $entries['formats'] ?? array();

                    foreach ($formats as $format => $enabled) {
                        $format_enabled = nel_form_input_default($enabled) === '1';

                        if ($format_enabled) {
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
                $this->updateSetting($this->domain, $setting_name, $new_value, $lock_override);
                $changes ++;
            }
        }

        if ($changes > 0) {
            $this->domain->regenCache();
            $this->domain->reload();
            nel_site_domain()->reload();
            $regen = new Regen();
            $regen->allBoardPages($this->domain);
        }

        $this->panel();
    }

    public function remove(): void
    {}

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_board_config_modify':
                nel_derp(310, _gettext('You are not allowed to modify the board configuration.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }

    private function updateSetting(Domain $domain, $config_name, $setting, $lock_override)
    {
        // TODO: Bind to string instead of cast
        if ($lock_override) {
            $prepared = $this->database->prepare(
                'UPDATE "' . $domain->reference('config_table') .
                '" SET "setting_value" = ? WHERE "setting_name" = ? AND "board_id" = ?');
            $this->database->executePrepared($prepared, [(string) $setting, $config_name, $domain->id()]);
        } else {
            $prepared = $this->database->prepare(
                'UPDATE "' . $domain->reference('config_table') .
                '" SET "setting_value" = ? WHERE "setting_name" = ? AND "board_id" = ? AND "edit_lock" = 0');
            $this->database->executePrepared($prepared, [(string) $setting, $config_name, $domain->id()]);
        }
    }
}
