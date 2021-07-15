<?php
declare(strict_types = 1);

namespace Nelliel\Modules\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Regen;
use Nelliel\Modules\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;
use Nelliel\Domains\DomainSite;
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
        $this->domain = ($this->defaults) ? new DomainSite($this->database) : new DomainBoard($this->board_id,
                $this->database);
        $this->data_table = NEL_BOARD_DEFAULTS_TABLE;
        $this->id_field = '';
        $this->id_column = '';
    }

    public function renderPanel()
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Render\OutputPanelBoardSettings($this->domain, false);
        $output_panel->render(['defaults' => $this->defaults], false);
    }

    public function creator()
    {
        $this->verifyAccess($this->domain);
    }

    public function add()
    {
        $this->verifyAction($this->domain);
    }

    public function editor()
    {
        $this->verifyAccess($this->domain);
    }

    public function update()
    {
        $this->verifyAction($this->domain);
        $lock_override = $this->session_user->checkPermission($this->domain, 'perm_manage_board_config_override');
        $config_table = ($this->defaults) ? $this->data_table : $this->domain->reference('config_table');
        $defaults = $this->defaultsList();

        foreach ($_POST as $key => $value)
        {
            $can_update = $this->defaults || $lock_override || !$defaults[$key]['edit_lock'];

            if (isset($value['lock']) && $this->defaults)
            {
                $lock_value = nel_form_input_default($value['lock']);
                $this->setLock($config_table, $key, $lock_value);
            }

            $force_update = isset($value['force_update']) && $value['force_update'] == 1 && $this->defaults;

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

            if ($can_update)
            {
                if ($force_update)
                {
                    foreach ($this->getBoardDomains() as $board_domain)
                    {
                        $this->updateSetting($board_domain->reference('config_table'), $key, $value, $lock_override);
                    }
                }

                $this->updateSetting($config_table, $key, $value, $lock_override);
            }
        }

        if (!$this->defaults)
        {
            $this->domain->regenCache();
            $this->domain->reload();
            $regen = new Regen();
            $regen->allBoardPages($this->domain);
            $regen->boardList(nel_site_domain());
        }

        $this->outputMain(true);
    }

    public function remove()
    {
        $this->verifyAction($this->domain);
    }

    public function enable()
    {
        $this->verifyAction($this->domain);
    }

    public function disable()
    {
        $this->verifyAction($this->domain);
    }

    public function makeDefault()
    {
        $this->verifyAction($this->domain);
    }

    private function setLock($config_table, $config_name, $setting)
    {
        $prepared = $this->database->prepare(
                'UPDATE "' . $config_table . '" SET "edit_lock" = ? WHERE "setting_name" = ?');
        $this->database->executePrepared($prepared, [$setting, $config_name], true);
    }

    private function updateSetting($config_table, $config_name, $setting, $lock_override)
    {
        if ($this->defaults || $lock_override)
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . $config_table . '" SET "setting_value" = ? WHERE "setting_name" = ?');
            $this->database->executePrepared($prepared, [$setting, $config_name], true);
        }
        else
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . $config_table . '" SET "setting_value" = ? WHERE "setting_name" = ? AND "edit_lock" = 0');
            $this->database->executePrepared($prepared, [$setting, $config_name], true);
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

    public function defaultsList()
    {
        $defaults_data = $this->database->executeFetchAll('SELECT * FROM "' . $this->data_table . '"',
                PDO::FETCH_ASSOC);
        $defaults = array();

        foreach ($defaults_data as $data)
        {
            $defaults[$data['setting_name']] = $data;
        }

        return $defaults;
    }

    public function verifyAccess(Domain $domain)
    {
        if ($this->defaults)
        {
            if (!$this->session_user->checkPermission($this->domain, 'perm_manage_board_defaults'))
            {
                nel_derp(340, _gettext('You do not have access to the New Board Defaults panel.'));
            }
        }
        else
        {
            if (!$this->session_user->checkPermission($this->domain, 'perm_manage_board_config'))
            {
                nel_derp(330, _gettext('You do not have access to the Board Settings panel.'));
            }
        }
    }

    public function verifyAction(Domain $domain)
    {
        if ($this->defaults)
        {
            if (!$this->session_user->checkPermission($this->domain, 'perm_manage_board_defaults'))
            {
                nel_derp(341, _gettext('You are not allowed to manage board defaults.'));
            }
        }
        else
        {
            if (!$this->session_user->checkPermission($this->domain, 'perm_manage_board_config'))
            {
                nel_derp(331, _gettext('You are not allowed to manage board settings.'));
            }
        }
    }
}
