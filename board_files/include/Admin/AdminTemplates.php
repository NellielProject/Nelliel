<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/templates_panel.php';

class AdminTemplates extends AdminBase
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
        nel_render_templates_panel($user, $this->domain);
    }

    public function creator($user)
    {
    }

    public function add($user)
    {
        if (!$user->boardPerm('', 'perm_templates_add'))
        {
            nel_derp(341, _gettext('You are not allowed to add templates.'));
        }

        $template_id = $_GET['template-id'];

        $ini_parser = new \Nelliel\INIParser(new \Nelliel\FileHandler());
        $template_inis = $ini_parser->parseDirectories(TEMPLATE_PATH, 'template_info.ini');

        foreach ($template_inis as $ini)
        {
            if ($ini['id'] === $template_id)
            {
                $display_name = $ini['name'];
                $directory = $ini['directory'];
                $output = $ini['output_type'];
            }
        }

        $prepared = $this->database->prepare(
                'INSERT INTO "' . TEMPLATE_TABLE .
                '" ("id", "name", "directory", "output_type", "is_default") VALUES (?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$template_id, $display_name, $directory, $output, 0]);
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
        if (!$user->boardPerm($this->domain->id(), 'perm_templates_delete'))
        {
            nel_derp(342, _gettext('You are not allowed to remove templates.'));
        }

        $template_id = $_GET['template-id'];
        $prepared = $this->database->prepare('DELETE FROM "' . TEMPLATE_TABLE . '" WHERE "id" = ?');
        $this->database->executePrepared($prepared, array($template_id));
        $this->renderPanel($user);
    }

    public function makeDefault($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_templates_modify'))
        {
            nel_derp(342, _gettext('You are not allowed to modify styles.'));
        }

        $template_id = $_GET['template-id'];
        $this->database->exec('UPDATE "' . TEMPLATE_TABLE . '" SET "is_default" = 0');
        $prepared = $this->database->prepare('UPDATE "' . TEMPLATE_TABLE . '" SET "is_default" = ? WHERE "id" = ?');
        $this->database->executePrepared($prepared, [1, $template_id]);
        $this->renderPanel($user);
    }
}
