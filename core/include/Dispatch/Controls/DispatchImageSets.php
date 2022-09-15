<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminImageSets;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchImageSets extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs)
    {
        $image_sets = new AdminImageSets($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'install':
                $image_sets->install($inputs['id']);
                break;

            case 'uninstall':
                $image_sets->uninstall($inputs['id']);
                break;

            case 'enable':
                $image_sets->enable($inputs['id']);
                break;

            case 'disable':
                $image_sets->disable($inputs['id']);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $image_sets->panel();
                }
        }
    }
}