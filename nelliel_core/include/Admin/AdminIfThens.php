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
        $this->verifyAction();
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
        $this->verifyAction();
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
        $this->verifyAction();
        $ifthen_id = $_GET['ifthen-id'];
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_IF_THENS_TABLE . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$ifthen_id]);
        $this->outputMain(true);
    }

    public function enable()
    {
        $this->verifyAction();
        $ifthen_id = $_GET['ifthen-id'];
        $prepared = $this->database->prepare('UPDATE "' . NEL_IF_THENS_TABLE . '" SET "enabled" = 1 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$ifthen_id]);
        $this->outputMain(true);
    }

    public function disable()
    {
        $this->verifyAction();
        $ifthen_id = $_GET['ifthen-id'];
        $prepared = $this->database->prepare('UPDATE "' . NEL_IF_THENS_TABLE . '" SET "enabled" = 0 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$ifthen_id]);
        $this->outputMain(true);
    }

    public function verifyAccess()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_ifthens'))
        {
            nel_derp(450, _gettext('You do not have access to the If-Thens panel.'));
        }
    }

    public function verifyAction()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_ifthens'))
        {
            nel_derp(451, _gettext('You are not allowed to manage if-thens.'));
        }
    }
}
