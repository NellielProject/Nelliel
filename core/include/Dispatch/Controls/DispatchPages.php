<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminPages;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchPages extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $pages = new AdminPages($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'new':
                if ($inputs['method'] === 'GET') {
                    $pages->creator();
                }

                if ($inputs['method'] === 'POST') {
                    $pages->add();
                }

                break;

            case 'modify':
                if ($inputs['method'] === 'GET') {
                    $pages->editor($inputs['id']);
                }

                if ($inputs['method'] === 'POST') {
                    $pages->update($inputs['id']);
                }

                break;

            case 'delete':
                $pages->remove($inputs['id']);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $pages->panel();
                }
        }
    }
}