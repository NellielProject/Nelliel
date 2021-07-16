<?php
declare(strict_types = 1);

namespace Nelliel\Modules\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Modules\Account\Session;
use Nelliel\Auth\Authorization;

class AdminStaffBoard extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel()
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Render\OutputPanelStaffBoard($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator()
    {
    }

    public function add()
    {
        $this->verifyAction($this->domain);
        $subject = $_POST['subject'] ?? '';
        $message = $_POST['message'] ?? '';
        $time = time();
        $query = 'INSERT INTO "' . NEL_STAFF_BOARD_TABLE .
                '" ("user_id", "subject", "message", "post_time") VALUES (?, ?, ?, ?)';
        $prepared = $this->database->prepare($query);
        $this->database->executePrepared($prepared, [$this->session_user->id(), $subject, $message, $time]);
        $this->outputMain(true);
    }

    public function editor()
    {
    }

    public function update()
    {
    }

    public function remove()
    {
        $this->verifyAction($this->domain);
        $entry = $_GET['entry'];
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_STAFF_BOARD_TABLE . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$entry]);
        $this->outputMain(true);
    }

    private function regenNews()
    {
        $regen = new \Nelliel\Regen();
        $regen->news($this->domain);
    }

    public function verifyAccess(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_staff_board_access'))
        {
            nel_derp(480, _gettext('You do not have access to the Staff Board panel.'));
        }
    }

    public function verifyAction(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_staff_board_post'))
        {
            //nel_derp(441, _gettext('You are not allowed to manage news articles.'));
        }
    }
}
