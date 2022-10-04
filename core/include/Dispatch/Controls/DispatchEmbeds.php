<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminEmbeds;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchEmbeds extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $embeds = new AdminEmbeds($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'new':
                if ($inputs['method'] === 'GET') {
                    $embeds->creator();
                }

                if ($inputs['method'] === 'POST') {
                    $embeds->add();
                }

                break;

            case 'modify':
                if ($inputs['method'] === 'GET') {
                    $embeds->editor($inputs['id']);
                }

                if ($inputs['method'] === 'POST') {
                    $embeds->update($inputs['id']);
                }

                break;

            case 'delete':
                $embeds->delete($inputs['id']);
                break;

            case 'enable':
                $embeds->enable($inputs['id']);
                break;

            case 'disable':
                $embeds->disable($inputs['id']);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $embeds->panel();
                }
        }
    }
}