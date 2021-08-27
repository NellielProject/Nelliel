<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputInterstitial;

class DispatchThreads extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function dispatch(array $inputs)
    {
        if ($this->session->modmodeRequested())
        {
            $this->session->init(true);
        }

        $redirect = new \Nelliel\Redirect();
        $redirect->doRedirect(true);

        if (isset($_POST['form_submit_report']))
        {
            $report = new \Nelliel\Report($this->domain);
            $report->submit();

            if ($this->session->inModmode($this->domain))
            {
                $url = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=output&section=index&actions=view&index=0&board-id=' .
                        $inputs['board_id'] . '&modmode=true';
            }
            else
            {
                $url = $this->domain->reference('board_directory') . '/' . NEL_MAIN_INDEX . NEL_PAGE_EXT;
            }

            $messages[] = _gettext('The selected items have been reported.');
            $link['url'] = $url;
            $link['text'] = _gettext('Click here if you are not automatically redirected');
            $parameters['page_title'] = $this->domain->reference('title');
            $output_interstitial = new OutputInterstitial($this->domain, false);
            echo $output_interstitial->render($parameters, false, $messages, [$link]);
            $redirect->changeURL($url);
        }

        if (isset($_POST['form_submit_delete']))
        {
            $thread_handler = new \Nelliel\ThreadHandler($this->domain);
            $thread_handler->processContentDeletes();

            if ($this->session->inModmode($this->domain))
            {
                $url = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=output&section=index&actions=view&index=0&board-id=' .
                        $inputs['board_id'] . '&modmode=true';
            }
            else
            {
                $url = $this->domain->reference('board_directory') . '/' . NEL_MAIN_INDEX . NEL_PAGE_EXT;
            }

            $redirect->changeURL($url);
            $output_post = new \Nelliel\Output\OutputPost($this->domain, true);
            echo $output_post->contentDeleted(['forward_url' => $url], false);
        }
    }
}