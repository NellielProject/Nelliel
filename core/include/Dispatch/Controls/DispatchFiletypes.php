<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminFiletypes;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchFiletypes extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $filetypes = new AdminFiletypes($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'new':
                if ($inputs['method'] === 'GET') {
                    $filetypes->creator();
                }

                if ($inputs['method'] === 'POST') {
                    $filetypes->add();
                }

                break;

            case 'modify':
                if ($inputs['method'] === 'GET') {
                    $filetypes->editor($inputs['id']);
                }

                if ($inputs['method'] === 'POST') {
                    $filetypes->update($inputs['id']);
                }

                break;

            case 'delete':
                $filetypes->delete($inputs['id']);
                break;

            case 'enable':
                $filetypes->enable($inputs['id']);
                break;

            case 'disable':
                $filetypes->disable($inputs['id']);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $filetypes->panel();
                }
        }
    }
}