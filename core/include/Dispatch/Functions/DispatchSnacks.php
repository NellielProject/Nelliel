<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Functions;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Snacks;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Bans\BansAccess;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchSnacks extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function dispatch(array $inputs): void
    {
        $snacks = new Snacks($this->domain, new BansAccess($this->domain->database()));

        switch ($inputs['section']) {
            case 'user-bans':
                if ($inputs['method'] === 'POST') {
                    switch ($inputs['action']) {
                        case 'file-appeal':
                            $snacks->banAppeal();
                            break;
                    }
                }
        }
    }
}