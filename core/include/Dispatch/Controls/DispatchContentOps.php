<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminContentOps;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchContentOps extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $content_ops = new AdminContentOps($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'new':
                if ($inputs['method'] === 'GET') {
                    $content_ops->creator();
                }

                if ($inputs['method'] === 'POST') {
                    $content_ops->add();
                }

                break;

            case 'modify':
                if ($inputs['method'] === 'GET') {
                    $content_ops->editor($inputs['id']);
                }

                if ($inputs['method'] === 'POST') {
                    $content_ops->update($inputs['id']);
                }

                break;

            case 'delete':
                $content_ops->delete($inputs['id']);
                break;

            case 'enable':
                $content_ops->enable($inputs['id']);
                break;

            case 'disable':
                $content_ops->disable($inputs['id']);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $content_ops->panel();
                }
        }
    }
}