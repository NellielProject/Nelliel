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
            nel_derp(341, _gettext('You are not allowed to add filetypes or filetype icon sets.'));
        }

        $id = $_POST['icon_set_id'];
        $display_name = $_POST['display_name'];
        $directory = $_POST['directory'];
        $prepared = $this->database->prepare(
                'INSERT INTO "' . FRONT_END_TABLE .
                '" ("id", "resource_type", "storage", "display_name", "location") VALUES (?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared,
                [$id, 'filetype-icon-set', 'directory', $display_name, $directory]);
        $this->renderPanel($user);
    }

    public function removeIconSet($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_filetypes_delete'))
        {
            nel_derp(342, _gettext('You are not allowed to remove filetypes or filetype icon sets.'));
        }

        $template_id = $_GET['icon-set-id'];
        $prepared = $this->database->prepare('DELETE FROM "' . FRONT_END_TABLE . '" WHERE "id" = ?');
        $this->database->executePrepared($prepared, array($template_id));
        $this->renderPanel($user);
    }
}
