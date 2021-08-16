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

class AdminBoardSettings extends Admin
{
    private $defaults = false;
    private $board_id;

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        // TODO: Something better should be possible
        $this->board_id = $_GET['board-id'] ?? '';
        $this->defaults = empty($this->board_id) ? true : false;
        parent::__construct($authorization, $domain, $session);
        $this->domain = $domain;
        $this->data_table = NEL_BOARD_DEFAULTS_TABLE;
        $this->id_field = '';
        $this->id_column = '';
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel(): void
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Output\OutputPanelBoardSettings($this->domain, false);
        $output_panel->render(['defaults' => false], false);
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
        $lock_override = $this->session_user->checkPermission($this->domain, 'perm_manage_board_config_override');

        foreach ($_POST as $key => $value)
        {
            if ($key === 'enabled_filetypes')
            {
                $filetypes_array = array();

                foreach ($value['types'] as $type => $entries)
                {
                    $type_enabled = nel_form_input_default($entries['enabled']) === '1';
                    $filetypes_array[$type]['enabled'] = $type_enabled;
                    $type_formats = $entries['formats'] ?? array();

                    foreach ($type_formats as $format => $enabled)
                    {
                        $format_enabled = nel_form_input_default($enabled) === '1';

                        if ($format_enabled)
                        {
                            $filetypes_array[$type]['formats'][$format] = true;
                        }
                    }
                }

                $value = json_encode($filetypes_array);
                $key = 'enabled_filetypes';
            }
            else
            {
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
    {
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
        if ($lock_override)
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . $domain->reference('config_table') .
                    '" SET "setting_value" = ? WHERE "setting_name" = ? AND "board_id" = ?');
            $this->database->executePrepared($prepared, [$setting, $config_name, $domain->id()]);
        }
        else
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . $domain->reference('config_table') .
                    '" SET "setting_value" = ? WHERE "setting_name" = ? AND "board_id" = ? AND "edit_lock" = 0');
            $this->database->executePrepared($prepared, [$setting, $config_name, $domain->id()]);
        }
    }

    public function defaultsList()
    {
        $defaults_data = $this->database->executeFetchAll('SELECT * FROM "' . $this->data_table . '"', PDO::FETCH_ASSOC);
        $defaults = array();

        foreach ($defaults_data as $data)
        {
            $defaults[$data['setting_name']] = $data;
        }

        return $defaults;
    }

    public function verifyAccess(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_board_config'))
        {
            nel_derp(330, _gettext('You do not have access to the Board Settings panel.'));
        }
    }

    public function verifyAction(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_board_config'))
        {
            nel_derp(331, _gettext('You are not allowed to manage board settings.'));
        }
    }
}
