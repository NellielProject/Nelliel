<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminSiteSettings;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;

class DispatchSiteSettings extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs)
    {
        $site_settings = new AdminSiteSettings($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'update':
                if ($inputs['method'] === 'POST') {
                    $site_settings->update();
                }

                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $site_settings->panel();
                }
        }
    }
}