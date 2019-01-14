<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;

require_once INCLUDE_PATH . 'output/management/reports_panel.php';

class AdminReports extends AdminHandler
{
    private $domain;
    private $defaults = false;

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

        if ($inputs['action'] === 'dismiss')
        {
            $this->dismiss($user, $_GET['report_id']);
        }
        else if (isset($_POST['form_submit_report']))
        {
            $this->add($user);
        }

        $this->renderPanel($user);
    }

    public function renderPanel($user)
    {
        nel_render_reports_panel($user, $this->domain);
    }

    public function creator($user)
    {
    }

    public function add($user)
    {
        $report_data = array();
        $report_data['reason'] = $_POST['report_reason'] ?? null;
        $report_data['reporter_ip'] = $_SERVER['REMOTE_ADDR'];
        $base_content_id = new \Nelliel\ContentID();

        foreach ($_POST as $name => $value)
        {
            if ($base_content_id->isContentID($name))
            {
                $content_id = new \Nelliel\ContentID($name);
            }
            else
            {
                continue;
            }

            if ($value == 'action')
            {
                $report_data['content_id'] = $content_id->getIDString();
                $query = 'INSERT INTO "' . REPORTS_TABLE .
                        '" ("board_id", "content_id", "reason", "reporter_ip") VALUES (?, ?, ?, ?)';
                $prepared = $this->database->prepare($query);
                $this->database->executePrepared($prepared,
                        [$this->domain->id(), $report_data['content_id'], $report_data['reason'],
                            @inet_pton($report_data['reporter_ip'])]);
            }
        }
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

    public function dismiss($user, $report_id)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_reports_dismiss'))
        {
            nel_derp(381, _gettext('You are not allowed to dismiss reports.'));
        }

        $prepared = $this->database->prepare('DELETE FROM "' . REPORTS_TABLE . '" WHERE "report_id" = ?');
        $this->database->executePrepared($prepared, [$report_id]);
    }
}
