<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/filetypes_panel.php';

class AdminFiletypes extends AdminBase
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
            if ($inputs['section'] == 'icon-set')
            {
                $this->addIconSet($user);
            }
        }
        else if ($inputs['action'] == 'remove')
        {
            if ($inputs['section'] == 'icon-set')
            {
                $this->removeIconSet($user);
            }
        }
        else if ($inputs['action'] == 'make-default')
        {
            if ($inputs['section'] == 'icon-set')
            {
                $this->makeDefault($user);;
            }
        }
        else
        {
            $this->renderPanel($user);
        }
    }

    public function renderPanel($user)
    {
        nel_render_filetypes_panel($user, $this->domain);
    }

    public function creator($user)
    {
    }

    public function add($user)
    {
    }

    public function editor($user)
    {
    }

    public function update($user)
    {
    }

    public function remove($user)
    {
    }

    public function addIconSet($user)
    {
        if (!$user->boardPerm('', 'perm_filetypes_add'))
        {
            nel_derp(421, _gettext('You are not allowed to modify styles.'));
        }

        $icon_set_id = $_GET['icon-set-id'];
        $ini_parser = new \Nelliel\INIParser(new \Nelliel\FileHandler());
        $template_inis = $ini_parser->parseDirectories(ICON_SET_PATH . 'filetype/', 'icon_set_info.ini');

        foreach ($template_inis as $ini)
        {
            if ($ini['id'] === $icon_set_id)
            {
                $name = $ini['name'];
                $directory = $ini['directory'];
            }
        }
        $prepared = $this->database->prepare(
                'INSERT INTO "' . ICON_SET_TABLE .
                '" ("id", "name", "directory", "set_type", "is_default") VALUES (?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$icon_set_id, $name, $directory, 'filetype', 0]);
        $this->renderPanel($user);
    }

    public function removeIconSet($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_filetypes_delete'))
        {
            nel_derp(421, _gettext('You are not allowed to modify styles.'));
        }

        $icon_set_id = $_GET['icon-set-id'];
        $prepared = $this->database->prepare('DELETE FROM "' . ICON_SET_TABLE . '" WHERE "id" = ?');
        $this->database->executePrepared($prepared, array($icon_set_id));
        $this->renderPanel($user);
    }

    public function makeDefault($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_filetypes_modify'))
        {
            nel_derp(421, _gettext('You are not allowed to modify styles.'));
        }

        $icon_set_id = $_GET['icon-set-id'];
        $this->database->exec('UPDATE "' . ICON_SET_TABLE . '" SET "is_default" = 0 WHERE "set_type" = \'filetype\'');
        $prepared = $this->database->prepare('UPDATE "' . ICON_SET_TABLE . '" SET "is_default" = ? WHERE "id" = ?');
        $this->database->executePrepared($prepared, [1, $icon_set_id]);
        $this->renderPanel($user);
    }
}
