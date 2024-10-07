<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminNoticeboard;
use Nelliel\Account\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelNoticeboard;

class DispatchNoticeboard extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $noticeboard = new AdminNoticeboard($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'view':
                $output_noticeboard = new OutputPanelNoticeboard($this->domain, false);
                $output_noticeboard->viewNotice(['notice_id' => $inputs['id']], false);
                break;

            case 'new':
                if ($inputs['method'] === 'GET') {
                    $noticeboard->creator();
                }

                if ($inputs['method'] === 'POST') {
                    $noticeboard->add();
                }

                break;

            case 'delete':
                $noticeboard->delete($inputs['id']);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $noticeboard->panel();
                }
        }
    }
}