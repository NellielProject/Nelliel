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

class AdminBoardDefaults extends Admin
{
    private $board_id;

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        // TODO: Something better should be possible
        $this->board_id = $_GET['board-id'] ?? '';
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
        $output_panel = new \Nelliel\Output\OutputPanelBoardSettings($this->domain, false); // TODO: Maybe separate output too
        $output_panel->render(['defaults' => true], false);
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
        $force_update_done = false;
        $this->verifyAction($this->domain);
        $lock_override = $this->session_user->checkPermission($this->domain, 'perm_manage_board_config_override');

        foreach ($_POST as $key => $value)
        {
            if (isset($value['lock']))
            {
                $lock_value = nel_form_input_default($value['lock']);
                $prepared = $this->database->prepare(
                        'UPDATE "' . NEL_BOARD_DEFAULTS_TABLE . '" SET "edit_lock" = ? WHERE "setting_name" = ?');
                $this->database->executePrepared($prepared, [$lock_value, $key]);
            }

            $force_update = isset($value['force_update']) && $value['force_update'] == 1;

            if ($force_update)
            {
                $force_update_done = true;
            }

            if ($key === 'enabled_filetypes')
            {
                $filetypes_array = array();

                foreach ($value as $type => $entries)
                {
                    if ($type === 'lock' || $type === 'force_update')
                    {
                        continue;
                    }

                    $type_enabled = nel_form_input_default($entries['enabled']) === '1';
                    $filetypes_array[$type]['enabled'] = $type_enabled;
                    $type_formats = $entries['formats'] ?? array();

                    foreach ($type_formats as $format => $enabled)
                    {
                        $format_enabled = nel_form_input_default($enabled) === '1';

                        if ($format_enabled)
                        {
                            $filetypes_array[$type]['formats'][] = $format;
                        }
                    }
                }

                $value = json_encode($filetypes_array);
                $key = 'enabled_filetypes';
            }
            else if ($key === 'enabled_styles')
            {
                $styles_array = array();

                foreach ($value as $style => $entries)
                {
                    if ($style === 'lock' || $style === 'force_update')
                    {
                        continue;
                    }

                    $style_enabled = nel_form_input_default($entries) === '1';

                    if ($style_enabled)
                    {
                        $styles_array[] = $style;
                    }
                }

                $value = json_encode($styles_array);
                $key = 'enabled_styles';
            }
            else
            {
                $value = nel_form_input_default($value);
            }

            if ($force_update)
            {
                foreach ($this->getBoardDomains() as $board_domain)
                {
                    $this->updateSetting($board_domain, $key, $value, $lock_override);
                }
            }

            $prepared = $this->database->prepare(
                    'UPDATE "' . NEL_BOARD_DEFAULTS_TABLE . '" SET "setting_value" = ? WHERE "setting_name" = ?');
            $this->database->executePrepared($prepared, [$value, $key]);
        }

        if ($force_update_done)
        {
            $regen = new Regen();
            $regen->allBoards(true, true);
        }

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

    private function getBoardDomains()
    {
        $query = 'SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"';
        $board_ids = $this->database->executeFetchAll($query, PDO::FETCH_COLUMN);
        $board_domains = array();

        foreach ($board_ids as $board_id)
        {
            $board_domains[] = new \Nelliel\Domains\DomainBoard($board_id, $this->database);
        }

        return $board_domains;
    }

    public function verifyAccess(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_board_defaults'))
        {
            nel_derp(340, _gettext('You do not have access to the New Board Defaults panel.'));
        }
    }

    public function verifyAction(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_board_defaults'))
        {
            nel_derp(341, _gettext('You are not allowed to manage board defaults.'));
        }
    }
}
