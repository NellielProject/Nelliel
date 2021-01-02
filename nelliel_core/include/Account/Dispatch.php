<?php

namespace Nelliel\Account;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Render\OutputLoginPage;
use Nelliel\Render\OutputAccount;
use Nelliel\Render\OutputRegisterPage;

class Dispatch
{
    private $domain;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    public function dispatch(array $inputs)
    {
        switch ($inputs['section'])
        {
            case 'login':
                if ($inputs['actions'][0] === 'submit')
                {
                    $session = new Session();
                    $session->login();
                    $session->loggedInOrError();
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
                $session = new Session();
                $session->logout();
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
                $session = new Session();

                if ($session->isActive())
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