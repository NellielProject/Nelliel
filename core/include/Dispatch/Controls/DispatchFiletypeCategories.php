<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminFiletypeCategories;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchFiletypeCategories extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $filetype_categories = new AdminFiletypeCategories($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'new':
                if ($inputs['method'] === 'GET') {
                    $filetype_categories->creator();
                }

                if ($inputs['method'] === 'POST') {
                    $filetype_categories->add();
                }

                break;

            case 'modify':
                if ($inputs['method'] === 'GET') {
                    $filetype_categories->editor($inputs['id']);
                }

                if ($inputs['method'] === 'POST') {
                    $filetype_categories->update($inputs['id']);
                }

                break;

            case 'delete':
                $filetype_categories->delete($inputs['id']);
                break;

            case 'enable':
                $filetype_categories->enable($inputs['id']);
                break;

            case 'disable':
                $filetype_categories->disable($inputs['id']);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $filetype_categories->panel();
                }
        }
    }
}