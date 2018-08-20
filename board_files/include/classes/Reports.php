<?php

namespace Nelliel;

use \PDO;
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Reports
{
    private $dbh;

    function __construct()
    {
        $this->dbh = nel_database();
    }

    public function collectReportedContent()
    {
        $report_data = array();
        $report_data['board_id'] = (isset($_GET['board_id'])) ? $_GET['board_id'] : null;
        $report_data['reason'] = (isset($_POST['report_reason'])) ? $_POST['report_reason'] : null;
        $report_data['reporter_ip'] = $_SERVER['REMOTE_ADDR'];

        foreach ($_POST as $name => $value)
        {
            if (\Nelliel\ContentID::isContentID($name))
            {
                $content_id = new \Nelliel\ContentID($name);
            }
            else
            {
                continue;
            }

            if($value == 'action')
            {
                $report_data['content_id'] = $content_id->getIDString();
                $this->addReport($report_data);
            }
        }
    }

    public function addReport($report_data)
    {
        $query = 'INSERT INTO "' . REPORTS_TABLE . '" ("board_id", "content_id", "reason", "reporter_ip") VALUES (?, ?, ?, ?)';
        $prepared = $this->dbh->prepare($query);
        $this->dbh->executePrepared($prepared, array($report_data['board_id'], $report_data['content_id'], $report_data['reason'], @inet_pton($report_data['reporter_ip'])));
    }
}
