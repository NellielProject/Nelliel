<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminFileFilters;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchFileFilters extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs)
    {
        $file_filters = new AdminFileFilters($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'new':
                if ($inputs['method'] === 'GET') {
                    $file_filters->creator();
                }

                if ($inputs['method'] === 'POST') {
                    $file_filters->add();
                }

                break;

            case 'modify':
                if ($inputs['method'] === 'GET') {
                    $file_filters->editor($inputs['id']);
                }

                if ($inputs['method'] === 'POST') {
                    $file_filters->update($inputs['id']);
                }

                break;

            case 'delete':
                $file_filters->delete($inputs['id']);
                break;

            case 'enable':
                $file_filters->enable($inputs['id']);
                break;

            case 'disable':
                $file_filters->disable($inputs['id']);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $file_filters->panel();
                }
        }
    }
}