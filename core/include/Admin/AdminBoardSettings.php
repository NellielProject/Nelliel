<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Regen;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;

class AdminBoardSettings extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->domain = $domain;
        $this->data_table = $this->domain->reference('config_table');
        $this->id_field = '';
        $this->id_column = '';
        $this->panel_name = _gettext('Board Settings');
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_board_config_modify');
        $output_panel = new \Nelliel\Output\OutputPanelBoardSettings($this->domain, false);
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
        $lock_override = $this->session_user->checkPermission($this->domain, 'perm_board_config_override');

        foreach ($_POST as $key => $value) {
            if ($key === 'enabled_filetypes') {
                $filetypes_array = array();

                foreach ($value as $type => $entries) {
                    $type_enabled = nel_form_input_default($entries['enabled']) === '1';
                    $filetypes_array[$type]['enabled'] = $type_enabled;
                    $type_formats = $entries['formats'] ?? array();

                    foreach ($type_formats as $format => $enabled) {
                        $format_enabled = nel_form_input_default($enabled) === '1';

                        if ($format_enabled) {
                            $filetypes_array[$type]['formats'][] = $format;
                        }
                    }
                }

                $value = json_encode($filetypes_array);
                $key = 'enabled_filetypes';
            } else if ($key === 'enabled_styles') {
                $styles_array = array();

                foreach ($value as $style => $entries) {
                    $style_enabled = nel_form_input_default($entries) === '1';

                    if ($style_enabled) {
                        $styles_array[] = $style;
                    }
                }

                $value = json_encode($styles_array);
                $key = 'enabled_styles';
            } else if ($key === 'enabled_content_ops') {
                $content_ops_array = array();

                foreach ($value as $content_op => $entries) {
                    $content_op_enabled = nel_form_input_default($entries) === '1';

                    if ($content_op_enabled) {
                        $content_ops_array[] = $content_op;
                    }
                }

                $value = json_encode($content_ops_array);
                $key = 'enabled_content_ops';
            } else {
                $value = nel_form_input_default($value);
            }

            $this->updateSetting($this->domain, $key, $value, $lock_override);
        }

        $this->domain->regenCache();
        $this->domain->reload();
        nel_site_domain()->reload();
        $regen = new Regen();
        $regen->allBoardPages($this->domain);
        $this->outputMain(true);
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
                nel_derp(310, _gettext('You are not allowed to modify the board settings.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }

    private function setLock(DomainBoard $domain, $config_name, $setting)
    {
        $prepared = $this->database->prepare(
            'UPDATE "' . $domain->reference('config_table') .
            '" SET "edit_lock" = ? WHERE "setting_name" = ? AND "board_id" = ?');
        $this->database->executePrepared($prepared, [$setting, $config_name, $domain->id()]);
    }

    private function updateSetting(Domain $domain, $config_name, $setting, $lock_override)
    {
        if ($lock_override) {
            $prepared = $this->database->prepare(
                'UPDATE "' . $domain->reference('config_table') .
                '" SET "setting_value" = ? WHERE "setting_name" = ? AND "board_id" = ?');
            $this->database->executePrepared($prepared, [$setting, $config_name, $domain->id()]);
        } else {
            $prepared = $this->database->prepare(
                'UPDATE "' . $domain->reference('config_table') .
                '" SET "setting_value" = ? WHERE "setting_name" = ? AND "board_id" = ? AND "edit_lock" = 0');
            $this->database->executePrepared($prepared, [$setting, $config_name, $domain->id()]);
        }
    }
}
