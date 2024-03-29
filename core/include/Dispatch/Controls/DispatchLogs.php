<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminLogs;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchLogs extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $logs = new AdminLogs($this->authorization, $this->domain, $this->session);
        $page = (int) ($inputs['page'] ?? 1);
        $log_set = $inputs['log_set'] ?? 'combined';

        switch ($inputs['section']) {
            default:
                $logs->panel($log_set, $page);
        }
    }
}