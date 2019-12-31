<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;

class AdminLogs extends AdminHandler
{
    private $defaults = false;

    function __construct(Authorization $authorization, Domain $domain)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
    }

    public function actionDispatch($inputs)
    {
        $session = new \Nelliel\Session(true);
        $user = $session->sessionUser();

        if ($inputs['action'] === 'remove')
        {
            $this->remove($user);
        }
        else if ($inputs['action'] === 'generate')
        {
            $logger = new \Nelliel\NellielLogger($this->database);

            for($i = 0; $i < 10000; $i++)
            {
                $message = 'TEST DATA for example a user did a fucking thing entry #' . $i;
                $logger->info($message, ['table' => SYSTEM_LOGS_TABLE, 'area' => 'SYSTEM', 'event_id' => 'TEST_DATA', 'originator' => 'TESTING', 'ip_address' => $_SERVER['REMOTE_ADDR']]);
            }

        }

        $this->renderPanel($user);
    }

    public function renderPanel($user)
    {
        $output_panel = new \Nelliel\Output\OutputPanelLogs($this->domain);
        $log_type = $_GET['log-type'] ?? '';
        $output_panel->render(['user' => $user, 'log_type' => $log_type], false);
    }

    public function creator($user)
    {
    }

    public function add($user)
    {
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
}
