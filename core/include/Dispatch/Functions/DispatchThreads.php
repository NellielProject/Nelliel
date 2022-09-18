<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Functions;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\DNSBL;
use Nelliel\Report;
use Nelliel\ThreadHandler;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputInterstitial;
use Nelliel\Output\OutputPost;

class DispatchThreads extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function dispatch(array $inputs)
    {
        $dnsbl = new DNSBL(nel_database('core'));
        $dnsbl->checkIP(nel_request_ip_address());

        if (isset($inputs['parameters']['modmode'])) {
            $this->session->init(true);
            $this->session->toggleModMode();
        }

        if (isset($_POST['form_submit_report'])) {
            $report = new Report($this->domain);
            $report->submit();

            if ($this->session->inModmode($this->domain)) {
                $url = nel_build_router_url([$this->domain->id()], true, 'modmode');
            } else {
                $url = $this->domain->reference('board_directory') . '/' . NEL_MAIN_INDEX . NEL_PAGE_EXT;
            }

            $messages[] = __('The selected items have been reported.');
            $link['url'] = $url;
            $link['text'] = __('Click here to continue.');
            $parameters['page_title'] = $this->domain->reference('title');
            $output_interstitial = new OutputInterstitial($this->domain, false);
            echo $output_interstitial->basic($parameters, false, $messages, [$link]);
        }

        if (isset($_POST['form_submit_delete'])) {
            $thread_handler = new ThreadHandler($this->domain);
            $thread_handler->processContentDeletes();

            if ($this->session->inModmode($this->domain)) {
                $url = nel_build_router_url([$this->domain->id()], true, 'modmode');
            } else {
                $url = $this->domain->reference('board_directory') . '/' . NEL_MAIN_INDEX . NEL_PAGE_EXT;
            }

            $output_post = new OutputPost($this->domain, true);
            echo $output_post->contentDeleted(['forward_url' => $url], false);
        }
    }
}