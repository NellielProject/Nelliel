<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;

class AdminIconSets extends AdminHandler
{

    function __construct(Authorization $authorization, Domain $domain)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->validateUser();
    }

    public function actionDispatch(string $action, bool $return)
    {
        /*if ($action === 'add')
        {
            $this->add();
        }
        else if ($action == 'remove')
        {
            $this->remove();
        }
        else if ($action == 'make-default')
        {
            $this->makeDefault();
        }

        if ($return)
        {
            return;
        }

        $this->renderPanel();*/
    }

    public function renderPanel()
    {
        $output_panel = new \Nelliel\Output\OutputPanelIconSets($this->domain, false);
        $output_panel->render(['user' => $this->session_user], false);
    }

    public function creator()
    {
    }

    public function add()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_icon_sets'))
        {
            nel_derp(461, _gettext('You are not allowed to install icon sets.'));
        }

        $icon_set_id = $_GET['icon-set-id'];
        $front_end_data = new \Nelliel\FrontEndData($this->database);
        $icon_set_inis = $front_end_data->getIconSetInis();

        foreach ($icon_set_inis as $ini)
        {
            if ($ini['id'] === $icon_set_id)
            {
                $info = json_encode($ini);
            }
        }

        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_ASSETS_TABLE . '" ("asset_id", "type", "is_default", "info") VALUES (?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$icon_set_id, 'icon-set', 0, $info]);
        $this->output_main = true;
    }

    public function editor()
    {
    }

    public function update()
    {
    }

    public function remove()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_icon_sets'))
        {
            nel_derp(462, _gettext('You are not allowed to uninstall icon sets.'));
        }

        $icon_set_id = $_GET['icon-set-id'];
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_ASSETS_TABLE . '" WHERE "asset_id" = ? AND "type" = ?');
        $this->database->executePrepared($prepared, [$icon_set_id, 'icon-set']);
        $this->output_main = true;
    }

    public function makeDefault()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_icon_sets'))
        {
            nel_derp(463, _gettext('You are not allowed to set the default icon set.'));
        }

        $icon_set_id = $_GET['icon-set-id'];
        $this->database->exec('UPDATE "' . NEL_ASSETS_TABLE . '" SET "is_default" = 0 WHERE "type" = \'icon-set\'');
        $prepared = $this->database->prepare(
                'UPDATE "' . NEL_ASSETS_TABLE . '" SET "is_default" = 1 WHERE "asset_id" = ? AND "type" = \'icon-set\'');
        $this->database->executePrepared($prepared, [$icon_set_id]);
        $this->output_main = true;
    }
}
