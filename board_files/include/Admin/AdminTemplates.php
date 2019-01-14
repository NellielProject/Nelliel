<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;

require_once INCLUDE_PATH . 'output/management/templates_panel.php';

class AdminTemplates extends AdminHandler
{
    private $domain;

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
        nel_render_templates_panel($user, $this->domain);
    }

    public function creator($user)
    {
    }

    public function add($user)
    {
        if (!$user->boardPerm('', 'perm_templates_modify'))
        {
            nel_derp(421, _gettext('You are not allowed to modify templates.'));
        }

        $template_id = $_GET['template-id'];
        $ini_parser = new \Nelliel\INIParser(new \Nelliel\FileHandler());
        $template_inis = $ini_parser->parseDirectories(TEMPLATE_FILE_PATH, 'template_info.ini');

        foreach ($template_inis as $ini)
        {
            if ($ini['id'] === $template_id)
            {
                $info = json_encode($ini);
            }
        }

        $prepared = $this->database->prepare(
                'INSERT INTO "' . TEMPLATES_TABLE . '" ("id", "type", "is_default", "info") VALUES (?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$template_id, 'template', 0, $info]);
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
        if (!$user->boardPerm($this->domain->id(), 'perm_templates_modify'))
        {
            nel_derp(421, _gettext('You are not allowed to modify templates.'));
        }

        $template_id = $_GET['template-id'];
        $prepared = $this->database->prepare(
                'DELETE FROM "' . TEMPLATES_TABLE . '" WHERE "id" = ? AND "type" = \'template\'');
        $this->database->executePrepared($prepared, [$template_id]);
        $this->renderPanel($user);
    }

    public function makeDefault($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_templates_modify'))
        {
            nel_derp(421, _gettext('You are not allowed to modify templates.'));
        }

        $template_id = $_GET['template-id'];
        $this->database->exec('UPDATE "' . TEMPLATES_TABLE . '" SET "is_default" = 0');
        $prepared = $this->database->prepare('UPDATE "' . TEMPLATES_TABLE . '" SET "is_default" = 1 WHERE "id" = ?');
        $this->database->executePrepared($prepared, [$template_id]);
        $this->renderPanel($user);
    }
}
