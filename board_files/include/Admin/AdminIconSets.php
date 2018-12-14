<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/icon_sets_panel.php';

class AdminIconSets extends AdminBase
{
    private $domain;

    function __construct($database, $authorization, $domain)
    {
        $this->database = $database;
        $this->authorization = $authorization;
        $this->domain = $domain;
    }

    public function actionDispatch($inputs)
    {
        $session = new \Nelliel\Session($this->authorization, true);
        $user = $session->sessionUser();

        if ($inputs['action'] === 'add')
        {
            $this->add($user);
        }
        else if ($inputs['action'] == 'remove')
        {
            $this->remove($user);
        }
        else if ($inputs['action'] == 'make-default')
        {
            $this->makeDefault($user);
        }
        else
        {
            $this->renderPanel($user);
        }
    }

    public function renderPanel($user)
    {
        nel_render_icon_sets_panel($user, $this->domain);
    }

    public function creator($user)
    {
    }

    public function add($user)
    {
        if (!$user->boardPerm('', 'perm_icon_sets_modify'))
        {
            nel_derp(461, _gettext('You are not allowed to modify icon setss.'));
        }

        $icon_set_id = $_GET['icon-set-id'];
        $ini_parser = new \Nelliel\INIParser(new \Nelliel\FileHandler());
        $icon_set_inis = $ini_parser->parseDirectories(ICON_SET_PATH, 'icon_set_info.ini');

        foreach ($icon_set_inis as $ini)
        {
            if ($ini['id'] === $icon_set_id)
            {
                $name = $ini['name'];
                $directory = $ini['directory'];
                $set_type = $ini['set_type'];
            }
        }
        $prepared = $this->database->prepare(
                'INSERT INTO "' . ICON_SET_TABLE .
                '" ("id", "name", "directory", "set_type", "is_default") VALUES (?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$icon_set_id, $name, $directory, $set_type, 0]);
        $this->renderPanel($user);
    }

    public function editor($user)
    {
    }

    public function update($user)
    {
    }

    public function remove($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_icon_sets_modify'))
        {
            nel_derp(461, _gettext('You are not allowed to modify icon sets.'));
        }

        $icon_set_id = $_GET['icon-set-id'];
        $set_type = $_GET['set-type'];
        $prepared = $this->database->prepare('DELETE FROM "' . ICON_SET_TABLE . '" WHERE "id" = ? AND "set_type" = ?');
        $this->database->executePrepared($prepared, array($icon_set_id, $set_type));
        $this->renderPanel($user);
    }

    public function makeDefault($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_icon_sets_modify'))
        {
            nel_derp(461, _gettext('You are not allowed to modify icon sets.'));
        }

        $icon_set_id = $_GET['icon-set-id'];
        $set_type = $_GET['set-type'];
        $prepared = $this->database->prepare('UPDATE "' . ICON_SET_TABLE . '" SET "is_default" = 0 WHERE "set_type" = ?');
        $this->database->executePrepared($prepared, [$set_type]);
        $prepared = $this->database->prepare('UPDATE "' . ICON_SET_TABLE . '" SET "is_default" = 1 WHERE "id" = ? AND "set_type" = ?');
        $this->database->executePrepared($prepared, [$icon_set_id, $set_type]);
        $this->renderPanel($user);
    }
}
