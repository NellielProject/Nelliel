<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Register;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputAccount;
use Nelliel\Output\OutputLoginPage;
use Nelliel\Output\OutputRegisterPage;

class DispatchAccount extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function dispatch(array $inputs)
    {
        switch ($inputs['section'])
        {
            case 'login':
                if ($inputs['actions'][0] === 'submit')
                {
                    $this->session->login();
                    $this->session->loggedInOrError();
                    $output_account = new OutputAccount($this->domain, false);
                    $output_account->render([], false);
                }
                else
                {
                    $output_login = new OutputLoginPage($this->domain, false);
                    $output_login->render([], false);
                }

                break;

            case 'logout':
                $this->session->logout();
                break;

            case 'register':
                $authorization = new Authorization(nel_database());

                if ($inputs['actions'][0] === 'submit')
                {
                    $register = new Register($authorization, $this->domain);
                    $register->new();
                }
                else
                {
                    $output_login = new OutputRegisterPage($this->domain, false);
                    $output_login->render(['section' => 'register'], false);
                }

                break;

            default:
                $this->session->init(true);

                if ($this->session->isActive())
                {
                    $output_account = new OutputAccount($this->domain, false);
                    $output_account->render([], false);
                }
                else
                {
                    $output_login = new OutputLoginPage($this->domain, false);
                    $output_login->render([], false);
                }
        }
    }
}