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

class AdminStyles extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session, array $inputs)
    {
        parent::__construct($authorization, $domain, $session, $inputs);
    }

    public function renderPanel()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelStyles($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator()
    {
        $this->verifyAccess();
    }

    public function add()
    {
        $this->verifyAction();
        $style_id = $_GET['style-id'];
        $style_inis = $this->domain->frontEndData()->getStyleInis();

        foreach ($style_inis as $ini)
        {
            if ($ini['id'] === $style_id)
            {
                $info = json_encode($ini);
            }
        }

        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_ASSETS_TABLE . '" ("asset_id", "type", "is_default", "info") VALUES (?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$style_id, 'style', 0, $info]);
        $this->outputMain(true);
    }

    public function editor()
    {
        $this->verifyAccess();
    }

    public function update()
    {
        $this->verifyAction();
    }

    public function remove()
    {
        $this->verifyAction();
        $style_id = $_GET['style-id'];
        $prepared = $this->database->prepare(
                'DELETE FROM "' . NEL_ASSETS_TABLE . '" WHERE "asset_id" = ? AND "type" = \'style\'');
        $this->database->executePrepared($prepared, [$style_id]);
        $this->outputMain(true);
    }

    public function enable()
    {
        $this->verifyAction();
    }

    public function disable()
    {
        $this->verifyAction();
    }

    public function makeDefault()
    {
        $this->verifyAction();
        $style_id = $_GET['style-id'];
        $this->database->exec('UPDATE "' . NEL_ASSETS_TABLE . '" SET "is_default" = 0 WHERE "type" = \'style\'');
        $prepared = $this->database->prepare(
                'UPDATE "' . NEL_ASSETS_TABLE . '" SET "is_default" = 1 WHERE "asset_id" = ? AND "type" = \'style\'');
        $this->database->executePrepared($prepared, [$style_id]);
        $this->outputMain(true);
    }

    public function verifyAccess()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_styles'))
        {
            nel_derp(410, _gettext('You do not have access to the Styles panel.'));
        }
    }

    public function verifyAction()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_styles'))
        {
            nel_derp(411, _gettext('You are not allowed to manage styles.'));
        }
    }
}
