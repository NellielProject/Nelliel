<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;

require_once INCLUDE_PATH . 'output/management/permissions_panel.php';

class AdminPermissions extends AdminHandler
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
        else
        {
            $this->renderPanel($user);
        }
    }

    public function renderPanel($user)
    {
        nel_render_permissions_panel($user, $this->domain);
    }

    public function creator($user)
    {
    }

    public function add($user)
    {
        if (!$user->boardPerm('', 'perm_permissions_modify'))
        {
            nel_derp(451, _gettext('You are not allowed to modify permissions.'));
        }

        $permission = $_POST['permission'];
        $description = $_POST['description'];
        $prepared = $this->database->prepare(
                'INSERT INTO "' . PERMISSIONS_TABLE . '" ("permission", "description") VALUES (?, ?)');
        $this->database->executePrepared($prepared, [$permission, $description]);
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
        if (!$user->boardPerm($this->domain->id(), 'perm_permissions_modify'))
        {
            nel_derp(451, _gettext('You are not allowed to modify permissions.'));
        }

        $permission = $_GET['permission'];
        $prepared = $this->database->prepare('DELETE FROM "' . PERMISSIONS_TABLE . '" WHERE "permission" = ?');
        $this->database->executePrepared($prepared, [$permission]);
        $this->renderPanel($user);
    }
}
