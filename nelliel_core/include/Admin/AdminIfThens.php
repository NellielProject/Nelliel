<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;

class AdminIfThens extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session, array $inputs)
    {
        parent::__construct($authorization, $domain, $session, $inputs);
    }

    public function renderPanel()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelIfThens($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelIfThens($this->domain, false);
        $output_panel->new(['editing' => false], false);
        $this->outputMain(false);
    }

    public function add()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_ifthens'))
        {
            nel_derp(481, _gettext('You are not allowed to add if-thens.'));
        }

        $board_id = $_POST['board_id'] ?? '';
        $if_conditions = $_POST['if_conditions'] ?? '';
        $then_actions = $_POST['then_actions'] ?? '';
        $notes = $_POST['notes'] ?? null;
        $enabled = $_POST['enabled'] ?? 0;
        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_IF_THENS_TABLE .
                '" ("board_id", "if_conditions", "then_actions", "notes", "enabled") VALUES (?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$board_id, $if_conditions, $then_actions, $notes, $enabled]);
        $this->outputMain(true);
    }

    public function editor()
    {
        $this->verifyAccess();
        $entry = $_GET['ifthen-id'] ?? 0;
        $output_panel = new \Nelliel\Render\OutputPanelIfThens($this->domain, false);
        $output_panel->edit(['editing' => true, 'entry' => $entry], false);
        $this->outputMain(false);
    }

    public function update()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_ifthens'))
        {
            nel_derp(482, _gettext('You are not allowed to modify if-thens.'));
        }

        $ifthen_id = $_GET['ifthen-id'] ?? 0;
        $board_id = $_POST['board_id'] ?? '';
        $if_conditions = $_POST['if_conditions'] ?? '';
        $then_actions = $_POST['then_actions'] ?? '';
        $notes = $_POST['notes'] ?? null;
        $enabled = $_POST['enabled'] ?? 0;
        $prepared = $this->database->prepare(
                'UPDATE "' . NEL_IF_THENS_TABLE .
                '" SET "board_id" = ?, "if_conditions" = ?, "then_actions" = ?, "notes" = ?, "enabled" = ? WHERE "entry" = ?');
        $this->database->executePrepared($prepared,
                [$board_id, $if_conditions, $then_actions, $notes, $enabled, $ifthen_id]);
        $this->outputMain(true);
    }

    public function remove()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_ifthens'))
        {
            nel_derp(483, _gettext('You are not allowed to remove if-thens.'));
        }

        $ifthen_id = $_GET['ifthen-id'];
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_IF_THENS_TABLE . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$ifthen_id]);
        $this->outputMain(true);
    }

    public function enable()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_ifthens'))
        {
            nel_derp(484, _gettext('You are not allowed to enable if-thens.'));
        }

        $ifthen_id = $_GET['ifthen-id'];
        $prepared = $this->database->prepare('UPDATE "' . NEL_IF_THENS_TABLE . '" SET "enabled" = 1 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$ifthen_id]);
        $this->outputMain(true);
    }

    public function disable()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_ifthens'))
        {
            nel_derp(485, _gettext('You are not allowed to disable if-thens.'));
        }

        $ifthen_id = $_GET['ifthen-id'];
        $prepared = $this->database->prepare('UPDATE "' . NEL_IF_THENS_TABLE . '" SET "enabled" = 0 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$ifthen_id]);
        $this->outputMain(true);
    }

    private function verifyAccess()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_ifthens'))
        {
            nel_derp(480, _gettext('You are not allowed to access the if-thens panel.'));
        }
    }
}
