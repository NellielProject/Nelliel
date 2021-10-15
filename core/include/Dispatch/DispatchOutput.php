<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputIndex;
use Nelliel\Output\OutputThread;
use Nelliel\Output\OutputCatalog;

class DispatchOutput extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function dispatch(array $inputs)
    {
        $inputs['index'] = $_GET['index'] ?? null;
        $inputs['thread'] = intval($_GET['thread'] ?? null);

        switch ($inputs['section'])
        {
            case 'index':
                foreach ($inputs['actions'] as $action)
                {
                    switch ($action)
                    {
                        case 'view':
                            $output_index = new OutputIndex($this->domain, false);
                            $output_index->render(['thread_id' => 0], false);
                            break;

                        case 'expand-thread':
                            $output_thread = new OutputThread($this->domain, false);
                            $output_thread->render(['thread_id' => $inputs['thread'], 'command' => 'expand-thread'],
                                    false);
                            break;

                        case 'collapse-thread':
                            $output_thread = new OutputThread($this->domain, false);
                            $output_thread->render(['thread_id' => $inputs['thread'], 'command' => 'collapse-thread'],
                                    false);
                            break;
                    }
                }

                break;

            case 'thread':
                foreach ($inputs['actions'] as $action)
                {
                    switch ($action)
                    {
                        case 'view':
                            $output_thread = new OutputThread($this->domain, false);
                            $output_thread->render(['thread_id' => $inputs['thread'], 'command' => 'view'], false);
                            break;
                    }
                }

            case 'catalog':
                foreach ($inputs['actions'] as $action)
                {
                    switch ($action)
                    {
                        case 'view':
                            $output_thread = new OutputCatalog($this->domain, false);
                            $output_thread->render(['command' => 'view'], false);
                            break;
                    }
                }

                break;
        }
    }
}