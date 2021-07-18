<?php
declare(strict_types = 1);

namespace Nelliel\Modules\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Modules\Account\Session;

class Dispatch
{
    private $domain;
    private $session;

    function __construct(Domain $domain, Authorization $authorization, Session $session)
    {
        $this->domain = $domain;
        $this->session = $session;
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
                            $output_index = new \Nelliel\Modules\Output\OutputIndex($this->domain, false);
                            $output_index->render(['thread_id' => 0], false);
                            break;

                        case 'expand-thread':
                            $output_thread = new \Nelliel\Modules\Output\OutputThread($this->domain, false);
                            $output_thread->render(
                                    ['thread_id' => $inputs['thread'], 'command' => 'expand-thread'], false);
                            break;

                        case 'collapse-thread':
                            $output_thread = new \Nelliel\Modules\Output\OutputThread($this->domain, false);
                            $output_thread->render(
                                    ['thread_id' => $inputs['thread'], 'command' => 'collapse-thread'], false);
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
                            $output_thread = new \Nelliel\Modules\Output\OutputThread($this->domain, false);
                            $output_thread->render(
                                    ['thread_id' => $inputs['thread'], 'command' => 'view'], false);
                            break;
                    }
                }

                break;
        }
    }
}