<?php

namespace Nelliel\Account;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;

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
                    $session = new \Nelliel\Account\Session();
                    $session->login();
                    $session->loggedInOrError();
                    $output_account = new \Nelliel\Render\OutputAccount($this->domain, false);
                    $output_account->render(['user' => $session->sessionUser()], false);
                }
                else
                {
                    $output_login = new \Nelliel\Render\OutputLoginPage($this->domain, false);
                    $output_login->render([], false);
                }

                break;

            case 'logout':
                $session = new \Nelliel\Account\Session();
                $session->logout();
                break;

            case 'register':
                $authorization = new \Nelliel\Auth\Authorization(nel_database());

                if ($inputs['actions'][0] === 'submit')
                {
                    $register = new \Nelliel\Account\Register($authorization, $this->domain);
                    $register->new();
                }
                else
                {
                    $output_login = new \Nelliel\Render\OutputRegisterPage($this->domain, false);
                    $output_login->render(['section' => 'register'], false);
                }

                break;

            default:
                $session = new \Nelliel\Account\Session();

                if ($session->isActive())
                {
                    $output_account = new \Nelliel\Render\OutputAccount($this->domain, false);
                    $output_account->render(['user' => $session->sessionUser()], false);
                }
                else
                {
                    $output_login = new \Nelliel\Render\OutputLoginPage($this->domain, false);
                    $output_login->render([], false);
                }
        }
    }
}