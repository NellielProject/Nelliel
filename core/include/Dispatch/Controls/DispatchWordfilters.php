<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminWordfilters;
use Nelliel\Account\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchWordfilters extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $wordfilters = new AdminWordfilters($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'new':
                if ($inputs['method'] === 'GET') {
                    $wordfilters->creator();
                }

                if ($inputs['method'] === 'POST') {
                    $wordfilters->add();
                }

                break;

            case 'modify':
                if ($inputs['method'] === 'GET') {
                    $wordfilters->editor((int) $inputs['id']);
                }

                if ($inputs['method'] === 'POST') {
                    $wordfilters->update((int) $inputs['id']);
                }

                break;

            case 'delete':
                $wordfilters->delete((int) $inputs['id']);
                break;

            case 'enable':
                $wordfilters->enable((int) $inputs['id']);
                break;

            case 'disable':
                $wordfilters->disable((int) $inputs['id']);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $wordfilters->panel();
                }
        }
    }
}