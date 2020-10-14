<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\DomainSite;
use Nelliel\Auth\Authorization;
use Nelliel\Admin\AdminHandler;

class Dispatch
{
    private $domain;
    private $authorization;

    function __construct(Domain $domain, Authorization $authorization)
    {
        $this->domain = $domain;
        $this->authorization = $authorization;
    }

    public function dispatch(array $inputs)
    {
        $admin_handler = null;
        $return = false;

        switch ($inputs['section'])
        {
            case 'bans':

                break;

            case 'manage-boards':
                $admin_handler = new AdminBoards($this->authorization, $this->domain);

                if($inputs['action'] === '')
                {
                    $return = false;
                }

                if ($inputs['action'] === 'remove')
                {
                    if ($inputs['action-confirmed'])
                    {
                        $admin_handler->remove();
                    }
                    else
                    {
                        $admin_handler->createInterstitial();
                    }
                }
                else if ($inputs['action'] === 'lock')
                {
                    $admin_handler->lock();
                }
                else if ($inputs['action'] === 'unlock')
                {
                    $admin_handler->unlock();
                }
                else
                {
                    $this->standard($admin_handler, $inputs);
                }
        }

        if ($return || is_null($admin_handler))
        {
            return;
        }

        $admin_handler->renderPanel();
    }

    private function standard(AdminHandler $admin_handler, array $inputs)
    {
        switch ($inputs['action'])
        {
            case 'new':
                $admin_handler->creator();
                break;

            case 'add':
                $admin_handler->add();
                break;

            case 'edit':
                $admin_handler->editor();
                break;

            case 'update':
                $admin_handler->update();
                break;

            case 'remove':
                $admin_handler->remove();
                break;
        }
    }
}