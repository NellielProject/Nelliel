<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminMarkup;
use Nelliel\Account\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchMarkup extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $markup = new AdminMarkup($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'new':
                if ($inputs['method'] === 'GET') {
                    $markup->creator();
                }

                if ($inputs['method'] === 'POST') {
                    $markup->add();
                }

                break;

            case 'modify':
                if ($inputs['method'] === 'GET') {
                    $markup->editor($inputs['id']);
                }

                if ($inputs['method'] === 'POST') {
                    $markup->update($inputs['id']);
                }

                break;

            case 'delete':
                $markup->delete($inputs['id']);
                break;

            case 'enable':
                $markup->enable($inputs['id']);
                break;

            case 'disable':
                $markup->disable($inputs['id']);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $markup->panel();
                }
        }
    }
}