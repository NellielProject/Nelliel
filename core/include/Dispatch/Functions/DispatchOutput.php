<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Functions;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
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

    public function dispatch(array $inputs): void
    {
        if ($this->domain->id() === Domain::SITE) {
            return;
        }

        if (array_key_exists('modmode', $inputs['parameters'])) {
            $this->session->init(true);
            $this->session->toggleModMode();
        }

        switch ($inputs['section']) {
            case $this->domain->reference('page_directory'):
                $output_thread = new OutputThread($this->domain, false);
                $output_thread->render(['thread_id' => $inputs['thread_id'] ?? 0, 'parameters' => $inputs['parameters']],
                    false);

            case 'catalog':
                if ($this->domain->setting('enable_catalog')) {
                    $output_thread = new OutputCatalog($this->domain, false);
                    $output_thread->render([], false);
                }

                break;

            default:
                if ($this->domain->setting('enable_index') || $this->session->inModmode($this->domain)) {
                    $output_index = new OutputIndex($this->domain, false);
                    $output_index->render(['page' => $inputs['page'] ?? 1], false);
                }

                break;
        }
    }
}