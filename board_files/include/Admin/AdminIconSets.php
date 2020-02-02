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
        $output_panel = new \Nelliel\Output\OutputPanelIconSets($this->domain);
        $output_panel->render(['user' => $this->session_user], false);
    }

    public function creator()
    {
    }

    public function add()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_icon_sets'))
        {
            nel_derp(461, _gettext('You are not allowed to modify icon setss.'));
        }
        $icon_set_id = $_GET['icon-set-id'];
        $ini_parser = new \Nelliel\INIParser(new \Nelliel\FileHandler());
        $icon_set_inis = $ini_parser->parseDirectories(ICON_SETS_FILE_PATH, 'icon_set_info.ini');

        foreach ($icon_set_inis as $ini)
        {
            if ($ini['id'] === $icon_set_id)
            {
                $info = json_encode($ini);
            }
        }

        $prepared = $this->database->prepare(
                'INSERT INTO "' . ASSETS_TABLE .
                '" ("id", "type", "is_default", "info") VALUES (?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$icon_set_id, 'icon-set', 0, $info]);
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
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_icon_sets'))
        {
            nel_derp(461, _gettext('You are not allowed to modify icon sets.'));
        }

        $icon_set_id = $_GET['icon-set-id'];
        $prepared = $this->database->prepare('DELETE FROM "' . ASSETS_TABLE . '" WHERE "id" = ? AND "type" = ?');
        $this->database->executePrepared($prepared, [$icon_set_id, 'icon-set']);
        $this->renderPanel();
    }

    public function makeDefault()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_icon_sets'))
        {
            nel_derp(461, _gettext('You are not allowed to modify icon sets.'));
        }

        $icon_set_id = $_GET['icon-set-id'];
        $set_type = $_GET['set-type'];
        $this->database->exec('UPDATE "' . ASSETS_TABLE . '" SET "is_default" = 0 WHERE "type" = \'icon-set\'');
        $prepared = $this->database->prepare('UPDATE "' . ASSETS_TABLE . '" SET "is_default" = 1 WHERE "id" = ? AND "type" = \'icon-set\'');
        $this->database->executePrepared($prepared, [$icon_set_id]);
        $this->renderPanel();
    }
}
