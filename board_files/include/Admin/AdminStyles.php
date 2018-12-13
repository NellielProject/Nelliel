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

        $id = $_POST['style_id'];
        $display_name = $_POST['display_name'];
        $directory = $_POST['directory'];
        $prepared = $this->database->prepare(
                'INSERT INTO "' . FRONT_END_TABLE .
                '" ("id", "resource_type", "storage", "display_name", "location") VALUES (?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared,
                [$id, 'css', 'file', $display_name, $directory]);
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
        $prepared = $this->database->prepare('DELETE FROM "' . FRONT_END_TABLE . '" WHERE "id" = ?');
        $this->database->executePrepared($prepared, array($style_id));
        $this->renderPanel($user);
    }
}
