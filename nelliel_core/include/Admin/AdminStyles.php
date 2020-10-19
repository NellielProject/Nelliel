<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;

class AdminStyles extends AdminHandler
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
        $output_panel = new \Nelliel\Output\OutputPanelStyles($this->domain, false);
        $output_panel->render(['user' => $this->session_user], false);
    }

    public function creator()
    {
    }

    public function add()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_styles'))
        {
            nel_derp(431, _gettext('You are not allowed to install styles.'));
        }

        $style_id = $_GET['style-id'];
        $front_end_data = new \Nelliel\FrontEndData($this->database);
        $style_inis = $front_end_data->getStyleInis();

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
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_styles'))
        {
            nel_derp(432, _gettext('You are not allowed to uninstall styles.'));
        }

        $style_id = $_GET['style-id'];
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_ASSETS_TABLE . '" WHERE "asset_id" = ? AND "type" = \'style\'');
        $this->database->executePrepared($prepared, [$style_id]);
        $this->output_main = true;
    }

    public function makeDefault()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_styles'))
        {
            nel_derp(433, _gettext('You are not allowed to set the default style.'));
        }

        $style_id = $_GET['style-id'];
        $this->database->exec('UPDATE "' . NEL_ASSETS_TABLE . '" SET "is_default" = 0 WHERE "type" = \'style\'');
        $prepared = $this->database->prepare(
                'UPDATE "' . NEL_ASSETS_TABLE . '" SET "is_default" = 1 WHERE "asset_id" = ? AND "type" = \'style\'');
        $this->database->executePrepared($prepared, [$style_id]);
        $this->output_main = true;
    }
}
