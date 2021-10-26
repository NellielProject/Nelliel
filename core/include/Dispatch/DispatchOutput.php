<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputCatalog;
use Nelliel\Output\OutputIndex;
use Nelliel\Output\OutputThread;

class DispatchOutput extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function dispatch(array $inputs)
    {
        if ($this->domain->id() === Domain::SITE) {
            return;
        }

        $inputs['parameters'] = explode('+', $inputs['parameters'] ?? '');

        if (in_array('modmode', $inputs['parameters'])) {
            $this->session->init(true);
            $this->session->toggleModMode();
        }

        switch ($inputs['section']) {
            case 'page':
                $output_thread = new OutputThread($this->domain, false);
                $output_thread->render(['inputs' => $inputs], false);

            case 'catalog':
                $output_thread = new OutputCatalog($this->domain, false);
                $output_thread->render([], false);

            // Index
            default:
                $output_index = new OutputIndex($this->domain, false);
                $output_index->render(['page' => $inputs['page'] ?? 1], false);
                break;
        }
    }
}