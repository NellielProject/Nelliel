<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/styles_panel.php';

class AdminStyles extends AdminBase
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
        nel_render_styles_panel($user, $this->domain);
    }

    public function creator($user)
    {
    }

    public function add($user)
    {
        if (!$user->boardPerm('', 'perm_styles_add'))
        {
            nel_derp(341, _gettext('You are not allowed to add styles.'));
        }

        $style_id = $_GET['style-id'];

        $prepared = $this->database->prepare(
                'INSERT INTO "' . STYLES_TABLE . '" ("id", "name", "file", "is_default") VALUES (?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$id, 'css', 'file', 0]);
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
        if (!$user->boardPerm($this->domain->id(), 'perm_styles_delete'))
        {
            nel_derp(342, _gettext('You are not allowed to remove styles.'));
        }

        $style_id = $_GET['style-id'];
        $prepared = $this->database->prepare('DELETE FROM "' . STYLES_TABLE . '" WHERE "id" = ?');
        $this->database->executePrepared($prepared, array($style_id));
        $this->renderPanel($user);
    }

    public function makeDefault($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_styles_modify'))
        {
            nel_derp(342, _gettext('You are not allowed to modify styles.'));
        }

        $style_id = $_GET['style-id'];
        $this->database->exec('UPDATE "' . STYLES_TABLE . '" SET "is_default" = 0');
        $prepared = $this->database->prepare('UPDATE "' . STYLES_TABLE . '" SET "is_default" = ? WHERE "id" = ?');
        $this->database->executePrepared($prepared, [1, $style_id]);
        $this->renderPanel($user);
    }
}
