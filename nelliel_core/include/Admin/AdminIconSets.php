<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Auth\Authorization;

class AdminIconSets extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, array $inputs)
    {
        parent::__construct($authorization, $domain, $inputs);
    }

    public function renderPanel()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelIconSets($this->domain, false);
        $output_panel->render([], false);
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
        $icon_set_inis = $this->domain->frontEndData()->getIconSetInis();

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
        $this->outputMain(true);
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
        $prepared = $this->database->prepare(
                'DELETE FROM "' . NEL_ASSETS_TABLE . '" WHERE "asset_id" = ? AND "type" = ?');
        $this->database->executePrepared($prepared, [$icon_set_id, 'icon-set']);
        $this->outputMain(true);
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
        $this->outputMain(true);
    }

    private function verifyAccess()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_icon_sets'))
        {
            nel_derp(460, _gettext('You are not allowed to access the icon sets panel.'));
        }
    }
}
