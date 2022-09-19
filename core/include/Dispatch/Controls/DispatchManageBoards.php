<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminBoards;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchManageBoards extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs)
    {
        $boards = new AdminBoards($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'new':
                if ($inputs['method'] === 'POST') {
                    $boards->add();
                }

                break;

            case 'delete':
                if ($inputs['method'] === 'GET') {
                    $boards->confirmDelete($inputs['id']);
                }

                if ($inputs['method'] === 'POST') {
                    $boards->delete($inputs['id']);
                }

                break;

            case 'lock':
                $boards->lock($inputs['id']);
                break;

            case 'unlock':
                $boards->unlock($inputs['id']);
                break;

            case 'remove-confirmed':
                $boards->delete($inputs['id'], true);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $boards->panel();
                }
        }
    }
}