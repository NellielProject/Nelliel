<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;
use Nelliel\Domain;
use Nelliel\Auth\Authorization;

class AdminBoardSettings extends AdminHandler
{
    private $defaults = false;

    function __construct(Authorization $authorization, Domain $domain)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->defaults = ($this->domain->id() === '_site_') ? true : false;
        $this->validateUser();
    }

    public function actionDispatch(string $action, bool $return)
    {
        if ($action === 'update')
        {
            $this->update();
        }

        if ($return)
        {
            return;
        }

        $this->renderPanel();
    }

    public function renderPanel()
    {
        $output_panel = new \Nelliel\Output\OutputPanelBoardSettings($this->domain, false);
        $output_panel->render(['user' => $this->session_user, 'defaults' => $this->defaults], false);
    }

    public function creator()
    {
    }

    public function add()
    {
    }

    public function editor()
    {
    }

    public function update()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_board_config'))
        {
            nel_derp(331, _gettext('You are not allowed to modify the board settings.'));
        }

        if ($this->defaults && !$this->session_user->checkPermission($this->domain, 'perm_board_defaults'))
        {
            nel_derp(332, _gettext('You are not allowed to modify the default board settings.'));
        }

        $config_table = ($this->defaults) ? NEL_BOARD_DEFAULTS_TABLE : $this->domain->reference('config_table');
        $lock_override = $this->session_user->checkPermission($this->domain, 'perm_board_config_lock_override');

        foreach ($_POST as $key => $value)
        {
            if(!is_array($value))
            {
                $this->updateSetting($config_table, $key, $value, $lock_override);
                continue;
            }

            if (isset($value['lock']))
            {
                $lock_value = nel_form_input_default($value['lock']);
                $this->setLock($config_table, $key, $lock_value);

                // TODO: Possibly alter check to defaults board instead of setting individual boards
                foreach ($this->getBoardDomains() as $board_domain)
                {
                    $this->setLock($board_domain->reference('config_table'), $key, $lock_value);
                }
            }

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

            $this->updateSetting($config_table, $key, $value, $lock_override);
        }

        if (!$this->defaults)
        {
            $regen = new \Nelliel\Regen();
            $regen->boardCache($this->domain);
            $regen->allBoardPages($this->domain);
        }
    }

    public function remove()
    {
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
            $board_domains[] = new \Nelliel\DomainBoard($board_id, $this->database);
        }

        return $board_domains;
    }
}
