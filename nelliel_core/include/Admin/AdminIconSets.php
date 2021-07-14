<?php

declare(strict_types=1);

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;

class AdminIconSets extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function renderPanel()
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Render\OutputPanelIconSets($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator()
    {
        $this->verifyAccess($this->domain);
    }

    public function add()
    {
        $this->verifyAction($this->domain);
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
        $this->verifyAccess($this->domain);
    }

    public function update()
    {
        $this->verifyAction($this->domain);
    }

    public function remove()
    {
        $this->verifyAction($this->domain);
        $icon_set_id = $_GET['icon-set-id'];
        $prepared = $this->database->prepare(
                'DELETE FROM "' . NEL_ASSETS_TABLE . '" WHERE "asset_id" = ? AND "type" = ?');
        $this->database->executePrepared($prepared, [$icon_set_id, 'icon-set']);
        $this->outputMain(true);
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
        $icon_set_id = $_GET['icon-set-id'];
        $this->database->exec('UPDATE "' . NEL_ASSETS_TABLE . '" SET "is_default" = 0 WHERE "type" = \'icon-set\'');
        $prepared = $this->database->prepare(
                'UPDATE "' . NEL_ASSETS_TABLE . '" SET "is_default" = 1 WHERE "asset_id" = ? AND "type" = \'icon-set\'');
        $this->database->executePrepared($prepared, [$icon_set_id]);
        $this->outputMain(true);
    }

    public function verifyAccess(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_icon_sets'))
        {
            nel_derp(430, _gettext('You do not have access to the Icon Sets panel.'));
        }
    }

    public function verifyAction(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_icon_sets'))
        {
            nel_derp(431, _gettext('You are not allowed to manage icon sets.'));
        }
    }
}
