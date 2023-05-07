<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminIPNotes;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchIPNotes extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $ip_notes = new AdminIPNotes($this->authorization, $this->domain, $this->session);
        $id = (int) $inputs['id'] ?? '';

        switch ($inputs['section']) {
            case 'add':
                $ip_notes->add();
            case 'delete':
                $ip_notes->delete($id);
            default:
        }
    }
}