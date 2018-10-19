<?php

namespace Nelliel\Panels;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/reports_panel.php';

class PanelReports extends PanelBase
{
    private $board_id = '';
    private $defaults = false;

    function __construct($database, $authorize, $board_id = null)
    {
        $this->database = $database;
        $this->authorize = $authorize;

        if(!is_null($board_id))
        {
            $this->board_id = $board_id;
        }
    }

    public function actionDispatch($inputs)
    {
        $user = $this->authorize->getUser($_SESSION['username']);

        if (!$user->boardPerm('', 'perm_reports_access') && !$user->boardPerm('', 'perm_reports_access'))
        {
            nel_derp(380, _gettext('You are not allowed to access the reports panel.'));
        }

        if($inputs['action'] === 'dismiss')
        {
            if (!$user->boardPerm('', 'perm_reports_dismiss') && !$user->boardPerm('', 'perm_reports_dismiss'))
            {
                nel_derp(381, _gettext('You are not allowed to dismiss reports.'));
            }

            $this->dismiss($_GET['report_id']);
            $this->renderPanel();
        }
        else if(isset($_POST['form_submit_report']))
        {
            $this->add();
            $this->renderPanel();
        }
        else
        {
            $this->renderPanel();
        }
    }

    public function renderPanel()
    {
        nel_render_reports_panel();
    }

    public function add()
    {
        $report_data = array();
        $report_data['board_id'] = (isset($_GET['board_id'])) ? $_GET['board_id'] : null;
        $report_data['reason'] = (isset($_POST['report_reason'])) ? $_POST['report_reason'] : null;
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
                        array($report_data['board_id'], $report_data['content_id'], $report_data['reason'],
                        @inet_pton($report_data['reporter_ip'])));
            }
        }
    }

    public function edit()
    {
    }

    public function update()
    {
    }

    public function remove()
    {
    }

    public function dismiss($report_id)
    {
        $prepared = $this->database->prepare('DELETE FROM "' . REPORTS_TABLE . '" WHERE "report_id" = ?');
        $this->database->executePrepared($prepared, array($report_id));
    }

}
