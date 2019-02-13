<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;

require_once INCLUDE_PATH . 'output/management/icon_sets_panel.php';

class AdminIconSets extends AdminHandler
{

    function __construct($database, Authorization $authorization, Domain $domain)
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
        if (!$user->domainPermission($this->domain, 'perm_icon_sets_modify'))
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
        if (!$user->domainPermission($this->domain, 'perm_icon_sets_modify'))
        {
            nel_derp(461, _gettext('You are not allowed to modify icon sets.'));
        }

        $icon_set_id = $_GET['icon-set-id'];
        $prepared = $this->database->prepare('DELETE FROM "' . ASSETS_TABLE . '" WHERE "id" = ? AND "type" = ?');
        $this->database->executePrepared($prepared, [$icon_set_id, 'icon-set']);
        $this->renderPanel($user);
    }

    public function makeDefault($user)
    {
        if (!$user->domainPermission($this->domain, 'perm_icon_sets_modify'))
        {
            nel_derp(461, _gettext('You are not allowed to modify icon sets.'));
        }

        $icon_set_id = $_GET['icon-set-id'];
        $set_type = $_GET['set-type'];
        $this->database->exec('UPDATE "' . ASSETS_TABLE . '" SET "is_default" = 0 WHERE "type" = \'icon-set\'');
        $prepared = $this->database->prepare('UPDATE "' . ASSETS_TABLE . '" SET "is_default" = 1 WHERE "id" = ? AND "type" = \'icon-set\'');
        $this->database->executePrepared($prepared, [$icon_set_id]);
        $this->renderPanel($user);
    }
}
