<?php

declare(strict_types=1);

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;

class AdminStaffBoard extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session, array $inputs)
    {
        parent::__construct($authorization, $domain, $session, $inputs);
    }

    public function renderPanel()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelStaffBoard($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator()
    {
        $this->verifyAccess();
    }

    public function add()
    {
        $this->verifyAction();
        $subject = $_POST['subject'] ?? '';
        $message = $_POST['message'] ?? '';
        $time = time();
        $query = 'INSERT INTO "' . NEL_STAFF_BOARD_TABLE . '" ("user_id", "subject", "message", "post_time") VALUES (?, ?, ?, ?)';
        $prepared = $this->database->prepare($query);
        $this->database->executePrepared($prepared,
                [$this->session_user->id(), $subject, $message, $time]);
        $this->outputMain(true);
    }

    public function editor()
    {
        $this->verifyAccess();
    }

    public function update()
    {
        $this->verifyAction();
    }

    public function remove()
    {
        $this->verifyAction();
        $entry = $_GET['entry'];
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_STAFF_BOARD_TABLE . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$entry]);
        $this->outputMain(true);
    }

    public function enable()
    {
        $this->verifyAction();
    }

    public function disable()
    {
        $this->verifyAction();
    }

    public function makeDefault()
    {
        $this->verifyAction();
    }

    private function regenNews()
    {
        $regen = new \Nelliel\Regen();
        $regen->news($this->domain);
    }

    public function verifyAccess()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_staff_board_access'))
        {
            nel_derp(480, _gettext('You do not have access to the Staff Board panel.'));
        }
    }

    public function verifyAction()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_staff_board_post'))
        {
            //nel_derp(441, _gettext('You are not allowed to manage news articles.'));
        }
    }
}
