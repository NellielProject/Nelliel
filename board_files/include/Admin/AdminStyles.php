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

    public function actionDispatch($inputs)
    {
        if ($inputs['action'] === 'add')
        {
            $this->add();
        }
        else if ($inputs['action'] == 'remove')
        {
            $this->remove();
        }
        else if ($inputs['action'] == 'make-default')
        {
            $this->makeDefault();
        }
        else
        {
            $this->renderPanel();
        }
    }

    public function renderPanel()
    {
        $output_panel = new \Nelliel\Output\OutputPanelStyles($this->domain);
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
        $ini_parser = new \Nelliel\INIParser(new \Nelliel\FileHandler());
        $style_inis = $ini_parser->parseDirectories(STYLES_FILE_PATH, 'style_info.ini');

        foreach ($style_inis as $ini)
        {
            if ($ini['id'] === $style_id)
            {
                $info = json_encode($ini);
            }
        }

        $prepared = $this->database->prepare(
                'INSERT INTO "' . ASSETS_TABLE . '" ("id", "type", "is_default", "info") VALUES (?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$style_id, 'style', 0, $info]);
        $this->renderPanel();
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
        $prepared = $this->database->prepare('DELETE FROM "' . ASSETS_TABLE . '" WHERE "id" = ? AND "type" = \'style\'');
        $this->database->executePrepared($prepared, [$style_id]);
        $this->renderPanel();
    }

    public function makeDefault()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_styles'))
        {
            nel_derp(433, _gettext('You are not allowed to set the default style.'));
        }

        $style_id = $_GET['style-id'];
        $this->database->exec('UPDATE "' . ASSETS_TABLE . '" SET "is_default" = 0 WHERE "type" = \'style\'');
        $prepared = $this->database->prepare(
                'UPDATE "' . ASSETS_TABLE . '" SET "is_default" = 1 WHERE "id" = ? AND "type" = \'style\'');
        $this->database->executePrepared($prepared, [$style_id]);
        $this->renderPanel();
    }
}
