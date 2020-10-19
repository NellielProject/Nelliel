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
        $this->validateUser();
    }

    public function actionDispatch(string $action, bool $return)
    {
        /*if ($action === 'remove')
        {
            $this->remove();
        }

        if ($return)
        {
            return;
        }

        $this->renderPanel();*/
    }

    public function renderPanel()
    {
        $output_panel = new \Nelliel\Output\OutputPanelLogs($this->domain, false);
        $log_type = $_GET['log-type'] ?? '';
        $output_panel->render(['user' => $this->session_user, 'log_type' => $log_type], false);
    }

    public function creator()
    {
    }

    public function add()
    {
    }

    public function editor()
    {
    }

    public function update()
    {
    }

    public function remove()
    {
    }
}
