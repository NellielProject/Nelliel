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
    }

    public function actionDispatch($inputs)
    {
        $session = new \Nelliel\Session(true);
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
        $output_panel = new \Nelliel\Output\OutputPanelStyles($this->domain);
        $output_panel->render(['user' => $user], false);
    }

    public function creator($user)
    {
    }

    public function add($user)
    {
        if (!$user->domainPermission($this->domain, 'perm_styles_modify'))
        {
            nel_derp(431, _gettext('You are not allowed to modify styles.'));
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
        if (!$user->domainPermission($this->domain, 'perm_styles_modify'))
        {
            nel_derp(431, _gettext('You are not allowed to modify styles.'));
        }

        $style_id = $_GET['style-id'];
        $prepared = $this->database->prepare('DELETE FROM "' . ASSETS_TABLE . '" WHERE "id" = ? AND "type" = \'style\'');
        $this->database->executePrepared($prepared, [$style_id]);
        $this->renderPanel($user);
    }

    public function makeDefault($user)
    {
        if (!$user->domainPermission($this->domain, 'perm_styles_modify'))
        {
            nel_derp(431, _gettext('You are not allowed to modify styles.'));
        }

        $style_id = $_GET['style-id'];
        $this->database->exec('UPDATE "' . ASSETS_TABLE . '" SET "is_default" = 0 WHERE "type" = \'style\'');
        $prepared = $this->database->prepare(
                'UPDATE "' . ASSETS_TABLE . '" SET "is_default" = 1 WHERE "id" = ? AND "type" = \'style\'');
        $this->database->executePrepared($prepared, [$style_id]);
        $this->renderPanel($user);
    }
}
