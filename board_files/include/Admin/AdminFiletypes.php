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
        nel_render_filetypes_panel($user, $this->domain);
    }

    public function creator($user)
    {
    }

    public function add($user)
    {
        if (!$user->boardPerm('', 'perm_filetypes_modify'))
        {
            nel_derp(431, _gettext('You are not allowed to modify filetypes.'));
        }

        $extension = $_POST['extension'];
        $parent_extension = $_POST['parent_extension'];
        $type = $_POST['type'];
        $format = $_POST['format'];
        $mime = $_POST['mime'];
        $regex = $_POST['regex'];
        $label = $_POST['label'];
        $prepared = $this->database->prepare(
                'INSERT INTO "' . FILETYPE_TABLE .
                '" ("extension", "parent_extension", "type", "format", "mime", "id_regex", "label") VALUES (?, ?, ?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared,
                [$extension, $parent_extension, $type, $format, $mime, $regex, $label]);
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
        if (!$user->boardPerm($this->domain->id(), 'perm_filetypes_modify'))
        {
            nel_derp(431, _gettext('You are not allowed to modify filetypes.'));
        }

        $filetype_id = $_GET['filetype-id'];
        $prepared = $this->database->prepare('DELETE FROM "' . FILETYPE_TABLE . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, array($filetype_id));
        $this->renderPanel($user);
    }
}
